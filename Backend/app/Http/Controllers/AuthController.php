<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            AuditLog::create([
                'user_id' => $user?->user_id,
                'user_name' => $user ? ($user->full_name . ' (' . $user->role . ')') : 'Unknown (' . $validated['email'] . ')',
                'action' => 'Failed login attempt',
                'record' => $validated['email'],
                'entity_type' => 'Auth',
                'entity_id' => (string) ($user?->user_id ?? 0),
                'old_value' => '—',
                'new_value' => 'Invalid credentials',
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        if ($user->status !== 'Active') {
            return response()->json([
                'message' => 'This account has been deactivated.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Login',
            'record' => $user->role,
            'entity_type' => 'Auth',
            'entity_id' => (string) $user->user_id,
            'old_value' => '—',
            'new_value' => 'Success',
            'created_at' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => $user->load('branch'),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => [
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
                'unique:users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^(\+251[\s\-]?[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|0[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|\+251[79]\d{8}|0[79]\d{8})$/'
            ],
            'password' => 'required|string|min:6',
        ], [
            'full_name.required' => 'Full name is required.',
            'full_name.min' => 'Full name must be at least 3 characters.',
            'full_name.max' => 'Full name cannot exceed 25 characters.',
            'full_name.regex' => 'Full name must contain only letters and spaces (no numbers or special characters).',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Email must follow the Gmail format (e.g. example@gmail.com).',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Phone numbers must follow valid Ethiopian format: +251 9XX XXX XXX, +251 7XX XXX XXX, or 09XX XXX XXX.',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'Customer',
            'branch_id' => null,
            'status' => 'Active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if ($user && $user->status !== 'Active') {
            if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
            return response()->json(['message' => 'Account is inactive or suspended. Please contact the Super Admin.'], 403);
        }
        return response()->json($user ? $user->load('branch') : null);
    }

    public function logout(Request $request)
    {
        $bearer = $request->bearerToken();
        if ($bearer) {
            \Laravel\Sanctum\PersonalAccessToken::findToken($bearer)?->delete();
        }

        if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Branch Managers and users cannot change their account status or role via profile endpoint
        if ($request->has('status') || $request->has('employment_status') || $request->has('role')) {
            if ($user->role !== 'Super Admin') {
                return response()->json([
                    'message' => 'Access denied: You cannot modify account status or role via profile update. Account status is controlled by administrators.'
                ], 403);
            }
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^(\+251[\s\-]?[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|0[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|\+251[79]\d{8}|0[79]\d{8})$/'
            ],
        ], [
            'phone.regex' => 'Phone numbers must follow valid Ethiopian format: +251 9XX XXX XXX, +251 7XX XXX XXX, or 09XX XXX XXX.',
        ]);

        $oldName = $user->full_name;
        $user->full_name = $validated['full_name'];
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        $user->save();

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Profile updated',
            'record' => $user->email,
            'entity_type' => 'User',
            'entity_id' => (string) $user->user_id,
            'old_value' => $oldName,
            'new_value' => $user->full_name,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Profile details updated successfully.',
            'user' => $user->fresh()->load('branch'),
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'current_password' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!empty($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'The provided current password does not match our records.',
                    'errors' => ['current_password' => ['Incorrect current password.']],
                ], 422);
            }
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Password changed',
            'record' => $user->email,
            'entity_type' => 'User',
            'entity_id' => (string) $user->user_id,
            'old_value' => '—',
            'new_value' => 'Password securely updated',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your password has been changed successfully.',
        ]);
    }

    // Role switcher helper for demonstration & evaluation
    public function switchRole(Request $request)
    {
        // In production, ensure only authenticated Super Admin can switch personas
        if (app()->environment('production')) {
            $currentUser = $request->user();
            if (!$currentUser) {
                $tokenString = $request->bearerToken();
                if ($tokenString) {
                    $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
                    $currentUser = $token?->tokenable;
                }
            }
            if (!$currentUser || $currentUser->role !== 'Super Admin') {
                return response()->json(['message' => 'Unauthorized persona simulation.'], 403);
            }
        }

        $validated = $request->validate([
            'role' => 'required|string',
            'branch_code' => 'nullable|string',
        ]);

        $query = User::where('role', $validated['role']);
        if (!empty($validated['branch_code'])) {
            $branch = Branch::where('branch_code', $validated['branch_code'])->first();
            if ($branch) {
                $query->where('branch_id', $branch->branch_id);
            }
        }

        $user = $query->first();

        if (!$user) {
            // fallback to first of that role
            $user = User::where('role', $validated['role'])->first();
        }

        if (!$user) {
            return response()->json(['message' => 'Role user not found'], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('branch'),
        ]);
    }

    public function listUsers()
    {
        $users = User::with('branch')->get();
        return response()->json($users);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return response()->json(['message' => 'If this email exists in our system, a password reset link has been dispatched.'], 200);
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = now()->addMinutes(30);

        DB::table('password_reset_tokens')->insert([
            'user_id' => $user->user_id,
            'email' => $user->email,
            'token' => $plainToken,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Password reset requested',
            'record' => $user->email,
            'entity_type' => 'Auth',
            'entity_id' => (string) $user->user_id,
            'old_value' => 'Active',
            'new_value' => 'Reset Token Issued (Expires in 30 mins)',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Password reset token successfully generated and dispatched.',
            'reset_token' => $plainToken,
            'expires_at' => $expiresAt->toIso8601String(),
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $tokenHash = hash('sha256', $validated['token']);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->where(function ($q) use ($validated, $tokenHash) {
                $q->where('token_hash', $tokenHash)
                  ->orWhere('token', $validated['token']);
            })
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Invalid, expired, or already used password reset token.',
            ], 422);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Mark token as used (single-use enforced)
        DB::table('password_reset_tokens')
            ->where('token_id', $resetRecord->token_id)
            ->update(['used_at' => now()]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'user_name' => $user->full_name . ' (' . $user->role . ')',
            'action' => 'Password reset completed',
            'record' => $user->email,
            'entity_type' => 'Auth',
            'entity_id' => (string) $user->user_id,
            'old_value' => 'Token Issued',
            'new_value' => 'Password Updated',
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Your password has been successfully reset. You may now log in with your new credentials.',
        ], 200);
    }
}
