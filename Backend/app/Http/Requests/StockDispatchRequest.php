<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Main Branch Manager and Super Admin only
        return in_array($user->role, ['Main Branch Manager', 'Super Admin']) ||
            ($user->role === 'Branch Manager' && $user->branch?->branch_type === 'Main');
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        if (is_array($items)) {
            $normalizedItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    if (isset($item['quantity']) && !isset($item['quantity_dispatched'])) {
                        $item['quantity_dispatched'] = $item['quantity'];
                    }
                    $normalizedItems[] = $item;
                }
            }
            $this->merge(['items' => $normalizedItems]);
        }
    }

    public function rules(): array
    {
        return [
            'to_branch_id' => 'required|exists:branches,branch_id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity_dispatched' => 'required|integer|min:1|max:100000',
        ];
    }

    public function messages(): array
    {
        return [
            'to_branch_id.required' => 'Destination branch is required.',
            'items.required' => 'At least one product item must be included in the dispatch.',
            'items.*.quantity_dispatched.min' => 'Dispatched quantity must be at least 1.',
        ];
    }
}
