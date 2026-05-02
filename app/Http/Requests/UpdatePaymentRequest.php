<?php

namespace App\Http\Requests;

use App\Models\PaymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'boat_id' => 'nullable|exists:boats,id',
            'landing_id' => 'nullable|exists:landings,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:Cash,GP,Bank',
            'source' => 'required|in:Cash,Bank',
            'payment_for' => ['required', Rule::in(PaymentType::pluck('name')->toArray())],
            'loan_reference' => 'nullable|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.type' => 'required_with:allocations|in:expense,landing',
            'allocations.*.id' => 'required_with:allocations',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $allocations = $this->input('allocations', []);
            $totalAllocation = collect($allocations)->sum('amount');
            if ($totalAllocation > $this->amount) {
                $validator->errors()->add('allocations', 'Total allocation amount cannot exceed payment amount');
            }
            
            // Require vendor_name when payment_for is "Other"
            if ($this->input('payment_for') === 'Other' && empty($this->input('vendor_name'))) {
                $validator->errors()->add('vendor_name', 'Vendor name is required when payment type is Other');
            }
        });
    }
}
