<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buyer_id' => 'required|exists:buyers,id',
            'boat_id' => 'required|exists:boats,id',
            'landing_id' => 'required|exists:landings,id',
            'invoice_date' => 'required|date',
            'original_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ];
    }
}
