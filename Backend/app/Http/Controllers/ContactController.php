<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactInquiry;
use App\Models\Notification;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Normalize phone by stripping any spaces or dashes before validation
        if ($request->has('phone') && is_string($request->phone)) {
            $request->merge([
                'phone' => preg_replace('/[\s\-]+/', '', $request->phone),
            ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:25',
                'regex:/^[\p{L}\s]+$/u',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^(\+251[97]\d{8}|0[97]\d{8})$/',
            ],
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10|max:300',
        ], [
            'name.required' => 'Full Name is required.',
            'name.min' => 'Full Name must be at least 3 characters.',
            'name.max' => 'Full Name cannot exceed 25 characters.',
            'name.regex' => 'Full Name must contain only letters and spaces (no numbers or special characters).',
            'email.required' => 'Email Address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.regex' => 'Email must follow the Gmail format (e.g. example@gmail.com).',
            'phone.required' => 'Phone Number is required.',
            'phone.regex' => 'Please enter a valid Ethiopian phone number (09XXXXXXXX, 07XXXXXXXX, +2519XXXXXXXX, or +2517XXXXXXXX).',
            'message.required' => 'Message is required.',
            'message.min' => 'Message must be at least 10 characters.',
            'message.max' => 'Message cannot exceed 300 characters.',
        ]);

        try {
            $inquiry = ContactInquiry::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
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
