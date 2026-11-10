<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDispatch extends Model
{
    use HasFactory;

    protected $table = 'stock_dispatches';
    protected $primaryKey = 'dispatch_id';

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'status',
        'reference_number',
        'notes',
        'created_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id', 'branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id', 'branch_id');
    }

    public function items()
    {
        return $this->hasMany(StockDispatchItem::class, 'dispatch_id', 'dispatch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function receivedStocks()
    {
        return $this->hasMany(ReceivedStock::class, 'dispatch_id', 'dispatch_id');
    }
}
