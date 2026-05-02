<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_date' => 'required|date',
            'original_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }
}
