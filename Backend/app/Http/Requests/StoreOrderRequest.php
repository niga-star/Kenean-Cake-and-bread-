<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Harmonize field aliases from storefront
        if (!$this->has('fulfillment_method') && $this->has('order_type')) {
            $this->merge(['fulfillment_method' => $this->input('order_type')]);
        }

        if ($this->has('payment_method') || $this->has('payment_type')) {
            $rawPayment = $this->input('payment_method') ?? $this->input('payment_type');
            $mappedPayment = match ($rawPayment) {
                'Online', 'online', 'Chapa', 'chapa' => 'Chapa',
                'Telebirr', 'telebirr' => 'Telebirr',
                'CBE Birr', 'cbe_birr', 'cbe', 'CBE' => 'CBE Birr',
                'Pickup', 'Pay on pickup', 'pay_on_pickup' => 'Pay on pickup',
                'Cash on Delivery', 'COD', 'cod', 'Pay on delivery', 'pay_on_delivery' => 'Pay on delivery',
                default => 'Pay on delivery',
            };
            $this->merge(['payment_method' => $mappedPayment]);
        }
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,branch_id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:40',
            'customer_email' => 'nullable|email|max:150',
            'fulfillment_method' => 'required|in:Delivery,Pickup',
            'address_id' => 'nullable|exists:addresses,address_id',
            'delivery_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:Chapa,Telebirr,CBE Birr,Pay on pickup,Pay on delivery',
            'promo_code' => 'nullable|string|max:50',
            'promotion_code' => 'nullable|string|max:50',
        ];
    }
}

