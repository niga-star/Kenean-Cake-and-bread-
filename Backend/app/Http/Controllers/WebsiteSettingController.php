<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\Banner;
use App\Models\AuditLog;

class WebsiteSettingController extends Controller
{
    /**
     * Public endpoint to get business info, contact coords, hours, story & stats
     */
    public function businessInfo()
    {
        $all = AppSetting::all()->pluck('value', 'key');

        return response()->json([
            'site_name' => $all['site_name'] ?? 'SweetBakes Bakery',
            'site_tagline' => $all['site_tagline'] ?? 'Artisanal Bakery & Celebration Cakes',
            'phone' => $all['business_phone'] ?? '+251 911 123 456',
            'email' => $all['business_email'] ?? 'info@sweetbakes.com',
            'address_en' => $all['business_address_en'] ?? 'Bole Medhanialem, Next to Edna Mall, Addis Ababa, Ethiopia',
            'address_am' => $all['business_address_am'] ?? 'ቦሌ መድኃኔዓለም፣ ኤድና ሞል አጠገብ፣ አዲስ አበባ፣ ኢትዮጵያ',
            'hours_en' => $all['business_hours_en'] ?? 'Mon – Sun: 8:00 AM – 9:00 PM',
            'hours_am' => $all['business_hours_am'] ?? 'ከሰኞ – እሑድ፡ ከጠዋቱ 2:00 – ማታ 3:00',
            'latitude' => (float) ($all['business_lat'] ?? 9.0016),
            'longitude' => (float) ($all['business_lng'] ?? 38.7845),
            'about_story_en' => $all['about_story_en'] ?? 'At SweetBakes, we believe every celebration starts with the aroma of freshly baked butter, warm vanilla, and handcrafted pastries.',
            'about_story_am' => $all['about_story_am'] ?? 'በስዊትቤክስ እያንዳንዱ በዓል የሚጀምረው አዲስ በተጋገረ ቅቤ፣ ሞቅ ባለ ቫኒላ እና በእጅ በተዘጋጁ ኬኮች መዓዛ ነው።',
            'stats' => [
                [
                    'id' => 'experience',
                    'value' => $all['stat_experience_years'] ?? '12+',
                    'label_en' => 'Years of Baking',
                    'label_am' => 'የስራ ልምድ ዓመታት'
                ],
                [
                    'id' => 'customers',
                    'value' => $all['stat_happy_customers'] ?? '50k+',
                    'label_en' => 'Happy Customers',
                    'label_am' => 'ደስተኛ ደንበኞች'
                ],
                [
                    'id' => 'daily_fresh',
                    'value' => $all['stat_daily_fresh'] ?? '100%',
                    'label_en' => 'Fresh Daily Guarantee',
                    'label_am' => 'በየቀኑ ትኩስ ጥራት'
                ],
                [
                    'id' => 'chefs',
                    'value' => $all['stat_master_chefs'] ?? '15+',
                    'label_en' => 'Master Pastry Chefs',
                    'label_am' => 'ዋና ኬክ ሼፎች'
                ],
            ],
            'social' => [
                'facebook' => $all['social_facebook'] ?? 'https://facebook.com/sweetbakes',
                'instagram' => $all['social_instagram'] ?? 'https://instagram.com/sweetbakes',
                'telegram' => $all['social_telegram'] ?? 'https://t.me/sweetbakes',
                'tiktok' => $all['social_tiktok'] ?? 'https://tiktok.com/@sweetbakes',
            ]
        ]);
    }

    /**
     * Public endpoint to get active homepage banners
     */
    public function banners()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json($banners);
    }

    /**
     * Admin endpoints for banner management
     */
    public function adminBanners()
    {
        return response()->json(Banner::orderBy('sort_order', 'asc')->get());
    }

    public function storeBanner(Request $request)
    {
        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_am' => 'nullable|string|max:255',
            'subtitle_en' => 'nullable|string|max:255',
            'subtitle_am' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_am' => 'nullable|string',
            'image_url' => 'required|string|max:500',
            'link_url' => 'nullable|string|max:255',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_am' => 'nullable|string|max:100',
            'badge_en' => 'nullable|string|max:100',
            'badge_am' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $banner = Banner::create($validated);

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Banner created',
            'record' => $banner->title_en,
            'entity_type' => 'Banner',
            'entity_id' => (string) $banner->banner_id,
            'old_value' => '—',
            'new_value' => $banner->title_en,
            'created_at' => now(),
        ]);

        return response()->json($banner, 201);
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title_en' => 'sometimes|string|max:255',
            'title_am' => 'nullable|string|max:255',
            'subtitle_en' => 'nullable|string|max:255',
            'subtitle_am' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_am' => 'nullable|string',
            'image_url' => 'sometimes|string|max:500',
            'link_url' => 'nullable|string|max:255',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_am' => 'nullable|string|max:100',
            'badge_en' => 'nullable|string|max:100',
            'badge_am' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $banner->update($validated);

        return response()->json($banner);
    }

    public function destroyBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $title = $banner->title_en;
        $banner->delete();

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Banner deleted',
            'record' => $title,
            'entity_type' => 'Banner',
            'entity_id' => (string) $id,
            'old_value' => $title,
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Banner deleted successfully']);
    }
}
