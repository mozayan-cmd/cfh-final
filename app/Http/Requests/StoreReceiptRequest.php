<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buyer_id' => 'required|exists:buyers,id',
            'invoice_id' => 'required|exists:invoices,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:Cash,GP,Bank',
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $invoice = Invoice::find($this->invoice_id);
            if ($invoice && $this->amount > $invoice->pending_amount) {
                $validator->errors()->add('amount', 'Receipt amount cannot exceed invoice pending amount of '.$invoice->pending_amount);
            }
        });
    }
}
