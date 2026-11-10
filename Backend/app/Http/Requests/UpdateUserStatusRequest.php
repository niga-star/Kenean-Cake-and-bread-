<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $currentUser = $this->user();
        if (!$currentUser) {
            return false;
        }

        // Managers and Super Admin only
        return in_array($currentUser->role, [
            'Super Admin',
            'Main Branch Manager',
            'Branch Manager',
            'Branch Admin',
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:Active,Inactive,Suspended',
            'status_reason' => 'required_if:status,Suspended|nullable|string|min:3|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Account status must be one of: Active, Inactive, or Suspended.',
            'status_reason.required_if' => 'A mandatory reason is required when placing an account into Suspended status.',
        ];
    }
}
