<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\Banner;
use App\Models\AuditLog;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        // Keep Banner 1 (Primary hero banner) synchronized if hero keys are submitted
        $bannerUpdate = [];
        if (isset($data['hero_title_en'])) $bannerUpdate['title_en'] = $data['hero_title_en'];
        if (isset($data['hero_title_am'])) $bannerUpdate['title_am'] = $data['hero_title_am'];
        if (isset($data['hero_subtitle_en'])) $bannerUpdate['subtitle_en'] = $data['hero_subtitle_en'];
        if (isset($data['hero_subtitle_am'])) $bannerUpdate['subtitle_am'] = $data['hero_subtitle_am'];
        if (isset($data['hero_title2_en'])) $bannerUpdate['badge_en'] = $data['hero_title2_en'];
        if (isset($data['hero_title2_am'])) $bannerUpdate['badge_am'] = $data['hero_title2_am'];
        if (isset($data['hero_desc_en'])) $bannerUpdate['description_en'] = $data['hero_desc_en'];
        if (isset($data['hero_desc_am'])) $bannerUpdate['description_am'] = $data['hero_desc_am'];
        if (isset($data['hero_button_text_en'])) $bannerUpdate['button_text_en'] = $data['hero_button_text_en'];
        if (isset($data['hero_button_text_am'])) $bannerUpdate['button_text_am'] = $data['hero_button_text_am'];
        if (isset($data['hero_button_link'])) $bannerUpdate['link_url'] = $data['hero_button_link'];
        if (isset($data['hero_image_url'])) $bannerUpdate['image_url'] = $data['hero_image_url'];

        if (!empty($bannerUpdate)) {
            $banner = Banner::find(1);
            if ($banner) {
                $banner->update($bannerUpdate);
            }
        }

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Settings updated',
            'record' => 'System & Business Settings',
            'entity_type' => 'AppSetting',
            'entity_id' => 'all',
            'old_value' => '—',
            'new_value' => 'Updated by Super Admin',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Settings saved successfully', 'settings' => AppSetting::all()->pluck('value', 'key')]);
    }
}
