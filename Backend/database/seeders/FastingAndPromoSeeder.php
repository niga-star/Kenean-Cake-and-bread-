<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\BranchInventory;
use App\Models\Promotion;

class FastingAndPromoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Mark existing vegan/bread products as Ye'Tsom (Fasting)
        Product::whereIn('product_id', [2, 5, 7])->update(['is_fasting' => true]);

        // Hide unwanted test items
        Product::whereIn('product_id', [10, 11])->update(['status' => 'Inactive']);

        // 2. Add signature Ethiopian Fasting Cakes
        $fastingCake1 = Product::updateOrCreate(
            ['product_name' => "Ye'Tsom Dark Chocolate Cake (የጾም ቸኮሌት)"],
            [
                'category_id' => 1,
                'description' => '100% plant-based Ethiopian fasting chocolate cake. Rich Dutch cocoa sponge layered with dairy-free dark chocolate ganache, zero eggs or dairy.',
                'base_price' => 820.00,
                'shelf_life_hours' => 48,
                'is_fasting' => true,
                'status' => 'Active',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $fastingCake1->product_id, 'sort_order' => 1],
            [
                'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600&auto=format&fit=crop&q=80',
            ]
        );

        $fastingCake2 = Product::updateOrCreate(
            ['product_name' => "Ye'Tsom Cinnamon Apple Loaf (የጾም አፕል)"],
            [
                'category_id' => 1,
                'description' => 'Tender spiced apple loaf made with crisp apples, warm cinnamon, and virgin sunflower oil. Perfectly balanced, 100% dairy and egg free.',
                'base_price' => 480.00,
                'shelf_life_hours' => 48,
                'is_fasting' => true,
                'status' => 'Active',
            ]
        );

        ProductImage::updateOrCreate(
            ['product_id' => $fastingCake2->product_id, 'sort_order' => 1],
            [
                'image_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=80',
            ]
        );

        // 3. Ensure healthy branch inventory across main branches (1: Main, 2: Bole, 3: Megenagna, 4: Piassa)
        $branchIds = [1, 2, 3, 4];
        $fastingProductIds = [$fastingCake1->product_id, $fastingCake2->product_id, 2, 5, 7];

        foreach ($fastingProductIds as $pid) {
            foreach ($branchIds as $bid) {
                BranchInventory::updateOrCreate(
                    ['branch_id' => $bid, 'product_id' => $pid],
                    [
                        'quantity_on_hand' => 30,
                        'reserved_quantity' => 0,
                        'reorder_level' => 5,
                        'status' => 'Healthy',
                    ]
                );
            }
        }

        // 4. Seed dynamic Promotions for Step 3
        Promotion::updateOrCreate(
            ['promotion_name' => 'KENEAN10'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYear(),
                'status' => 'Active',
            ]
        );

        Promotion::updateOrCreate(
            ['promotion_name' => 'WELCOME50'],
            [
                'discount_type' => 'fixed',
                'discount_value' => 50.00,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYear(),
                'status' => 'Active',
            ]
        );

        Promotion::updateOrCreate(
            ['promotion_name' => 'YETSOM15'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addYear(),
                'status' => 'Active',
            ]
        );
    }
}
