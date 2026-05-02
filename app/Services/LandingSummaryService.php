<?php

namespace App\Services;

use App\Models\Landing;

class LandingSummaryService
{
    public function getSummary(Landing $landing): array
    {
        $landing->load(['expenses', 'invoices', 'payments', 'receipts']);

        $grossValue = (float) $landing->gross_value;
        $totalExpenses = $landing->expenses->sum('amount');
        $totalExpensesPaid = $landing->expenses->sum('paid_amount');
        $netOwnerPayable = $grossValue - $totalExpenses;

        $ownerPayments = $landing->payments()
            ->where('payment_for', '!=', 'Expense')
            ->sum('amount');

        $totalOwnerPaid = (float) $ownerPayments;
        $ownerPending = $netOwnerPayable - $totalOwnerPaid;

        $totalInvoices = $landing->invoices->sum('original_amount');
        $totalReceived = $landing->invoices->sum('received_amount');
        $totalBuyerPending = $landing->invoices->sum('pending_amount');

        return [
            'gross_value' => $grossValue,
            'total_expenses' => $totalExpenses,
            'total_expenses_paid' => $totalExpensesPaid,
            'total_expenses_pending' => $totalExpenses - $totalExpensesPaid,
            'net_owner_payable' => $netOwnerPayable,
            'total_owner_paid' => $totalOwnerPaid,
            'owner_pending' => $ownerPending,
            'total_invoices' => $totalInvoices,
            'total_received' => $totalReceived,
            'total_buyer_pending' => $totalBuyerPending,
            'status' => $this->calculateStatus($ownerPending, $netOwnerPayable),
        ];
    }

    public function updateLandingStatus(Landing $landing): void
    {
        $summary = $this->getSummary($landing);
        $landing->update(['status' => $summary['status']]);
    }

    protected function calculateStatus(float $ownerPending, float $netOwnerPayable): string
    {
        if ($ownerPending <= 0 && $netOwnerPayable <= 0) {
            return 'Settled';
        }

        if ($netOwnerPayable <= 0) {
            return 'Overpaid';
        }

        if ($ownerPending <= 0) {
            return 'Settled';
        }

        $totalPaid = $netOwnerPayable - $ownerPending;
        if ($totalPaid > 0) {
            return 'Partial';
        }

        return 'Open';
    }
}
