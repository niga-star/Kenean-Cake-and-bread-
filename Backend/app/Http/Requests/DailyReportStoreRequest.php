<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Sellers, Cashiers, Branch Staff, and Branch Managers
        return in_array($user->role, [
            'Branch Seller',
            'Branch Seller / Cashier',
            'Cashier',
            'Branch Staff',
            'Branch Manager',
            'Branch Admin',
            'Main Branch Manager',
        ]);
    }

    public function rules(): array
    {
        return [
            'report_date' => 'required|date',
            'report_reference' => 'nullable|string|max:100',
            'action' => 'required|in:draft,submit',
            'orders' => 'present|array',
            'orders.*.order_id' => 'nullable|integer',
            'orders.*.order_number' => 'required|string',
            'orders.*.product_name' => 'required|string',
            'orders.*.category' => 'nullable|string',
            'orders.*.quantity' => 'required|numeric|min:1',
            'orders.*.unit_price' => 'required|numeric|min:0',
            'orders.*.order_status' => 'nullable|string',
            'orders.*.payment_status' => 'nullable|string',
            'orders.*.customer_info' => 'nullable|string',
            'custom_cakes' => 'present|array',
            'custom_cakes.*.custom_cake_id' => 'nullable|integer',
            'custom_cakes.*.cake_order_number' => 'required|string',
            'custom_cakes.*.customer_name' => 'required|string',
            'custom_cakes.*.cake_category' => 'nullable|string',
            'custom_cakes.*.cake_type_design' => 'nullable|string',
            'custom_cakes.*.size_weight' => 'nullable|string',
            'custom_cakes.*.quantity' => 'required|numeric|min:1',
            'custom_cakes.*.customization_details' => 'nullable|string',
            'custom_cakes.*.unit_price' => 'required|numeric|min:0',
            'custom_cakes.*.advance_payment' => 'nullable|numeric|min:0',
            'custom_cakes.*.delivery_date' => 'nullable|date',
            'custom_cakes.*.order_status' => 'nullable|string',
            'custom_cakes.*.payment_status' => 'nullable|string',
        ];
    }
}

