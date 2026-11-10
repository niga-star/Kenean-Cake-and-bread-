<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDispatchItem extends Model
{
    use HasFactory;

    protected $table = 'stock_dispatch_items';

    protected $fillable = [
        'dispatch_id',
        'product_id',
        'quantity_dispatched',
        'quantity_received',
    ];

    public function dispatch()
    {
        return $this->belongsTo(StockDispatch::class, 'dispatch_id', 'dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
