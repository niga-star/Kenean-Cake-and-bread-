<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user && $user->role === 'Branch Staff') {
            return false;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,branch_id',
            'product_id' => 'required|exists:products,product_id',
            'type' => 'required|in:production,transfer_dispatch,transfer_receive,damaged,expired,manual_adjustment',
            'quantity' => 'required|integer',
            'reason' => 'required|string|max:500',
        ];
    }
}

