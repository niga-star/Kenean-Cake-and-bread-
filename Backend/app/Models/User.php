<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'full_name_am',
        'email',
        'email_verified_at',
        'phone',
        'password',
        'role',
        'branch_id',
        'salary',
        'employment_status',
        'status',
        'status_reason',
        'hire_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salary' => 'decimal:2',
            'hire_date' => 'date',
        ];
    }

    public function getSalaryAttribute($value)
    {
        if ($this->relationLoaded('employeeProfile') && $this->employeeProfile) {
            return (float) $this->employeeProfile->salary;
        }
        return (float) ($value ?? 0.00);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function customCakeRequests()
    {
        return $this->hasMany(CustomCakeRequest::class, 'user_id', 'user_id');
    }

    public function salaryCorrectionRequests()
    {
        return $this->hasMany(SalaryCorrectionRequest::class, 'user_id', 'user_id');
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id', 'user_id');
    }

    public function staffReports()
    {
        return $this->hasMany(StaffReport::class, 'user_id', 'user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    public function isMainManager(): bool
    {
        return $this->role === 'Main Branch Manager';
    }

    public function isBranchManager(): bool
    {
        return in_array($this->role, ['Branch Manager', 'Branch Admin']);
    }

    public function isBranchAdmin(): bool
    {
        return in_array($this->role, ['Branch Manager', 'Branch Admin']);
    }

    public function isBranchSeller(): bool
    {
        return in_array($this->role, ['Branch Seller', 'Branch Seller / Cashier', 'Cashier']);
    }

    public function isServant(): bool
    {
        return $this->role === 'Servant';
    }

    public function isDeliveryStaff(): bool
    {
        return $this->role === 'Delivery Staff';
    }

    public function isBranchStaff(): bool
    {
        return in_array($this->role, [
            'Branch Staff',
            'Branch Seller',
            'Branch Seller / Cashier',
            'Cashier',
            'Servant',
            'Delivery Staff',
        ]);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'Customer';
    }
}
