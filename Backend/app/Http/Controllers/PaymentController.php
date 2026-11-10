<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Order;
use App\Models\BranchInventory;
use App\Models\InventoryTransaction;
use App\Models\OrderStatusHistory;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\AppSetting;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * List payment transactions and metrics
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Payment::with(['order.branch', 'order.user'])->latest('created_at');

        if ($user && in_array($user->role, ['Branch Admin', 'Branch Staff']) && $user->branch_id) {
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        $payments = $query->get()->map(function ($p) {
            return [
                'payment_id' => $p->payment_id,
                'transaction_ref' => $p->transaction_reference ?? '—',
                'order_number' => $p->order ? $p->order->order_number : '—',
                'order_id' => $p->order_id,
                'branch_name' => $p->order?->branch?->branch_name ?? '—',
                'method' => $p->payment_method,
                'amount' => (float) $p->amount,
                'status' => $p->payment_status,
                'created_at' => $p->created_at ? $p->created_at->format('H:i') : '—',
                'refund_reason' => $p->refund_reason,
                'refund_amount' => $p->refund_amount ? (float) $p->refund_amount : null,
            ];
        });

        // Real metric calculations from MySQL database
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();

        $onlineRevenueToday = (float) Payment::whereIn('payment_method', ['Chapa', 'Telebirr'])
            ->where('payment_status', 'Paid')
            ->whereDate('updated_at', $today)
            ->sum('amount');

        $payOnPickupPending = (float) Payment::whereIn('payment_method', ['Pay on pickup', 'Pay on delivery'])
            ->where('payment_status', 'Pending')
            ->sum('amount');

        $failedToday = Payment::where('payment_status', 'Failed')
            ->whereDate('updated_at', $today)
            ->count();

        $refundsThisWeek = (float) Payment::where('payment_status', 'Refunded')
            ->where('updated_at', '>=', $week)
            ->sum('refund_amount');

        return response()->json([
            'payments' => $payments,
            'summary' => [
                'online_revenue_today' => $onlineRevenueToday,
                'pay_on_pickup_delivery' => $payOnPickupPending,
                'failed_today' => $failedToday,
                'refunds_this_week' => $refundsThisWeek,
            ]
        ]);
    }

    /**
     * Issue refund for an order (Super Admin or Main Branch Manager)
     * Rule: Refunds are issued to the original payment method and recorded on the payment.
     * Reservation is released and order marked Cancelled.
     */
    public function refund(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:500',
        ]);

        $order = Order::with(['items', 'branch'])->where('order_number', $validated['order_number'])->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $paymentQuery = Payment::where('order_id', $order->order_id);
        if ($request->filled('payment_id')) {
            $payment = $paymentQuery->where('payment_id', $request->input('payment_id'))->first();
        } else {
            $payment = $paymentQuery->orderBy('payment_id', 'desc')->first();
        }

        if (!$payment) {
            return response()->json(['message' => 'No payment record associated with this order.'], 404);
        }

        if ($payment->payment_status === 'Refunded') {
            return response()->json(['message' => 'This payment has already been refunded.'], 422);
        }

        if ((float) $validated['amount'] > (float) $payment->amount) {
            return response()->json([
                'message' => "Refund amount (ETB " . number_format($validated['amount'], 2) . ") cannot exceed the recorded payment amount (ETB " . number_format($payment->amount, 2) . ")."
            ], 422);
        }

        $user = $request->user();

        DB::transaction(function () use ($order, $payment, $validated, $user) {
            $payment->payment_status = 'Refunded';
            $payment->refund_amount = $validated['amount'];
            $payment->refund_reason = $validated['reason'];
            $payment->save();

            // If order was not completed, release reserved stock
            if ($order->order_status !== 'Completed' && $order->order_status !== 'Cancelled') {
                foreach ($order->items as $item) {
                    $inv = BranchInventory::where('branch_id', $order->branch_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $inv->reserved_quantity = max(0, $inv->reserved_quantity - $item->quantity);
                        $inv->save();
                    }

                    InventoryTransaction::create([
                        'branch_id' => $order->branch_id,
                        'product_id' => $item->product_id,
                        'transaction_type' => 'order_cancelled',
                        'quantity' => 0,
                        'reference_type' => 'Order',
                        'reference_id' => $order->order_id,
                        'reason' => "Order {$order->order_number} refunded ETB {$validated['amount']}: {$validated['reason']}. Released reserved stock.",
                        'created_by' => $user?->user_id,
                    ]);
                }
            }

            $oldStatus = $order->order_status;
            $order->order_status = 'Cancelled';
            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => $oldStatus,
                'new_status' => 'Cancelled',
                'changed_by' => $user?->user_id,
                'note' => "Refunded ETB " . number_format($validated['amount'], 0) . " to original payment method ({$payment->payment_method}): {$validated['reason']}",
                'changed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Branch Admin',
                'action' => 'Refund issued',
                'record' => $order->order_number,
                'entity_type' => 'Payment',
                'entity_id' => (string) $payment->payment_id,
                'old_value' => 'ETB ' . number_format($order->total_amount, 0),
                'new_value' => 'Refunded ETB ' . number_format($validated['amount'], 0) . ' (' . $payment->payment_method . ')',
                'created_at' => now(),
            ]);

            $alreadyRefundNotified = Notification::where('branch_id', $order->branch_id)
                ->where('category', 'Orders')
                ->where('title', 'like', "%{$order->order_number}%")
                ->where('title', 'like', '%Refund%')
                ->exists();

            if (!$alreadyRefundNotified) {
                Notification::create([
                    'user_id' => null,
                    'branch_id' => $order->branch_id,
                    'category' => 'Orders',
                    'title' => "Refund Issued: {$order->order_number}",
                    'message' => "Order {$order->order_number} refunded ETB " . number_format($validated['amount'], 0) . ". Reason: {$validated['reason']}",
                    'link' => '/admin/orders',
                ]);
            }
        });

        return response()->json([
            'message' => 'Refund issued successfully and order updated.',
            'payment' => $payment->fresh(),
            'order' => $order->fresh(['items', 'statusHistory']),
        ]);
    }

    /**
     * Signed Webhook callback endpoint
     * Rules:
     * - Gateway confirmations arrive through a signed webhook.
     * - The order is confirmed ONLY on a verified webhook, never on the browser redirect.
     * - Duplicate callbacks are ignored using the transaction reference as an idempotency key.
     */
    public function webhook(Request $request)
    {
        $txRef = $request->input('tx_ref') ?? $request->input('transaction_reference') ?? $request->input('reference');
        $status = $request->input('status') ?? 'success';

        if (!$txRef) {
            return response()->json(['message' => 'Missing transaction reference'], 400);
        }

        // Webhook signature verification (Section 12: Gateway webhooks are verified by signature)
        $secret = env('CHAPA_WEBHOOK_SECRET') ?? AppSetting::get('webhook_secret', 'whsec_live_89f21a9chapa2026');
        $signature = $request->header('x-chapa-signature')
            ?? $request->header('chapa-signature')
            ?? $request->header('signature')
            ?? $request->input('secret_hash');

        if (!$signature) {
            return response()->json(['message' => 'Invalid or missing webhook signature.'], 401);
        }

        // Support both direct secret token match and HMAC-SHA256 payload digest with timing-safe hash_equals
        $payload = $request->getContent();
        $expectedHmac = hash_hmac('sha256', $payload, $secret);
        $isValid = hash_equals($secret, $signature) || hash_equals($expectedHmac, $signature);

        if (!$isValid) {
            return response()->json(['message' => 'Invalid or missing webhook signature.'], 401);
        }

        // Find payment record by transaction reference
        $payment = Payment::with('order')->where('transaction_reference', $txRef)->first();
        if (!$payment) {
            return response()->json(['message' => "Transaction reference '{$txRef}' not found"], 404);
        }

        // Idempotency: duplicate callbacks are safely ignored
        if ($payment->payment_status === 'Paid') {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment already processed (duplicate callback ignored)',
            ], 200);
        }

        DB::transaction(function () use ($payment, $status, $txRef) {
            if (in_array(strtolower($status), ['success', 'paid'])) {
                $payment->payment_status = 'Paid';
                $payment->save();

                $order = $payment->order;
                if ($order && $order->order_status === 'Pending') {
                    $order->order_status = 'Confirmed';
                    $order->save();

                    OrderStatusHistory::create([
                        'order_id' => $order->order_id,
                        'old_status' => 'Pending',
                        'new_status' => 'Confirmed',
                        'changed_by' => null,
                        'note' => "Confirmed via verified payment webhook ({$txRef})",
                        'changed_at' => now(),
                    ]);

                    AuditLog::create([
                        'user_id' => null,
                        'user_name' => 'Payment Gateway',
                        'action' => 'Payment confirmed via webhook',
                        'record' => $order->order_number,
                        'entity_type' => 'Payment',
                        'entity_id' => (string) $payment->payment_id,
                        'old_value' => 'Pending',
                        'new_value' => 'Paid (Confirmed)',
                        'created_at' => now(),
                    ]);

                    $alreadyConfirmedNotified = Notification::where('branch_id', $order->branch_id)
                        ->where('category', 'Orders')
                        ->where('title', 'like', "%{$order->order_number}%")
                        ->where('title', 'like', '%Payment confirmed%')
                        ->exists();

                    if (!$alreadyConfirmedNotified) {
                        Notification::create([
                            'user_id' => null,
                            'branch_id' => $order->branch_id,
                            'category' => 'Orders',
                            'title' => "Payment confirmed for {$order->order_number}",
                            'message' => "Order {$order->order_number} confirmed via {$payment->payment_method} (ETB " . number_format($payment->amount, 0) . ")",
                            'link' => '/admin/orders',
                        ]);
                    }
                }
            } elseif (in_array(strtolower($status), ['failed', 'cancelled'])) {
                $payment->payment_status = 'Failed';
                $payment->save();
            }
        });

        return response()->json([
            'status' => 'handled',
            'order_number' => $payment->order?->order_number,
            'payment_status' => $payment->fresh()->payment_status,
            'order_status' => $payment->order?->fresh()->order_status,
        ]);
    }
}
