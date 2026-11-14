<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([]);
        }

        $addresses = Address::where('user_id', $user->user_id)
            ->latest()
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'street' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $user->user_id)->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id' => $user->user_id,
            'street' => $validated['street'],
            'city' => $validated['city'] ?? 'Dessie',
            'region' => $validated['region'] ?? 'Amhara',
            'notes' => $validated['notes'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'message' => 'Delivery address saved successfully.',
            'address' => $address,
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $address = Address::where('user_id', $user->user_id)
            ->where('address_id', $id)
            ->firstOrFail();

        $address->delete();

        return response()->json(['message' => 'Address removed successfully.']);
    }

    public function makeDefault(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        Address::where('user_id', $user->user_id)->update(['is_default' => false]);
        $address = Address::where('user_id', $user->user_id)
            ->where('address_id', $id)
            ->firstOrFail();

        $address->is_default = true;
        $address->save();

        return response()->json([
            'message' => 'Default delivery address updated.',
            'address' => $address,
        ]);
    }
}
