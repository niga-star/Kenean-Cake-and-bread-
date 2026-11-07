<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $primaryKey = 'banner_id';

    protected $fillable = [
        'title_en',
        'title_am',
        'subtitle_en',
        'subtitle_am',
        'description_en',
        'description_am',
        'image_url',
        'link_url',
        'button_text_en',
        'button_text_am',
        'badge_en',
        'badge_am',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
