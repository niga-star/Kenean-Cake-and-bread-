<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivedStock extends Model
{
    use HasFactory;

    protected $table = 'received_stocks';
    protected $primaryKey = 'received_stock_id';

    protected $fillable = [
        'branch_id',
        'dispatch_id',
        'product_id',
        'product_name',
        'category_name',
        'quantity_received',
        'date_received',
        'received_by',
        'receiver_name',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'date_received' => 'date',
        'quantity_received' => 'integer',
        'branch_id' => 'integer',
        'dispatch_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function dispatch()
    {
        return $this->belongsTo(StockDispatch::class, 'dispatch_id', 'dispatch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }
}

