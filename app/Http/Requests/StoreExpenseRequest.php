<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'boat_id' => 'nullable|exists:boats,id',
            'landing_id' => 'nullable|string',
            'date' => 'required|date',
            'type' => ['required', Rule::in(Expense::types())],
            'vendor_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }
}
