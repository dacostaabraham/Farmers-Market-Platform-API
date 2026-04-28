<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'identifier'        => 'required|string|max:50|unique:farmers,identifier',
            'firstname'         => 'required|string|max:100',
            'lastname'          => 'required|string|max:100',
            'phone'             => 'required|string|max:20|unique:farmers,phone',
            'village'           => 'nullable|string|max:100',
            'credit_limit_fcfa' => 'nullable|integer|min:0',
        ];
    }
}
