<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactInquiry;
use App\Models\Notification;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:3000',
        ]);

        try {
            $inquiry = ContactInquiry::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'subject' => $validated['subject'] ?? 'General Inquiry',
                'message' => $validated['message'],
                'status' => 'Unread',
            ]);

            // Dispatch a manager notification
            try {
                Notification::create([
                    'category' => 'System',
                    'title' => 'New Customer Inquiry',
                    'message' => "Message received from {$inquiry->name} ({$inquiry->email}): '{$inquiry->subject}'",
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {}
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info("Customer inquiry submitted: " . json_encode($validated));
            $inquiry = (object) $validated;
        }

        return response()->json([
            'message' => 'Your message was sent to our master bakers! We will contact you shortly.',
            'inquiry' => $inquiry,
        ], 201);
    }

    public function index()
    {
        return response()->json(ContactInquiry::latest()->paginate(20));
    }
}
