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
            // Bakery Identity & Description
            'site_name' => $all['site_name'] ?? 'SweetBakes Bakery',
            'site_tagline' => $all['site_tagline'] ?? 'Artisanal Bakery & Celebration Cakes',
            'site_description_en' => $all['site_description_en'] ?? 'Handcrafted celebration cakes and artisanal breads baked fresh daily with love in Addis Ababa.',
            'site_description_am' => $all['site_description_am'] ?? 'በፍቅር እና በጥንቃቄ የተዘጋጁ ልዩ የክብረ በዓል ኬኮች እና ትኩስ የተፈጥሮ ዳቦዎች በአዲስ አበባ።',
            'since_year' => $all['since_year'] ?? '2015',

            // Contact & Location
            'phone' => $all['business_phone'] ?? '+251 911 123 456',
            'email' => $all['business_email'] ?? 'info@sweetbakes.com',
            'address_en' => $all['business_address_en'] ?? 'Bole Medhanialem, Next to Edna Mall, Addis Ababa, Ethiopia',
            'address_am' => $all['business_address_am'] ?? 'ቦሌ መድኃኔዓለም፣ ኤድና ሞል አጠገብ፣ አዲስ አበባ፣ ኢትዮጵያ',
            'hours_en' => $all['business_hours_en'] ?? 'Mon – Sun: 8:00 AM – 9:00 PM',
            'hours_am' => $all['business_hours_am'] ?? 'ከሰኞ – እሑድ፡ ከጠዋቱ 2:00 – ማታ 3:00',
            'latitude' => (float) ($all['business_lat'] ?? 9.0016),
            'longitude' => (float) ($all['business_lng'] ?? 38.7845),

            // Homepage Hero Section Customization
            'hero_subtitle_en' => $all['hero_subtitle_en'] ?? 'Artisanal Cakes & Breads',
            'hero_subtitle_am' => $all['hero_subtitle_am'] ?? 'ልዩ የክብረ በዓል ኬኮች እና ዳቦ',
            'hero_title_en' => $all['hero_title_en'] ?? 'Baked Fresh with Love',
            'hero_title_am' => $all['hero_title_am'] ?? 'በፍቅር የተዘጋጀ ትኩስ ጣዕም',
            'hero_title2_en' => $all['hero_title2_en'] ?? "Addis Ababa's Favorite",
            'hero_title2_am' => $all['hero_title2_am'] ?? 'የአዲስ አበባ ተመራጭ',
            'hero_desc_en' => $all['hero_desc_en'] ?? 'Handcrafted celebration cakes and warm sourdough delivered fresh to your doorstep.',
            'hero_desc_am' => $all['hero_desc_am'] ?? 'የልደትና የሰርግ ኬኮች እንዲሁም ትኩስ የተፈጥሮ ዳቦዎች በሁሉም የአዲስ አበባ ክፍሎች ባሉበት ይደርሳሉ።',
            'hero_button_text_en' => $all['hero_button_text_en'] ?? 'Order Online',
            'hero_button_text_am' => $all['hero_button_text_am'] ?? 'አሁን ይዘዙ',
            'hero_button_link' => $all['hero_button_link'] ?? '/shop',
            'hero_image_url' => $all['hero_image_url'] ?? '/mockup/hero_banner_hq.jpg',

            // About Page & Section Customization
            'about_tag_en' => $all['about_tag_en'] ?? 'About SweetBakes',
            'about_tag_am' => $all['about_tag_am'] ?? 'ስለ ስዊትቤክስ',
            'about_title_en' => $all['about_title_en'] ?? 'A Bakery You Can Trust',
            'about_title_am' => $all['about_title_am'] ?? 'የሚተማመኑበት የዳቦና ኬክ መጋገሪያ',
            'about_story_en' => $all['about_story_en'] ?? 'At SweetBakes, we believe every celebration starts with the aroma of freshly baked butter, warm vanilla, and handcrafted pastries.',
            'about_story_am' => $all['about_story_am'] ?? 'በስዊትቤክስ እያንዳንዱ በዓል የሚጀምረው አዲስ በተጋገረ ቅቤ፣ ሞቅ ባለ ቫኒላ እና በእጅ በተዘጋጁ ኬኮች መዓዛ ነው።',
            'about_image_url' => $all['about_image_url'] ?? '/mockup/baker_hires.jpg',
            'about_chef_quote_en' => $all['about_chef_quote_en'] ?? '“Making life sweeter, one cake and bread at a time.”',
            'about_chef_quote_am' => $all['about_chef_quote_am'] ?? '“እያንዳንዱን ኬክ እና ዳቦ በፍቅር እና በጥንቃቄ እያዘጋጀን ህይወትዎን እናጣፍጣለን::”',
            'about_chef_title_en' => $all['about_chef_title_en'] ?? '— SweetBakes Master Pastry Chef',
            'about_chef_title_am' => $all['about_chef_title_am'] ?? '— የስዊትቤክስ ዋና ኬክ ሼፍ',
            'about_bullet_1_en' => $all['about_bullet_1_en'] ?? '100% Artisanal & Fresh Daily',
            'about_bullet_1_am' => $all['about_bullet_1_am'] ?? '100% በእጅ የተዘጋጀና ትኩስ',
            'about_bullet_2_en' => $all['about_bullet_2_en'] ?? 'Finest Natural Ingredients',
            'about_bullet_2_am' => $all['about_bullet_2_am'] ?? 'ከምርጥ የተፈጥሮ ግብአቶች የተዘጋጀ',
            'about_bullet_3_en' => $all['about_bullet_3_en'] ?? 'Custom Design Celebration Cakes',
            'about_bullet_3_am' => $all['about_bullet_3_am'] ?? 'ልዩ የክብረ በዓል ኬኮች ዲዛይን',
            'about_bullet_4_en' => $all['about_bullet_4_en'] ?? 'Multi-Branch Addis Delivery',
            'about_bullet_4_am' => $all['about_bullet_4_am'] ?? 'በሁሉም ቅርንጫፎች ፈጣን ማድረሻ',

            // Key Statistics
            'stat_experience_years' => $all['stat_experience_years'] ?? '12+',
            'stat_happy_customers' => $all['stat_happy_customers'] ?? '50k+',
            'stat_daily_fresh' => $all['stat_daily_fresh'] ?? '100%',
            'stat_master_chefs' => $all['stat_master_chefs'] ?? '15+',
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
