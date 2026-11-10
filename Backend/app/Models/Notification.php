<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'branch_id',
        'category',
        'title',
        'message',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($notification) {
            // Deduplication guard: prevent identical notifications for the same recipient/branch within 24 hours
            $duplicate = static::where('category', $notification->category)
                ->where('title', $notification->title)
                ->where(function ($q) use ($notification) {
                    if (is_null($notification->user_id)) {
                        $q->whereNull('user_id');
                    } else {
                        $q->where('user_id', $notification->user_id);
                    }
                })
                ->where(function ($q) use ($notification) {
                    if (is_null($notification->branch_id)) {
                        $q->whereNull('branch_id');
                    } else {
                        $q->where('branch_id', $notification->branch_id);
                    }
                })
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($duplicate) {
                return false;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}
