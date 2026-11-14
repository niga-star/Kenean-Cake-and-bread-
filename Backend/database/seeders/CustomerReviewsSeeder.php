<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;

class CustomerReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $usersData = [
            ['full_name' => 'Sara Ahmed', 'email' => 'sara.ahmed@sweetbakes.com', 'phone' => '+251911223301'],
            ['full_name' => 'Daniel Kebede', 'email' => 'daniel.kebede@sweetbakes.com', 'phone' => '+251911223302'],
            ['full_name' => 'Mekdes Tadesse', 'email' => 'mekdes.tadesse@sweetbakes.com', 'phone' => '+251911223303'],
            ['full_name' => 'Almaz Hailu', 'email' => 'almaz.hailu@sweetbakes.com', 'phone' => '+251911223304'],
            ['full_name' => 'Yonas Bekele', 'email' => 'yonas.bekele@sweetbakes.com', 'phone' => '+251911223305'],
            ['full_name' => 'Hanan Mohammed', 'email' => 'hanan.mohammed@sweetbakes.com', 'phone' => '+251911223306'],
        ];

        $userIds = [];
        foreach ($usersData as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'full_name' => $u['full_name'],
                    'phone' => $u['phone'],
                    'password' => Hash::make('ReviewCustomer2026!'),
                    'role' => 'Customer',
                    'status' => 'Active',
                    'email_verified_at' => now(),
                ]
            );
            $userIds[$u['full_name']] = $user->user_id;
        }

        // Available products
        $prodTruffle = Product::where('product_name', 'like', '%Chocolate Truffle%')->first() ?? Product::find(2);
        $prodCroissant = Product::where('product_name', 'like', '%Croissant%')->first() ?? Product::find(4);
        $prodStrawberry = Product::where('product_name', 'like', '%Strawberry%')->first() ?? Product::find(1);
        $prodCupcake = Product::where('product_name', 'like', '%Cupcake%')->first() ?? Product::find(5);
        $prodBaguette = Product::where('product_name', 'like', '%Baguette%')->first() ?? Product::find(3);
        $prodCookie = Product::where('product_name', 'like', '%Cookie%')->first() ?? Product::find(6);

        $reviews = [
            [
                'user' => 'Sara Ahmed',
                'product_id' => $prodTruffle?->product_id ?? 1,
                'rating' => 5,
                'comment' => 'The Chocolate Truffle Cake was absolutely sublime! So rich, moist, and arrived perfectly chilled at our Dessie family celebration. Everyone was asking where we ordered it!',
            ],
            [
                'user' => 'Daniel Kebede',
                'product_id' => $prodCroissant?->product_id ?? 2,
                'rating' => 5,
                'comment' => 'Best French croissants in Dessie! Golden, flaky, and packed with real butter flavor. Pairs wonderfully with their espresso every morning.',
            ],
            [
                'user' => 'Mekdes Tadesse',
                'product_id' => $prodStrawberry?->product_id ?? 1,
                'rating' => 5,
                'comment' => 'Ordered the Strawberry Shortcake for my daughter\'s birthday. Fresh berries, light sponge, and elegant piping. Exceeded our highest expectations!',
            ],
            [
                'user' => 'Almaz Hailu',
                'product_id' => $prodCupcake?->product_id ?? 3,
                'rating' => 5,
                'comment' => 'The red velvet cupcakes with cream cheese frosting are pure heaven. Fast delivery right to our office in Piazza, beautifully packaged.',
            ],
            [
                'user' => 'Yonas Bekele',
                'product_id' => $prodBaguette?->product_id ?? 4,
                'rating' => 5,
                'comment' => 'Crispy crust and soft airy crumb. You can immediately tell they use traditional sourdough fermentation. Our go-to bakery every single weekend!',
            ],
            [
                'user' => 'Hanan Mohammed',
                'product_id' => $prodCookie?->product_id ?? 5,
                'rating' => 5,
                'comment' => 'Their custom cake team brought our anniversary sketch to life in 3D! Plus the chocolate chip cookies are chewy and loaded with chocolate.',
            ],
        ];

        foreach ($reviews as $idx => $r) {
            Review::updateOrCreate(
                [
                    'user_id' => $userIds[$r['user']],
                    'product_id' => $r['product_id'],
                ],
                [
                    'rating' => $r['rating'],
                    'comment' => $r['comment'],
                    'created_at' => now()->subDays(14 - ($idx * 2)),
                ]
            );
        }
    }
}

