<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentPostingService
{
    protected ExpenseSettlementService $expenseService;

    protected LandingSummaryService $landingService;

    public function __construct(
        ExpenseSettlementService $expenseService,
        LandingSummaryService $landingService
    ) {
        $this->expenseService = $expenseService;
        $this->landingService = $landingService;
    }

    public function postPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'user_id' => $data['user_id'] ?? auth()->id(),
                'boat_id' => $data['boat_id'] ?? null,
                'landing_id' => $data['landing_id'] ?? null,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'mode' => $data['mode'],
                'source' => $data['source'],
                'payment_for' => $data['payment_for'],
                'loan_reference' => $data['loan_reference'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $allocations = $data['allocations'] ?? [];
            foreach ($allocations as $allocation) {
                $this->createAllocation($payment, $allocation);
            }

            Transaction::create([
                'user_id' => $data['user_id'] ?? auth()->id(),
                'type' => 'Payment',
                'mode' => $data['mode'],
                'source' => $data['source'],
                'amount' => $data['amount'],
                'boat_id' => $data['boat_id'],
                'landing_id' => $data['landing_id'] ?? null,
                'cash_source_receipt_id' => $data['cash_source_receipt_id'] ?? null,
                'transactionable_type' => Payment::class,
                'transactionable_id' => $payment->id,
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['landing_id']) && $data['landing_id']) {
                $landing = Landing::find($data['landing_id']);
                if ($landing) {
                    $this->landingService->updateLandingStatus($landing);
                }
            }

            return $payment;
        });
    }

    protected function createAllocation(Payment $payment, array $data): PaymentAllocation
    {
        $allocation = PaymentAllocation::create([
            'payment_id' => $payment->id,
            'allocatable_type' => $data['type'] === 'expense' ? Expense::class : Landing::class,
            'allocatable_id' => $data['id'],
            'amount' => $data['amount'],
        ]);

        if ($data['type'] === 'expense') {
            $this->expenseService->allocatePayment($allocation);
        }

        return $allocation;
    }

    public function reversePayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            foreach ($payment->allocations as $allocation) {
                if ($allocation->allocatable_type === Expense::class) {
                    $this->expenseService->reverseAllocation($allocation);
                }
            }

            $payment->allocations()->delete();

            $payment->transaction?->delete();

            if ($payment->landing_id) {
                $landing = Landing::find($payment->landing_id);
                if ($landing) {
                    $this->landingService->updateLandingStatus($landing);
                }
            }
        });
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $this->reversePayment($payment);

            $payment->update([
                'boat_id' => $data['boat_id'] ?? null,
                'landing_id' => $data['landing_id'] ?? null,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'mode' => $data['mode'],
                'source' => $data['source'],
                'payment_for' => $data['payment_for'],
                'loan_reference' => $data['loan_reference'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $allocations = $data['allocations'] ?? [];
            foreach ($allocations as $allocation) {
                $this->createAllocation($payment, $allocation);
            }

            $payment->transaction?->update([
                'type' => 'Payment',
                'mode' => $data['mode'],
                'source' => $data['source'],
                'amount' => $data['amount'],
                'boat_id' => $data['boat_id'],
                'landing_id' => $data['landing_id'] ?? null,
                'cash_source_receipt_id' => $data['cash_source_receipt_id'] ?? null,
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
            ]);

            if ($payment->landing_id) {
                $landing = Landing::find($payment->landing_id);
                if ($landing) {
                    $this->landingService->updateLandingStatus($landing);
                }
            }

            return $payment;
        });
    }
}
