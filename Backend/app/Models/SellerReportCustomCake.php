<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerReportCustomCake extends Model
{
    use HasFactory;

    protected $table = 'seller_report_custom_cakes';

    protected $fillable = [
        'report_id',
        'custom_cake_id',
        'cake_order_number',
        'customer_name',
        'cake_category',
        'cake_type_design',
        'size_weight',
        'quantity',
        'customization_details',
        'unit_price',
        'total_price',
        'advance_payment',
        'remaining_balance',
        'delivery_date',
        'order_status',
        'payment_status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(SellerDailyReport::class, 'report_id', 'id');
    }

    public function systemCustomCake()
    {
        return $this->belongsTo(CustomCakeRequest::class, 'custom_cake_id', 'request_id');
    }
}

