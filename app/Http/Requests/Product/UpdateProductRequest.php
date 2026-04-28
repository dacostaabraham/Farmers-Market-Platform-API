<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:200',
            'description' => 'sometimes|nullable|string|max:1000',
            'price_fcfa'  => 'sometimes|integer|min:1',
            'category_id' => 'sometimes|exists:categories,id',
            'unit'        => 'sometimes|string|max:50',
            'is_active'   => 'sometimes|boolean',
        ];
    }
}
