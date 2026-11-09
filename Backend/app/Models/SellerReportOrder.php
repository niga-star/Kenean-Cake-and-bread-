<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerReportOrder extends Model
{
    use HasFactory;

    protected $table = 'seller_report_orders';

    protected $fillable = [
        'report_id',
        'order_id',
        'order_number',
        'product_name',
        'category',
        'quantity',
        'unit_price',
        'total_amount',
        'order_status',
        'payment_status',
        'customer_info',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function report()
    {
        return $this->belongsTo(SellerDailyReport::class, 'report_id', 'id');
    }

    public function systemOrder()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
