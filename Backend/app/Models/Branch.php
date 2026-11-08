<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_name',
        'branch_code',
        'branch_type',
        'region',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'opening_hours',
        'manager_id',
        'max_managers',
        'max_staff',
        'status',
        'is_shopping_enabled',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_shopping_enabled' => 'boolean',
        'max_managers' => 'integer',
        'max_staff' => 'integer',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'branch_id', 'branch_id');
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class, 'branch_id', 'branch_id');
    }

    public function inventory()
    {
        return $this->hasMany(BranchInventory::class, 'branch_id', 'branch_id');
    }

    public function productPrices()
    {
        return $this->hasMany(BranchProductPrice::class, 'branch_id', 'branch_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'branch_id', 'branch_id');
    }

    public function transfersSent()
    {
        return $this->hasMany(StockTransfer::class, 'source_branch_id', 'branch_id');
    }

    public function transfersReceived()
    {
        return $this->hasMany(StockTransfer::class, 'destination_branch_id', 'branch_id');
    }

    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class, 'branch_id', 'branch_id');
    }

    public function salaryCorrectionRequests()
    {
        return $this->hasMany(SalaryCorrectionRequest::class, 'branch_id', 'branch_id');
    }

    public function staffReports()
    {
        return $this->hasMany(StaffReport::class, 'branch_id', 'branch_id');
    }
}
