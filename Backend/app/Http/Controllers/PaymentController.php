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
        if (!$user || $user->role === 'Customer') {
            return response()->json(['message' => 'Unauthorized access to payments.'], 403);
        }

        $scopedBranchId = null;
        if ($user->role !== 'Super Admin') {
            $scopedBranchId = $user->branch_id ?? \App\Models\Branch::where('manager_id', $user->user_id)->value('branch_id');
            if (!$scopedBranchId) {
                return response()->json([
                    'payments' => [],
                    'summary' => [
                        'online_revenue_today' => 0,
                        'pay_on_pickup_delivery' => 0,
                        'failed_today' => 0,
                        'refunds_this_week' => 0,
                    ],
                ]);
            }
        }

        $query = Payment::with(['order.branch', 'order.user'])->latest('created_at');

        if ($scopedBranchId) {
            $query->whereHas('order', function ($q) use ($scopedBranchId) {
                $q->where('branch_id', $scopedBranchId);
            });
        }

        $payments = $query->get()->map(function ($p) {
            $isTest = (bool) ($p->is_test || str_starts_with($p->transaction_reference ?? '', 'TEST-') || str_contains($p->payment_status, 'TEST'));
            return [
                'payment_id' => $p->payment_id,
                'transaction_ref' => $p->transaction_reference ?? '—',
                'order_number' => $p->order ? $p->order->order_number : '—',
                'order_id' => $p->order_id,
                'branch_name' => $p->order?->branch?->branch_name ?? '—',
                'customer_name' => $p->order?->customer_name ?? $p->order?->user?->full_name ?? 'Customer',
                'method' => $p->payment_method,
                'amount' => (float) $p->amount,
                'status' => $p->payment_status,
                'is_test' => $isTest,
                'created_at' => $p->created_at ? $p->created_at->format('H:i') : '—',
                'order_date' => $p->created_at ? $p->created_at->format('Y-m-d H:i') : '—',
                'refund_reason' => $p->refund_reason,
                'refund_amount' => $p->refund_amount ? (float) $p->refund_amount : null,
            ];
        });

        // Real metric calculations from database (scoped by branch for non-Super Admin)
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();

        $scopeBranch = function ($q) use ($scopedBranchId) {
            if ($scopedBranchId) {
                $q->whereHas('order', fn ($oq) => $oq->where('branch_id', $scopedBranchId));
            }
            return $q;
        };

        $onlineRevenueToday = (float) $scopeBranch(Payment::whereIn('payment_method', ['Chapa', 'Telebirr'])
            ->where('payment_status', 'Paid')
            ->whereDate('updated_at', $today))
            ->sum('amount');

        $payOnPickupPending = (float) $scopeBranch(Payment::whereIn('payment_method', ['Pay on pickup', 'Pay on delivery'])
            ->where('payment_status', 'Pending'))
            ->sum('amount');

        $failedToday = $scopeBranch(Payment::where('payment_status', 'Failed')
            ->whereDate('updated_at', $today))
            ->count();

        $refundsThisWeek = (float) $scopeBranch(Payment::where('payment_status', 'Refunded')
            ->where('updated_at', '>=', $week))
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
        if ($user && $user->role !== 'Super Admin') {
            $userBranchId = $user->branch_id ?? \App\Models\Branch::where('manager_id', $user->user_id)->value('branch_id');
            if (!$userBranchId || $order->branch_id != $userBranchId) {
                return response()->json(['message' => 'Access denied: You can only refund orders belonging to your assigned branch.'], 403);
            }
        }

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

        // Webhook signature verification (Gateway webhooks are verified by HMAC-SHA256 / secret header)
        $secret = config('services.chapa.webhook_secret') ?: AppSetting::get('webhook_secret');
        if (empty($secret)) {
            return response()->json(['message' => 'Webhook signature verification is not configured.'], 503);
        }
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

        // Amount & Currency Verification (Master Prompt Section 11)
        $callbackAmount = $request->input('amount') ?? $request->input('data.amount');
        if ($callbackAmount !== null && is_numeric($callbackAmount)) {
            if (abs((float) $callbackAmount - (float) $payment->amount) > 0.01) {
                \Illuminate\Support\Facades\Log::warning("Payment webhook amount mismatch for {$txRef}: expected {$payment->amount}, got {$callbackAmount}");
                return response()->json([
                    'message' => 'Payment amount mismatch: Expected ETB ' . number_format($payment->amount, 2) . ' but received ETB ' . number_format($callbackAmount, 2),
                ], 422);
            }
        }

        $callbackCurrency = $request->input('currency') ?? $request->input('data.currency');
        if ($callbackCurrency !== null && !empty(trim($callbackCurrency))) {
            if (strtoupper(trim($callbackCurrency)) !== 'ETB') {
                \Illuminate\Support\Facades\Log::warning("Payment webhook currency mismatch for {$txRef}: expected ETB, got {$callbackCurrency}");
                return response()->json([
                    'message' => 'Payment currency mismatch: Expected ETB but received ' . strtoupper(trim($callbackCurrency)),
                ], 422);
            }
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

    /**
     * Simulate Payment endpoint (TEST MODE ONLY)
     * Supports: Telebirr, CBE Birr, Chapa
     * Actions: success, fail, cancel
     * Statuses: PAID_TEST, FAILED_TEST, CANCELLED_TEST
     */
    public function simulatePayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required',
            'payment_method' => 'required|string',
            'action' => 'required|string|in:success,fail,cancel',
            'phone' => 'nullable|string|max:50',
            'account' => 'nullable|string|max:50',
            'card_name' => 'nullable|string|max:100',
            'card_last4' => 'nullable|string|max:4',
            'reason' => 'nullable|string|max:255',
        ]);

        // Normalize payment method
        $rawMethod = $validated['payment_method'];
        $method = match (strtolower(str_replace([' ', '_', '-'], '', $rawMethod))) {
            'telebirr' => 'Telebirr',
            'cbebirr', 'cbe' => 'CBE Birr',
            'chapa', 'card', 'creditcard', 'debitcard' => 'Chapa',
            'cashonpickup', 'cash' => 'CASH_ON_PICKUP',
            default => $rawMethod,
        };

        $orderIdentifier = $validated['order_id'];
        $order = Order::with(['payment', 'branch', 'items'])->where(function ($q) use ($orderIdentifier) {
            if (is_numeric($orderIdentifier)) {
                $q->where('order_id', $orderIdentifier)->orWhere('order_number', $orderIdentifier);
            } else {
                $q->where('order_number', $orderIdentifier);
            }
        })->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->order_status === 'Cancelled') {
            return response()->json(['message' => 'This order has already been cancelled.'], 422);
        }

        $payment = Payment::where('order_id', $order->order_id)->latest('payment_id')->first();
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->order_id,
                'payment_method' => $method,
                'amount' => $order->total_amount,
                'payment_status' => 'PENDING',
                'is_test' => true,
            ]);
        }

        $action = $validated['action'];

        if ($action === 'success') {
            // Already paid idempotency
            if (in_array($payment->payment_status, ['PAID_TEST', 'Paid'])) {
                return response()->json([
                    'status' => 'success',
                    'order_number' => $order->order_number,
                    'order_id' => $order->order_id,
                    'payment_status' => $payment->payment_status,
                    'order_status' => $order->order_status,
                    'transaction_reference' => $payment->transaction_reference,
                    'amount' => (float) $order->total_amount,
                    'payment_method' => $payment->payment_method,
                    'is_test' => true,
                    'message' => 'Payment already completed in TEST MODE.',
                ]);
            }

            $prefix = match ($method) {
                'Telebirr' => 'TEST-TEL-',
                'CBE Birr' => 'TEST-CBE-',
                'Chapa' => 'TEST-CHP-',
                default => 'TEST-PAY-',
            };

            $ref = $prefix . strtoupper(bin2hex(random_bytes(4)));

            DB::transaction(function () use ($order, $payment, $method, $ref, $validated) {
                $payment->payment_method = $method;
                $payment->payment_status = 'PAID_TEST';
                $payment->transaction_reference = $ref;
                $payment->is_test = true;
                $payment->test_details = [
                    'phone' => $validated['phone'] ?? null,
                    'account' => $validated['account'] ?? null,
                    'card_name' => $validated['card_name'] ?? null,
                    'card_last4' => $validated['card_last4'] ?? null,
                    'mode' => 'TEST_SIMULATION',
                    'simulated_at' => now()->toIso8601String(),
                ];
                $payment->save();

                if ($order->order_status === 'Pending') {
                    $order->order_status = 'Confirmed';
                    $order->save();

                    OrderStatusHistory::create([
                        'order_id' => $order->order_id,
                        'old_status' => 'Pending',
                        'new_status' => 'Confirmed',
                        'changed_by' => null,
                        'note' => "Simulated {$method} payment successful [TEST MODE - Ref: {$ref}]",
                        'changed_at' => now(),
                    ]);
                }

                AuditLog::create([
                    'user_id' => null,
                    'user_name' => $order->customer_name ?: 'Customer',
                    'action' => 'Simulated test payment succeeded',
                    'record' => $order->order_number,
                    'entity_type' => 'Payment',
                    'entity_id' => (string) $payment->payment_id,
                    'old_value' => 'PENDING',
                    'new_value' => "PAID_TEST via {$method} (Ref: {$ref})",
                    'created_at' => now(),
                ]);

                Notification::create([
                    'user_id' => null,
                    'branch_id' => $order->branch_id,
                    'category' => 'Orders',
                    'title' => "Simulated Payment: {$order->order_number}",
                    'message' => "Order {$order->order_number} marked PAID (TEST) via {$method} (ETB " . number_format($order->total_amount, 0) . ")",
                    'link' => '/admin/orders',
                ]);
            });

            return response()->json([
                'status' => 'success',
                'order_number' => $order->order_number,
                'order_id' => $order->order_id,
                'payment_status' => 'PAID_TEST',
                'order_status' => $order->fresh()->order_status,
                'transaction_reference' => $ref,
                'amount' => (float) $order->total_amount,
                'payment_method' => $method,
                'is_test' => true,
                'message' => "Simulated {$method} payment successful! Order marked as PAID (TEST).",
            ]);
        }

        if ($action === 'fail') {
            $reason = $validated['reason'] ?? 'Simulated payment failed (Insufficient test balance or declined).';
            $failRef = 'TEST-FAIL-' . strtoupper(bin2hex(random_bytes(3)));

            $payment->payment_method = $method;
            $payment->payment_status = 'FAILED_TEST';
            $payment->is_test = true;
            if (!$payment->transaction_reference || !str_starts_with($payment->transaction_reference, 'TEST-')) {
                $payment->transaction_reference = $failRef;
            }
            $payment->test_details = [
                'reason' => $reason,
                'mode' => 'TEST_SIMULATION',
                'simulated_at' => now()->toIso8601String(),
            ];
            $payment->save();

            OrderStatusHistory::create([
                'order_id' => $order->order_id,
                'old_status' => $order->order_status,
                'new_status' => $order->order_status,
                'changed_by' => null,
                'note' => "Simulated {$method} payment failed [TEST MODE: {$reason}]",
                'changed_at' => now(),
            ]);

            return response()->json([
                'status' => 'failed',
                'order_number' => $order->order_number,
                'order_id' => $order->order_id,
                'payment_status' => 'FAILED_TEST',
                'order_status' => $order->order_status,
                'amount' => (float) $order->total_amount,
                'payment_method' => $method,
                'is_test' => true,
                'reason' => $reason,
                'message' => "Simulated payment failed: {$reason}. You may retry payment.",
            ], 422);
        }

        if ($action === 'cancel') {
            $cancelRef = 'TEST-CANC-' . strtoupper(bin2hex(random_bytes(3)));

            $payment->payment_method = $method;
            $payment->payment_status = 'CANCELLED_TEST';
            $payment->is_test = true;
            if (!$payment->transaction_reference || !str_starts_with($payment->transaction_reference, 'TEST-')) {
                $payment->transaction_reference = $cancelRef;
            }
            $payment->test_details = [
                'mode' => 'TEST_SIMULATION',
                'cancelled_at' => now()->toIso8601String(),
            ];
            $payment->save();

            return response()->json([
                'status' => 'cancelled',
                'order_number' => $order->order_number,
                'order_id' => $order->order_id,
                'payment_status' => 'CANCELLED_TEST',
                'order_status' => $order->order_status,
                'payment_method' => $method,
                'is_test' => true,
                'message' => 'Simulated payment cancelled by customer.',
            ]);
        }

        return response()->json(['message' => 'Invalid action.'], 400);
    }
}
