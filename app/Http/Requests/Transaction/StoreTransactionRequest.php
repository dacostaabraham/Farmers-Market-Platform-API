<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'farmer_id'              => 'required|exists:farmers,id',
            'payment_method'         => 'required|in:cash,credit',
            'notes'                  => 'nullable|string|max:500',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'             => 'At least one product item is required.',
            'items.*.product_id.exists'  => 'One or more products do not exist.',
            'items.*.quantity.min'       => 'Quantity must be at least 1.',
        ];
    }
}
