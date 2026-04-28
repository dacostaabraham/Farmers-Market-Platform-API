<?php

namespace App\Http\Requests\Repayment;

use Illuminate\Foundation\Http\FormRequest;

class StoreRepaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'farmer_id'    => 'required|exists:farmers,id',
            'commodity_kg' => 'required|numeric|min:0.001',
            'notes'        => 'nullable|string|max:500',
            'preview'      => 'nullable|boolean',
        ];
    }
}
