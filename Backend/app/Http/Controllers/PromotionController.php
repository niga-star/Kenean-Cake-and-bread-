<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Validate a promo code against active promotions in database.
     */
    public function validatePromo(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $subtotal = (float) $request->input('subtotal', 0);
        $today = Carbon::today();

        $promo = Promotion::whereRaw('UPPER(promotion_name) = ?', [$code])
            ->where('status', 'Active')
            ->first();

        if (!$promo) {
            return response()->json([
                'valid' => false,
                'message' => 'Promotion code "' . $code . '" not found or inactive.',
            ], 422);
        }

        // Date validity check
        if ($promo->start_date && Carbon::parse($promo->start_date)->startOfDay()->gt($today)) {
            return response()->json([
                'valid' => false,
                'message' => 'Promotion code "' . $code . '" is not yet active.',
            ], 422);
        }

        if ($promo->end_date && Carbon::parse($promo->end_date)->endOfDay()->lt($today)) {
            return response()->json([
                'valid' => false,
                'message' => 'Promotion code "' . $code . '" has expired.',
            ], 422);
        }

        // Calculate discount
        $discountAmount = 0.00;
        if ($promo->discount_type === 'percentage') {
            $discountAmount = round(($subtotal * (float) $promo->discount_value) / 100, 2);
        } else {
            // Fixed discount
            $discountAmount = min($subtotal, (float) $promo->discount_value);
        }

        return response()->json([
            'valid' => true,
            'promotion_id' => $promo->promotion_id,
            'promotion_name' => $promo->promotion_name,
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
            'discount_amount' => $discountAmount,
            'message' => $promo->discount_type === 'percentage'
                ? "{$promo->discount_value}% discount applied successfully!"
                : "ETB {$promo->discount_value} flat discount applied successfully!",
        ]);
    }

    /**
     * Return public active promotions list for banners.
     */
    public function activePromotions()
    {
        $today = Carbon::today();
        $promos = Promotion::where('status', 'Active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->select(['promotion_id', 'promotion_name', 'discount_type', 'discount_value'])
            ->get();

        return response()->json($promos);
    }
}
