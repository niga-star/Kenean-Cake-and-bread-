<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Models\BranchInventory;
use App\Models\User;
use App\Models\Banner;
use App\Models\AppSetting;
use Carbon\Carbon;

class StorefrontDynamicSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Standardize Categories
        $categoriesData = [
            ['id' => 1, 'name' => 'Cakes', 'slug' => 'cakes', 'desc' => 'Handcrafted artisan celebration cakes'],
            ['id' => 2, 'name' => 'Bread', 'slug' => 'bread', 'desc' => 'Wholesome artisan loaves & sourdough'],
            ['id' => 3, 'name' => 'Pastries', 'slug' => 'pastries', 'desc' => 'Flaky French butter croissants & pastries'],
            ['id' => 4, 'name' => 'Cupcakes', 'slug' => 'cupcakes', 'desc' => 'Signature frosted cupcakes'],
            ['id' => 5, 'name' => 'Cookies', 'slug' => 'cookies', 'desc' => 'Fresh baked cookies & snacks'],
        ];

        foreach ($categoriesData as $c) {
            Category::updateOrCreate(
                ['category_id' => $c['id']],
                [
                    'category_name' => $c['name'],
                    'slug' => $slug = $c['slug'],
                    'description' => $c['desc'],
                ]
            );
        }

        // 2. Ensure Core Products with Proper Categories
        Product::where('product_id', 1)->update(['category_id' => 1, 'product_name' => 'Chocolate Cake', 'base_price' => 850.00, 'status' => 'Active']);
        Product::where('product_id', 2)->update(['category_id' => 2, 'product_name' => 'Fresh Bread', 'base_price' => 400.00, 'status' => 'Active']);
        Product::where('product_id', 3)->update(['category_id' => 1, 'product_name' => 'Vanilla Cake', 'base_price' => 750.00, 'status' => 'Active']);
        Product::where('product_id', 4)->update(['category_id' => 3, 'product_name' => 'Croissant', 'base_price' => 250.00, 'status' => 'Active']);
        Product::where('product_id', 5)->update(['category_id' => 2, 'product_name' => 'Whole Wheat Bread', 'base_price' => 55.00, 'status' => 'Active']);
        Product::where('product_id', 6)->update(['category_id' => 3, 'product_name' => 'Blueberry Muffin Box', 'base_price' => 320.00, 'status' => 'Active']);
        Product::where('product_id', 7)->update(['category_id' => 2, 'product_name' => 'Rustic Rye Sourdough Loaf', 'base_price' => 250.00, 'status' => 'Active']);

        if (Product::where('product_id', 12)->exists()) {
            Product::where('product_id', 12)->update(['category_id' => 1, 'status' => 'Active']);
        }
        if (Product::where('product_id', 13)->exists()) {
            Product::where('product_id', 13)->update(['category_id' => 1, 'status' => 'Active']);
        }
        if (Product::where('product_id', 14)->exists()) {
            Product::where('product_id', 14)->update(['category_id' => 4, 'product_name' => 'Cupcakes', 'base_price' => 250.00, 'status' => 'Active']);
        }

        $strawberryCake = Product::updateOrCreate(
            ['product_name' => 'Strawberry Cake'],
            [
                'category_id' => 1,
                'description' => 'Light sponge cake infused with fresh wild strawberry compote and whipped cream.',
                'base_price' => 850.00,
                'shelf_life_hours' => 48,
                'is_fasting' => false,
                'status' => 'Active',
            ]
        );

        $cookies = Product::updateOrCreate(
            ['product_name' => 'Cookies'],
            [
                'category_id' => 5,
                'description' => 'Batch-baked chocolate chunk and butter hazelnut cookies.',
                'base_price' => 300.00,
                'shelf_life_hours' => 72,
                'is_fasting' => false,
                'status' => 'Active',
            ]
        );

        $pastries = Product::updateOrCreate(
            ['product_name' => 'Pastries'],
            [
                'category_id' => 3,
                'description' => 'Assorted flaky fruit danishes and custard twists.',
                'base_price' => 350.00,
                'shelf_life_hours' => 24,
                'is_fasting' => false,
                'status' => 'Active',
            ]
        );

        // Ensure stock for all active products across all branches
        $allProducts = Product::where('status', 'Active')->get();
        $branches = [1, 2, 3, 4, 5];
        foreach ($allProducts as $p) {
            foreach ($branches as $bid) {
                BranchInventory::updateOrCreate(
                    ['branch_id' => $bid, 'product_id' => $p->product_id],
                    [
                        'quantity_on_hand' => 45,
                        'reserved_quantity' => 0,
                        'reorder_level' => 8,
                    ]
                );
            }
        }

        // 3. Seed Images in MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductImage::truncate();
        Review::truncate();
        Promotion::truncate();
        PromotionProduct::truncate();
        Banner::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $imagesConfig = [
            // Product 1: Chocolate Cake
            1 => [
                ['url' => '/mockup/shop_chocolate_cake.jpg', 'primary' => true, 'sort' => 0],
                ['url' => '/mockup/detail_thumb_2.jpg', 'primary' => false, 'sort' => 1],
                ['url' => '/mockup/detail_thumb_3.jpg', 'primary' => false, 'sort' => 2],
                ['url' => '/mockup/detail_thumb_4.jpg', 'primary' => false, 'sort' => 3],
            ],
            // Product 2: Fresh Bread
            2 => [
                ['url' => '/mockup/shop_fresh_bread.jpg', 'primary' => true, 'sort' => 0],
                ['url' => '/mockup/product_fresh_bread.jpg', 'primary' => false, 'sort' => 1],
            ],
            // Product 3: Vanilla Cake
            3 => [
                ['url' => '/mockup/shop_vanilla_cake.jpg', 'primary' => true, 'sort' => 0],
                ['url' => '/mockup/product_vanilla_cake.jpg', 'primary' => false, 'sort' => 1],
            ],
            // Product 4: Croissant
            4 => [
                ['url' => '/mockup/shop_croissant.jpg', 'primary' => true, 'sort' => 0],
                ['url' => '/mockup/product_croissant.jpg', 'primary' => false, 'sort' => 1],
            ],
            // Product 5: Whole Wheat Bread
            5 => [
                ['url' => '/mockup/product_fresh_bread.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Product 6: Blueberry Muffin Box
            6 => [
                ['url' => '/mockup/shop_pastries.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Product 7: Rustic Rye Sourdough Loaf
            7 => [
                ['url' => '/mockup/shop_fresh_bread.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Product 12: Ye'Tsom Dark Chocolate Cake
            12 => [
                ['url' => '/mockup/detail_main_cake.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Product 13: Ye'Tsom Cinnamon Apple Loaf
            13 => [
                ['url' => '/mockup/shop_fresh_bread.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Product 14: Cupcakes
            14 => [
                ['url' => '/mockup/shop_cupcakes.jpg', 'primary' => true, 'sort' => 0],
                ['url' => '/mockup/product_cupcakes.jpg', 'primary' => false, 'sort' => 1],
            ],
            // Strawberry Cake
            $strawberryCake->product_id => [
                ['url' => '/mockup/shop_strawberry_cake.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Cookies
            $cookies->product_id => [
                ['url' => '/mockup/shop_cookies.jpg', 'primary' => true, 'sort' => 0],
            ],
            // Pastries
            $pastries->product_id => [
                ['url' => '/mockup/shop_pastries.jpg', 'primary' => true, 'sort' => 0],
            ],
        ];

        foreach ($imagesConfig as $pid => $imgs) {
            if (Product::where('product_id', $pid)->exists()) {
                foreach ($imgs as $img) {
                    ProductImage::create([
                        'product_id' => $pid,
                        'image_url' => $img['url'],
                        'sort_order' => $img['sort'],
                    ]);
                }
            }
        }

        // 4. Seed Real Reviews in Database
        $defaultUser = User::first();
        $userId = $defaultUser ? $defaultUser->user_id : 1;

        $reviewsSeed = [
            ['user_id' => $userId, 'product_id' => 1, 'rating' => 5, 'comment' => 'The chocolate cake was amazing! Fresh, beautiful and delicious. Highly recommended!'],
            ['user_id' => $userId, 'product_id' => 2, 'rating' => 5, 'comment' => 'Great service and tasty bread. Will order again soon!'],
            ['user_id' => $userId, 'product_id' => 1, 'rating' => 5, 'comment' => 'Best bakery in Addis! The custom cake exceeded my expectations.'],
            ['user_id' => $userId, 'product_id' => 3, 'rating' => 5, 'comment' => 'The vanilla bean frosting is subtle and creamy. A celebration favorite.'],
            ['user_id' => $userId, 'product_id' => 4, 'rating' => 5, 'comment' => 'Flaky, buttery layers, fresh out of the morning oven. Best croissants in town.'],
            ['user_id' => $userId, 'product_id' => 14, 'rating' => 5, 'comment' => 'SweetBakes cupcakes are the absolute hit at our office parties. Truly top tier in Ethiopia!'],
            ['user_id' => $userId, 'product_id' => 7, 'rating' => 5, 'comment' => 'The sourdough bread has that artisan golden crust. My daily morning ritual in Bole.'],
            ['user_id' => $userId, 'product_id' => 12, 'rating' => 5, 'comment' => 'Ordered the Ye’Tsom fasting cake for our celebration. Perfectly moist and flavorful!'],
            ['user_id' => $userId, 'product_id' => $strawberryCake->product_id, 'rating' => 5, 'comment' => 'Delivered warm to CMC in under 35 minutes! SweetBakes is our family’s go-to bakery.'],
            ['user_id' => $userId, 'product_id' => $cookies->product_id, 'rating' => 5, 'comment' => 'Delicious chocolate chunks and crispy edges!'],
        ];

        foreach ($reviewsSeed as $rev) {
            Review::create($rev);
        }

        // 5. Seed Active Promotions in MySQL
        $promo = Promotion::create([
            'promotion_name' => 'SPECIAL15',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addMonths(6),
            'status' => 'Active',
        ]);

        PromotionProduct::create(['promotion_id' => $promo->promotion_id, 'product_id' => 1]);
        PromotionProduct::create(['promotion_id' => $promo->promotion_id, 'product_id' => 3]);

        // 6. Seed Dynamic Banners
        $banners = [
            [
                'title_en' => 'Handcrafted with Love, Baked Fresh Every Dawn',
                'title_am' => 'በፍቅር የተዘጋጀ፣ በየማለዳው ትኩስ የሚጋገር',
                'subtitle_en' => 'Artisanal Celebration Cakes & Gourmet Pastries',
                'subtitle_am' => 'የክብረ በዓል ኬኮች እና ጣፋጭ ፔስትሪዎች',
                'description_en' => 'Order bespoke designer cakes, crispy French croissants, and artisanal breads delivered straight to your door across Addis Ababa.',
                'description_am' => 'የልደት እና ሰርግ ኬኮች፣ የፈረንሳይ ክሩአሳን እና የተፈጥሮ ዳቦዎችን በሁሉም የአዲስ አበባ ክፍሎች ባሉበት እናደርሳለን።',
                'image_url' => '/mockup/hero_cake.jpg',
                'link_url' => '/shop',
                'button_text_en' => 'Order Online',
                'button_text_am' => 'አሁን ይዘዙ',
                'badge_en' => "Addis Ababa's Favorite Bakery",
                'badge_am' => 'የአዲስ አበባ ተመራጭ ዳቦ ቤት',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title_en' => 'Custom Celebration & Wedding Cakes',
                'title_am' => 'ልዩ የሰርግ እና የልደት ኬክ ማዘዣ',
                'subtitle_en' => 'Bring Your Dream Design to Life',
                'subtitle_am' => 'የሚያልሙትን ዲዛይን እውን ያድርጉ',
                'description_en' => 'Choose your flavors, upload reference photos, and get an instant custom quote prepared by our master decorators.',
                'description_am' => 'የሚፈልጉትን ጣዕም ይምረጡ፣ ፎቶዎችን ይላኩ እና ፈጣን ዋጋ ከዋና ኬክ አዘጋጆቻችን ያግኙ።',
                'image_url' => '/mockup/hero_banner_full.jpg',
                'link_url' => '/custom-cake',
                'button_text_en' => 'Design Your Cake',
                'button_text_am' => 'ኬክዎን ይንደፉ',
                'badge_en' => 'Bespoke Studio',
                'badge_am' => 'ልዩ የኬክ ዲዛይን',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title_en' => 'Golden Crust Artisan Breads & Pastries',
                'title_am' => 'ትኩስ የጠዋት ዳቦ እና ክሩአሳን',
                'subtitle_en' => 'Slow-Fermented Sourdough & Buttery Croissants',
                'subtitle_am' => 'በተፈጥሮ እርሾ የተዘጋጀ ዳቦ እና ቅቤ ክሩአሳን',
                'description_en' => 'Start your mornings with fresh golden sourdough, flaky fruit danishes, and rich dark roast coffee.',
                'description_am' => 'ማለዳዎን በወርቃማ የተፈጥሮ ዳቦ እና በጣፋጭ ክሩአሳን ከትኩስ ቡና ጋር ይጀምሩ።',
                'image_url' => '/mockup/hero_right_hires.jpg',
                'link_url' => '/shop?category=pastries',
                'button_text_en' => 'Explore Pastries',
                'button_text_am' => 'ፔስትሪዎችን ይመልከቱ',
                'badge_en' => 'Baked Hourly',
                'badge_am' => 'በየሰዓቱ ትኩስ',
                'is_active' => true,
                'sort_order' => 3,
            ]
        ];

        foreach ($banners as $b) {
            Banner::create($b);
        }

        // 7. Seed Real Business Info & App Settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Kenean Cake & Bread', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Artisanal Cake & Fresh Daily Bread', 'group' => 'general'],
            ['key' => 'business_phone', 'value' => '+251 911 123 456', 'group' => 'contact'],
            ['key' => 'business_email', 'value' => 'info@keneanbakery.com', 'group' => 'contact'],
            ['key' => 'business_address_en', 'value' => 'Dessie Piazza, Next to Arada Market, Dessie, Ethiopia', 'group' => 'contact'],
            ['key' => 'business_address_am', 'value' => 'ደሴ ፒያሳ፣ ከአራዳ ገበያ አጠገብ፣ ደሴ፣ ኢትዮጵያ', 'group' => 'contact'],
            ['key' => 'business_hours_en', 'value' => 'Mon – Sun: 7:00 AM – 9:00 PM', 'group' => 'contact'],
            ['key' => 'business_hours_am', 'value' => 'ከሰኞ – እሑድ፡ ከጠዋቱ 1:00 – ማታ 3:00', 'group' => 'contact'],
            ['key' => 'business_lat', 'value' => '11.1198', 'group' => 'contact'],
            ['key' => 'business_lng', 'value' => '39.6342', 'group' => 'contact'],
            ['key' => 'about_story_en', 'value' => 'At Kenean Cake & Bread, we believe every celebration starts with the aroma of freshly baked butter, warm vanilla, and handcrafted pastries. Founded in Dessie, Ethiopia, our master bakers combine timeless artisanal techniques with rich local flavors to create unforgettable cakes and oven-fresh daily bread.', 'group' => 'about'],
            ['key' => 'about_story_am', 'value' => 'በከነአን ኬክ እና ዳቦ እያንዳንዱ በዓል የሚጀምረው አዲስ በተጋገረ ቅቤ፣ ሞቅ ባለ ቫኒላ እና በእጅ በተዘጋጁ ኬኮች መዓዛ ነው። በደሴ ከተማ የተመሰረተው ዳቦ መጋገሪያችን ዘመናዊ የእጅ ጥበብን ከሀገር በቀል ጣዕሞች ጋር በማዋሃድ የማይረሱ ኬኮችንና ትኩስ ዳቦዎችን ያቀርባል።', 'group' => 'about'],
            ['key' => 'stat_experience_years', 'value' => '12+', 'group' => 'about'],
            ['key' => 'stat_happy_customers', 'value' => '50k+', 'group' => 'about'],
            ['key' => 'stat_daily_fresh', 'value' => '100%', 'group' => 'about'],
            ['key' => 'stat_master_chefs', 'value' => '15+', 'group' => 'about'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/keneanbakery', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/keneanbakery', 'group' => 'social'],
            ['key' => 'social_telegram', 'value' => 'https://t.me/keneanbakery', 'group' => 'social'],
            ['key' => 'social_tiktok', 'value' => 'https://tiktok.com/@keneanbakery', 'group' => 'social'],
        ];

        foreach ($settings as $s) {
            AppSetting::updateOrCreate(['key' => $s['key']], ['value' => $s['value'], 'group' => $s['group']]);
        }
    }
}
