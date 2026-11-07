<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    /**
     * Get wishlist items for the authenticated user (or empty if guest).
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        if (!$user) {
            return response()->json(['product_ids' => [], 'products' => []]);
        }

        $wishlists = Wishlist::where('user_id', $user->user_id)
            ->with(['product.images', 'product.category'])
            ->latest()
            ->get();

        $productIds = $wishlists->pluck('product_id')->map(fn($id) => (int) $id)->toArray();
        $products = $wishlists->pluck('product')->filter()->values();

        return response()->json([
            'product_ids' => $productIds,
            'products' => $products,
        ]);
    }

    /**
     * Toggle a single product in wishlist.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
        ]);

        $user = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        $productId = (int) $validated['product_id'];

        if (!$user) {
            return response()->json([
                'success' => true,
                'guest' => true,
                'product_id' => $productId,
                'message' => 'Wishlist updated locally',
            ]);
        }

        $existing = Wishlist::where('user_id', $user->user_id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
        } else {
            Wishlist::firstOrCreate([
                'user_id' => $user->user_id,
                'product_id' => $productId,
            ]);
            $wishlisted = true;
        }

        $currentIds = Wishlist::where('user_id', $user->user_id)
            ->pluck('product_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        return response()->json([
            'success' => true,
            'wishlisted' => $wishlisted,
            'product_ids' => $currentIds,
        ]);
    }

    /**
     * Sync local guest product IDs into authenticated user's wishlist.
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,product_id',
        ]);

        $user = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to sync wishlist to account.',
            ], 401);
        }

        foreach ($validated['product_ids'] as $productId) {
            Wishlist::firstOrCreate([
                'user_id' => $user->user_id,
                'product_id' => (int) $productId,
            ]);
        }

        $wishlists = Wishlist::where('user_id', $user->user_id)
            ->with(['product.images', 'product.category'])
            ->latest()
            ->get();

        $productIds = $wishlists->pluck('product_id')->map(fn($id) => (int) $id)->toArray();
        $products = $wishlists->pluck('product')->filter()->values();

        return response()->json([
            'success' => true,
            'product_ids' => $productIds,
            'products' => $products,
        ]);
    }

    /**
     * Retrieve product cards for guest-saved product IDs from localStorage.
     */
    public function guestProducts(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,product_id',
        ]);

        $products = Product::whereIn('product_id', $validated['product_ids'])
            ->with(['images', 'category'])
            ->get();

        return response()->json([
            'product_ids' => $products->pluck('product_id')->map(fn($id) => (int) $id)->toArray(),
            'products' => $products,
        ]);
    }
}
