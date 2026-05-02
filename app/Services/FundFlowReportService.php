<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Transaction;

class FundFlowReportService
{
    public function getFundFlow($startDate = null, $endDate = null)
    {
        $userId = auth()->id();

        // Receipts (Inflow)
        $receiptsQuery = Receipt::with('buyer')
            ->where('user_id', $userId);
        if ($startDate && $endDate) {
            $receiptsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $receipts = $receiptsQuery->orderBy('date', 'desc')->get();

        // Payments (Outflow)
        $paymentsQuery = Payment::with('boat')
            ->where('user_id', $userId);
        if ($startDate && $endDate) {
            $paymentsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $payments = $paymentsQuery->orderBy('date', 'desc')->get();

        // Expenses (Outflow)
        $expensesQuery = Expense::with('boat')
            ->where('user_id', $userId);
        if ($startDate && $endDate) {
            $expensesQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $expenses = $expensesQuery->orderBy('date', 'desc')->get();

        // Loans (Inflow)
        $loansQuery = Loan::where('user_id', $userId);
        if ($startDate && $endDate) {
            $loansQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $loans = $loansQuery->orderBy('date', 'desc')->get();

        // Withdrawals (Outflow) - from transactions table
        // Include transactions where type='Payment', mode='Cash', source='Cash'
        // and (notes contains 'withdrawal' OR transactionable_type is null meaning manual withdrawal)
        $withdrawalsQuery = Transaction::where('user_id', $userId)
            ->where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('source', 'Cash')
            ->where(function ($query) {
                $query->where('notes', 'like', '%withdrawal%')
                      ->orWhere('notes', 'like', '%Withdrawal%')
                      ->orWhereNull('transactionable_id');
            });
        if ($startDate && $endDate) {
            $withdrawalsQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $withdrawals = $withdrawalsQuery->orderBy('date', 'desc')->get();

        // Calculate totals
        $totalReceipts = $receipts->sum('amount');
        $totalPayments = $payments->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $totalLoans = $loans->sum('amount');
        $totalWithdrawals = $withdrawals->sum('amount');

        $totalInflows = $totalReceipts + $totalLoans;
        $totalOutflows = $totalPayments + $totalExpenses + $totalWithdrawals;
        $netChange = $totalInflows - $totalOutflows;

        return [
            'summary' => [
                'total_inflows' => $totalInflows,
                'total_outflows' => $totalOutflows,
                'net_change' => $netChange,
            ],
            'categories' => [
                'receipts' => [
                    'label' => 'Receipts (Inflow)',
                    'transactions' => $receipts->map(function ($r) {
                        return [
                            'date' => $r->date,
                            'amount' => $r->amount,
                            'mode' => $r->mode,
                            'buyer_name' => $r->buyer->name ?? '-',
                            'notes' => $r->notes,
                        ];
                    })->toArray(),
                    'total' => $totalReceipts,
                ],
                'loans' => [
                    'label' => 'Loans (Inflow)',
                    'transactions' => $loans->map(function ($l) {
                        return [
                            'date' => $l->date,
                            'amount' => $l->amount,
                            'source' => $l->source,
                            'notes' => $l->notes,
                        ];
                    })->toArray(),
                    'total' => $totalLoans,
                ],
                'payments' => [
                    'label' => 'Payments (Outflow)',
                    'transactions' => $payments->map(function ($p) {
                        return [
                            'date' => $p->date,
                            'amount' => $p->amount,
                            'mode' => $p->mode,
                            'payment_for' => $p->payment_for,
                            'boat_name' => $p->boat->name ?? '-',
                            'notes' => $p->notes,
                        ];
                    })->toArray(),
                    'total' => $totalPayments,
                ],
                'expenses' => [
                    'label' => 'Expenses (Outflow)',
                    'transactions' => $expenses->map(function ($e) {
                        return [
                            'date' => $e->date,
                            'amount' => $e->amount,
                            'mode' => $e->mode,
                            'type' => $e->type,
                            'vendor_name' => $e->vendor_name,
                            'boat_name' => $e->boat->name ?? '-',
                            'notes' => $e->notes,
                        ];
                    })->toArray(),
                    'total' => $totalExpenses,
                ],
                'withdrawals' => [
                    'label' => 'Withdrawals (Outflow)',
                    'transactions' => $withdrawals->map(function ($w) {
                        return [
                            'date' => $w->date,
                            'amount' => $w->amount,
                            'notes' => $w->notes,
                        ];
                    })->toArray(),
                    'total' => $totalWithdrawals,
                ],
            ],
        ];
    }
}
