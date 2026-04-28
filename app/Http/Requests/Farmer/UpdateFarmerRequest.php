<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'firstname'         => 'sometimes|string|max:100',
            'lastname'          => 'sometimes|string|max:100',
            'phone'             => 'sometimes|string|max:20|unique:farmers,phone,' . $this->farmer?->id,
            'village'           => 'sometimes|nullable|string|max:100',
            'credit_limit_fcfa' => 'sometimes|integer|min:0',
            'is_active'         => 'sometimes|boolean',
        ];
    }
}
