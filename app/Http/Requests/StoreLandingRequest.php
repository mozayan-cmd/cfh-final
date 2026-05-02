<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'boat_id' => 'required|exists:boats,id',
            'date' => 'required|date',
            'gross_value' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'expense_ids' => 'nullable|array',
            'expense_ids.*' => 'integer|exists:expenses,id',
        ];
    }
}
