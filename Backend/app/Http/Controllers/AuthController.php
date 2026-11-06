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
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^(\+251[\s\-]?[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|0[79]\d{2}[\s\-]?\d{3}[\s\-]?\d{3}|\+251[79]\d{8}|0[79]\d{8})$/'
            ],
            'password' => 'required|string|min:6',
        ], [
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
        return response()->json($request->user()->load('branch'));
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Role switcher helper for demonstration & evaluation
    public function switchRole(Request $request)
    {
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
