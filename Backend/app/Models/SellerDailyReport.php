<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerDailyReport extends Model
{
    use HasFactory;

    protected $table = 'seller_daily_reports';

    protected $fillable = [
        'report_reference',
        'branch_id',
        'seller_id',
        'report_date',
        'status',
        'manager_id',
        'manager_notes',
        'submitted_at',
        'verified_at',
        'returned_at',
        'branch_report_id',
        'regular_orders_count',
        'regular_products_sold',
        'regular_order_revenue',
        'custom_cake_orders_count',
        'custom_cakes_sold',
        'custom_cake_revenue',
        'total_advance_payments',
        'total_outstanding_balances',
        'combined_daily_revenue',
        'total_payments_received',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'returned_at' => 'datetime',
        'regular_order_revenue' => 'decimal:2',
        'custom_cake_revenue' => 'decimal:2',
        'total_advance_payments' => 'decimal:2',
        'total_outstanding_balances' => 'decimal:2',
        'combined_daily_revenue' => 'decimal:2',
        'total_payments_received' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'user_id');
    }

    public function consolidatedReport()
    {
        return $this->belongsTo(BranchConsolidatedReport::class, 'branch_report_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(SellerReportOrder::class, 'report_id', 'id');
    }

    public function customCakes()
    {
        return $this->hasMany(SellerReportCustomCake::class, 'report_id', 'id');
    }

    /**
     * Recalculate summary totals from order items
     */
    public function recalculateTotals()
    {
        $orders = $this->orders;
        $cakes = $this->customCakes;

        $regOrdersCount = $orders->count();
        $regProductsSold = (int) $orders->sum('quantity');
        $regOrderRevenue = (float) $orders->sum('total_amount');

        $customOrdersCount = $cakes->count();
        $customCakesSold = (int) $cakes->sum('quantity');
        $customCakeRevenue = (float) $cakes->sum('total_price');
        $advancePayments = (float) $cakes->sum('advance_payment');
        $outstandingBalances = (float) $cakes->sum('remaining_balance');

        $combinedRevenue = $regOrderRevenue + $customCakeRevenue;
        $paymentsReceived = $regOrderRevenue + $advancePayments;

        $this->update([
            'regular_orders_count' => $regOrdersCount,
            'regular_products_sold' => $regProductsSold,
            'regular_order_revenue' => $regOrderRevenue,
            'custom_cake_orders_count' => $customOrdersCount,
            'custom_cakes_sold' => $customCakesSold,
            'custom_cake_revenue' => $customCakeRevenue,
            'total_advance_payments' => $advancePayments,
            'total_outstanding_balances' => $outstandingBalances,
            'combined_daily_revenue' => $combinedRevenue,
            'total_payments_received' => $paymentsReceived,
        ]);
    }
}
