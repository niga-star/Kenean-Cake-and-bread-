<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Super Admin cannot receive stock directly (Section 5)
        if ($user->role === 'Super Admin') {
            return false;
        }

        // Branch Managers and Branch Staff at non-Main branches can receive stock
        return in_array($user->role, [
            'Branch Manager',
            'Branch Admin',
            'Main Branch Manager',
            'Branch Staff',
            'Branch Seller',
            'Branch Seller / Cashier',
            'Cashier',
            'Servant',
        ]);
    }

    public function rules(): array
    {
        return [
            'dispatch_id' => 'required|integer|exists:stock_dispatches,dispatch_id',
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity_received' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'dispatch_id.required' => 'A valid dispatch reference ID is required to receive incoming branch stock.',
            'dispatch_id.exists' => 'The specified stock dispatch does not exist.',
            'quantity_received.min' => 'Received quantity must be at least 1.',
        ];
    }
}
