<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Only Super Admin and Branch Managers are authorized to adjust inventory
        if ($user->role === 'Super Admin') {
            return true;
        }

        if (in_array($user->role, ['Branch Manager', 'Branch Admin', 'Main Branch Manager']) || 
            str_contains(strtolower($user->role), 'manager')) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,branch_id',
            'product_id' => 'required|exists:products,product_id',
            'type' => 'required|in:production,transfer_dispatch,transfer_receive,damaged,expired,manual_adjustment,restock,correction',
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'required|string|min:3|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.not_in' => 'Adjustment quantity cannot be zero. Specify a positive or negative adjustment amount.',
            'reason.required' => 'A mandatory reason is required for every stock adjustment.',
        ];
    }
}


