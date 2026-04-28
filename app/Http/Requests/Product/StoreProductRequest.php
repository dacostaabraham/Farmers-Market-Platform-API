<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:200',
            'sku'         => 'nullable|string|max:50|unique:products,sku',
            'description' => 'nullable|string|max:1000',
            'price_fcfa'  => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
            'unit'        => 'nullable|string|max:50',
        ];
    }
}
