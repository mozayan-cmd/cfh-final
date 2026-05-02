<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class ExpenseSettlementService
{
    public function allocatePayment(PaymentAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $expense = $allocation->allocatable;

            if (! $expense instanceof Expense) {
                return;
            }

            $expense->paid_amount = $expense->paid_amount + $allocation->amount;
            $expense->pending_amount = $expense->amount - $expense->paid_amount;
            $expense->payment_status = $this->calculateStatus($expense->paid_amount, $expense->pending_amount);
            $expense->save();
        });
    }

    public function updateExpenseStatus(Expense $expense): void
    {
        $expense->payment_status = $this->calculateStatus(
            $expense->paid_amount,
            $expense->pending_amount
        );
        $expense->save();
    }

    protected function calculateStatus(float $paid, float $pending): string
    {
        if ($pending <= 0) {
            return 'Paid';
        }
        if ($paid > 0) {
            return 'Partial';
        }

        return 'Pending';
    }

    public function reverseAllocation(PaymentAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $expense = $allocation->allocatable;

            if (! $expense instanceof Expense) {
                return;
            }

            $expense->paid_amount = max(0, $expense->paid_amount - $allocation->amount);
            $expense->pending_amount = $expense->amount - $expense->paid_amount;
            $expense->payment_status = $this->calculateStatus($expense->paid_amount, $expense->pending_amount);
            $expense->save();
        });
    }
}
