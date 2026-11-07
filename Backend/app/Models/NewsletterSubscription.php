<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $primaryKey = 'subscription_id';

    protected $fillable = [
        'email',
        'status',
    ];
}
