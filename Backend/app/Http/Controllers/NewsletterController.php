<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsletterSubscription;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $sub = NewsletterSubscription::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            ['status' => 'Active']
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to SweetBakes news & special offers!',
            'subscription' => $sub,
        ], 200);
    }

    public function index()
    {
        $subscribers = NewsletterSubscription::latest()->paginate(30);
        return response()->json($subscribers);
    }
}
