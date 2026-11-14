<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchInventory;

class CartController extends Controller
{
    /**
     * Get or create active shopping cart for user/session
     */
    public function getCart(Request $request)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie('cart_session');

        if (!$user && !$sessionId) {
            return response()->json(['cart_id' => null, 'branch_id' => null, 'items' => []]);
        }

        $cart = null;
        if ($user) {
            $cart = Cart::with(['items.product.images', 'items.product.category'])
                ->where('user_id', $user->user_id)
                ->first();
        } elseif ($sessionId) {
            $cart = Cart::with(['items.product.images', 'items.product.category'])
                ->where('session_id', $sessionId)
                ->first();
        }

        if (!$cart) {
            return response()->json(['cart_id' => null, 'branch_id' => null, 'items' => []]);
        }

        $items = $cart->items->map(function ($item) use ($cart) {
            $product = $item->product;
            $price = $product ? $product->getPriceForBranch($cart->branch_id) : 0;
            return [
                'cart_item_id' => $item->cart_item_id,
                'product_id' => $item->product_id,
                'product_name' => $product?->product_name ?? 'Product',
                'image_url' => $product?->images->first()?->image_url ?? '',
                'unit_price' => $price,
                'quantity' => (int) $item->quantity,
                'subtotal' => $price * (int) $item->quantity,
            ];
        });

        return response()->json([
            'cart_id' => $cart->cart_id,
            'branch_id' => $cart->branch_id,
            'items' => $items,
            'total_items' => $items->sum('quantity'),
            'total_amount' => $items->sum('subtotal'),
        ]);
    }

    /**
     * Synchronize entire frontend cart to database
     */
    public function syncCart(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,branch_id',
            'session_id' => 'nullable|string|max:100',
            'items' => 'present|array',
            'items.*.product_id' => 'required|integer|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        $branch = Branch::find($validated['branch_id']);
        if ($branch && (!$branch->is_shopping_enabled || $branch->status !== 'Active')) {
            return response()->json([
                'message' => 'Sorry, this branch is currently unavailable for shopping.'
            ], 422);
        }

        $user = $request->user();
        $sessionId = $validated['session_id'] ?? $request->header('X-Session-Id');

        if (!$user && !$sessionId) {
            $sessionId = 'sess_' . bin2hex(random_bytes(16));
        }

        $cart = null;
        if ($user) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->user_id],
                ['branch_id' => $validated['branch_id'], 'session_id' => $sessionId]
            );
        } else {
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                ['branch_id' => $validated['branch_id']]
            );
        }

        $cart->update(['branch_id' => $validated['branch_id']]);

        // Replace cart items with capped inventory quantities
        CartItem::where('cart_id', $cart->cart_id)->delete();

        foreach ($validated['items'] as $it) {
            $inv = BranchInventory::where('branch_id', $validated['branch_id'])
                ->where('product_id', $it['product_id'])
                ->first();
            $maxAvail = $inv ? max(0, (int)$inv->quantity_on_hand - (int)$inv->reserved_quantity) : 0;
            // Products with zero stock remain unavailable for purchase regardless of price
            $qty = min((int)$it['quantity'], $maxAvail);

            if ($qty > 0) {
                CartItem::create([
                    'cart_id' => $cart->cart_id,
                    'product_id' => $it['product_id'],
                    'quantity' => $qty,
                ]);
            }
        }

        return $this->getCart($request);
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');

        $query = Cart::query();
        if ($user) {
            $query->where('user_id', $user->user_id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return response()->json(['message' => 'No active cart found']);
        }

        $cart = $query->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->cart_id)->delete();
        }

        return response()->json(['message' => 'Cart emptied successfully']);
    }
}
