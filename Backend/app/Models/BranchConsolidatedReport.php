<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchConsolidatedReport extends Model
{
    use HasFactory;

    protected $table = 'branch_consolidated_reports';

    protected $fillable = [
        'report_reference',
        'branch_id',
        'manager_id',
        'period_type',
        'period_start',
        'period_end',
        'sellers_included_count',
        'status',
        'confirmed_accuracy',
        'manager_notes',
        'super_admin_notes',
        'super_admin_id',
        'submitted_at',
        'verified_at',
        'is_locked',
        'total_regular_orders',
        'total_regular_products_sold',
        'total_regular_revenue',
        'total_custom_cake_orders',
        'total_custom_cakes_sold',
        'total_custom_cake_revenue',
        'total_advance_payments',
        'total_outstanding_balances',
        'total_branch_revenue',
        'total_payments_received',
        'total_orders_completed',
        'total_orders_pending',
        'total_orders_cancelled',
        'breakdown_json',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'confirmed_accuracy' => 'boolean',
        'is_locked' => 'boolean',
        'total_regular_revenue' => 'decimal:2',
        'total_custom_cake_revenue' => 'decimal:2',
        'total_advance_payments' => 'decimal:2',
        'total_outstanding_balances' => 'decimal:2',
        'total_branch_revenue' => 'decimal:2',
        'total_payments_received' => 'decimal:2',
        'breakdown_json' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'user_id');
    }

    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id', 'user_id');
    }

    public function sellerReports()
    {
        return $this->hasMany(SellerDailyReport::class, 'branch_report_id', 'id');
    }

    /**
     * Consolidate metrics from a collection of approved SellerDailyReport models
     */
    public function consolidateFromSellerReports($sellerReports)
    {
        $this->sellers_included_count = $sellerReports->pluck('seller_id')->unique()->count();
        $this->total_regular_orders = (int) $sellerReports->sum('regular_orders_count');
        $this->total_regular_products_sold = (int) $sellerReports->sum('regular_products_sold');
        $this->total_regular_revenue = (float) $sellerReports->sum('regular_order_revenue');

        $this->total_custom_cake_orders = (int) $sellerReports->sum('custom_cake_orders_count');
        $this->total_custom_cakes_sold = (int) $sellerReports->sum('custom_cakes_sold');
        $this->total_custom_cake_revenue = (float) $sellerReports->sum('custom_cake_revenue');
        $this->total_advance_payments = (float) $sellerReports->sum('total_advance_payments');
        $this->total_outstanding_balances = (float) $sellerReports->sum('total_outstanding_balances');

        $this->total_branch_revenue = (float) $sellerReports->sum('combined_daily_revenue');
        $this->total_payments_received = (float) $sellerReports->sum('total_payments_received');

        // Detailed breakdowns for Super Admin inspection
        $productBreakdown = [];
        $cakeBreakdown = [];
        $completedCount = 0;
        $pendingCount = 0;
        $cancelledCount = 0;

        foreach ($sellerReports as $sReport) {
            foreach ($sReport->orders as $o) {
                if (in_array(strtolower($o->order_status), ['completed', 'delivered'])) {
                    $completedCount++;
                } elseif (in_array(strtolower($o->order_status), ['cancelled', 'rejected'])) {
                    $cancelledCount++;
                } else {
                    $pendingCount++;
                }

                $key = $o->product_name;
                if (!isset($productBreakdown[$key])) {
                    $productBreakdown[$key] = [
                        'name' => $o->product_name,
                        'category' => $o->category,
                        'quantity' => 0,
                        'total' => 0,
                    ];
                }
                $productBreakdown[$key]['quantity'] += (int) $o->quantity;
                $productBreakdown[$key]['total'] += (float) $o->total_amount;
            }

            foreach ($sReport->customCakes as $c) {
                if (in_array(strtolower($c->order_status), ['completed', 'delivered'])) {
                    $completedCount++;
                } elseif (in_array(strtolower($c->order_status), ['cancelled', 'rejected'])) {
                    $cancelledCount++;
                } else {
                    $pendingCount++;
                }

                $key = $c->cake_category . ' - ' . $c->cake_type_design;
                if (!isset($cakeBreakdown[$key])) {
                    $cakeBreakdown[$key] = [
                        'category' => $c->cake_category,
                        'design' => $c->cake_type_design,
                        'size' => $c->size_weight,
                        'quantity' => 0,
                        'total' => 0,
                        'advance' => 0,
                        'balance' => 0,
                    ];
                }
                $cakeBreakdown[$key]['quantity'] += (int) $c->quantity;
                $cakeBreakdown[$key]['total'] += (float) $c->total_price;
                $cakeBreakdown[$key]['advance'] += (float) $c->advance_payment;
                $cakeBreakdown[$key]['balance'] += (float) $c->remaining_balance;
            }
        }

        $this->total_orders_completed = $completedCount;
        $this->total_orders_pending = $pendingCount;
        $this->total_orders_cancelled = $cancelledCount;
        $this->breakdown_json = [
            'products' => array_values($productBreakdown),
            'custom_cakes' => array_values($cakeBreakdown),
            'sellers' => $sellerReports->map(fn($sr) => [
                'seller_id' => $sr->seller_id,
                'seller_name' => $sr->seller?->full_name ?? $sr->seller?->name ?? 'Seller #' . $sr->seller_id,
                'report_ref' => $sr->report_reference,
                'regular_revenue' => $sr->regular_order_revenue,
                'custom_cake_revenue' => $sr->custom_cake_revenue,
                'total_revenue' => $sr->combined_daily_revenue,
            ])->values()->all(),
        ];

        return $this;
    }
}

