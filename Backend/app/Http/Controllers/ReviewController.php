<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use App\Models\AuditLog;

class ReviewController extends Controller
{
    /**
     * Get featured customer reviews for the Home Page Testimonials Carousel
     * Genuine database records only. If no reviews exist, returns empty array.
     */
    public function featured()
    {
        $reviews = Review::with(['user', 'product'])
            ->latest()
            ->take(12)
            ->get();

        $results = [];

        foreach ($reviews as $rev) {
            $name = $rev->user?->full_name ?? 'Verified Customer';
            $results[] = [
                'review_id' => $rev->review_id,
                'name' => $name,
                'avatar' => null,
                'rating' => (int) $rev->rating,
                'quote' => $rev->comment,
                'product_id' => $rev->product_id,
                'product_name' => $rev->product?->product_name ?? 'Artisan Pastry',
                'created_at' => $rev->created_at?->diffForHumans() ?? 'Recently',
            ];
        }

        // Group into slides of 3 for the carousel
        $slides = array_chunk($results, 3);

        return response()->json([
            'slides' => $slides,
            'total' => count($results),
        ]);
    }

    /**
     * Get real reviews and genuine rating breakdown for a specific product
     */
    public function productReviews($id)
    {
        $product = Product::findOrFail($id);
        $reviews = Review::with('user')
            ->where('product_id', $id)
            ->latest()
            ->get();

        $total = $reviews->count();
        $avg = $total > 0 ? round((float) $reviews->avg('rating'), 1) : 0.0;

        // Calculate genuine star counts
        $counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $r) {
            $star = (int) $r->rating;
            if (isset($counts[$star])) {
                $counts[$star]++;
            }
        }

        $breakdown = [];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $breakdown[] = [
                'stars' => $star,
                'count' => $counts[$star],
                'percentage' => $total > 0 ? round(($counts[$star] / $total) * 100) : 0,
            ];
        }

        $list = $reviews->map(function ($r) {
            return [
                'review_id' => $r->review_id,
                'author' => $r->user?->full_name ?? 'Verified SweetBakes Customer',
                'rating' => (int) $r->rating,
                'date' => $r->created_at?->format('M d, Y') ?? 'Recently',
                'comment' => $r->comment,
            ];
        });

        return response()->json([
            'average_rating' => $avg,
            'total_reviews' => $total,
            'breakdown' => $breakdown,
            'reviews' => $list->values()->all(),
        ]);
    }

    /**
     * Submit a customer review
     */
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $userId = $user ? $user->user_id : User::first()?->user_id ?? 1;

        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Thank you! Your review has been submitted.',
            'review' => $review->load('user'),
        ], 201);
    }

    /**
     * Admin: List all reviews
     */
    public function adminIndex()
    {
        $reviews = Review::with(['user', 'product'])
            ->latest()
            ->paginate(25);

        return response()->json($reviews);
    }

    /**
     * Admin: Delete a review
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        AuditLog::create([
            'user_id' => $request->user()?->user_id,
            'user_name' => $request->user()?->full_name ?? 'Super Admin',
            'action' => 'Review deleted',
            'record' => 'Review #' . $id,
            'entity_type' => 'Review',
            'entity_id' => (string) $id,
            'old_value' => 'Active',
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
