<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\BranchInventory;

class SweetBakesAlignmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chocolate Cake - ETB 850
        Product::where('product_id', 1)->update([
            'product_name' => 'Chocolate Cake',
            'base_price' => 850.00,
            'status' => 'Active',
        ]);

        // 2. Vanilla Cake - ETB 750
        Product::where('product_id', 3)->update([
            'product_name' => 'Vanilla Cake',
            'base_price' => 750.00,
            'status' => 'Active',
        ]);

        // 3. Fresh Bread - ETB 400
        Product::where('product_id', 2)->update([
            'product_name' => 'Fresh Bread',
            'base_price' => 400.00,
            'status' => 'Active',
        ]);

        // 4. Cupcakes - ETB 250
        $cupcakes = Product::updateOrCreate(
            ['product_name' => 'Cupcakes'],
            [
                'category_id' => 4,
                'description' => 'Assorted gourmet cupcakes with signature vanilla, strawberry and mint buttercream swirls.',
                'base_price' => 250.00,
                'shelf_life_hours' => 24,
                'is_fasting' => false,
                'status' => 'Active',
            ]
        );

        // 5. Croissant - ETB 250
        Product::where('product_id', 4)->update([
            'product_name' => 'Croissant',
            'base_price' => 250.00,
            'status' => 'Active',
        ]);

        $pids = [1, 2, 3, 4, $cupcakes->product_id];
        $branches = [1, 2, 3, 4];

        foreach ($pids as $pid) {
            foreach ($branches as $bid) {
                BranchInventory::updateOrCreate(
                    ['branch_id' => $bid, 'product_id' => $pid],
                    [
                        'quantity_on_hand' => 40,
                        'reserved_quantity' => 0,
                        'reorder_level' => 5,
                        'status' => 'Healthy',
                    ]
                );
            }
        }
    }
}
