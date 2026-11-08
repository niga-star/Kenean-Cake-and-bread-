<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'user_id',
        'branch_id',
        'report_type',
        'title',
        'content',
        'submitted_to_id',
        'status',
        'manager_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function submittedTo()
    {
        return $this->belongsTo(User::class, 'submitted_to_id', 'user_id');
    }
}
