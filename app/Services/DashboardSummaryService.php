<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardSummaryService
{
    public function getSummary(): array
    {
        return [
            'cash_in_hand' => $this->getCashInHand(),
            'cash_at_bank' => $this->getCashAtBank(),
            'buyer_pending' => $this->getBuyerPending(),
            'boat_owner_pending' => $this->getBoatOwnerPending(),
            'expense_pending' => $this->getExpensePending(),
            'personal_fund_used' => $this->getPersonalFundUsed(),
            'loan_payments' => $this->getLoanPayments(),
            'basheer_pending' => $this->getBasheerPending(),
            'overdue_landings' => $this->getOverdueLandings(),
            'outstanding_loans' => $this->getOutstandingLoans(),
            'unlinked_expenses' => $this->getUnlinkedExpenses(),
        ];
    }

    public function getUnlinkedExpenses(): array
    {
        $expenses = Expense::whereNull('landing_id')
            ->where('user_id', auth()->id())
            ->with('boat')
            ->orderBy('date', 'desc')
            ->get();

        return [
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
            'by_boat' => $expenses->groupBy('boat_id')->map(function ($group) {
                return [
                    'boat_name' => $group->first()->boat->name ?? 'Unknown',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values(),
        ];
    }

    public function getOutstandingLoans(): array
    {
        $userId = auth()->id();
        $loans = Loan::whereNull('repaid_at')->where('user_id', $userId)->get();

        $basheer = $loans->where('source', 'Basheer')->sum('amount') - $loans->where('source', 'Basheer')->sum('repaid_amount');
        $personal = $loans->where('source', 'Personal')->sum('amount') - $loans->where('source', 'Personal')->sum('repaid_amount');
        $others = $loans->where('source', 'Others')->sum('amount') - $loans->where('source', 'Others')->sum('repaid_amount');

        return [
            'total' => $basheer + $personal + $others,
            'Basheer' => $basheer,
            'Personal' => $personal,
            'Others' => $others,
        ];
    }

    public function getCashInHand(): float
    {
        $userId = auth()->id();
        $cashReceipts = Receipt::where('mode', 'Cash')->where('user_id', $userId)->sum('amount');
        $cashPayments = Payment::where('mode', 'Cash')->where('user_id', $userId)->sum('amount');
        $cashDepositsToBank = Transaction::where('type', 'Receipt')->where('mode', 'Bank')->where('source', 'Cash')->where('user_id', $userId)->sum('amount');
        $loanReceipts = Transaction::where('type', 'Receipt')->where('mode', 'Cash')->whereIn('source', ['Basheer', 'Personal', 'Others'])->where('user_id', $userId)->sum('amount');
        $cashWithdrawals = Transaction::where('type', 'Payment')->where('mode', 'Cash')->where('source', 'Cash')->where('user_id', $userId)->where(function ($query) { $query->where('notes', 'like', '%withdrawal%')->orWhere('notes', 'like', '%Withdrawal%')->orWhereNull('transactionable_id'); })->sum('amount');

        return max(0, $cashReceipts + $loanReceipts + $cashWithdrawals - $cashPayments - $cashDepositsToBank);
    }

    public function getCashAtBank(): float
    {
        $userId = auth()->id();
        $bankGpReceipts = Receipt::whereIn('mode', ['GP', 'Bank'])->where('user_id', $userId)->sum('amount');
        $cashDepositsToBank = Transaction::where('type', 'Receipt')->whereIn('mode', ['Bank', 'GP'])->whereNotNull('cash_source_receipt_id')->where('user_id', $userId)->sum('amount');
        $lumpsumBankDeposits = Transaction::where('type', 'Receipt')->where('mode', 'Bank')->where('source', 'Cash')->whereNull('cash_source_receipt_id')->where('user_id', $userId)->sum('amount');
        $loanReceipts = Transaction::where('type', 'Receipt')->whereIn('mode', ['Bank', 'GP'])->whereIn('source', ['Basheer', 'Personal', 'Others'])->where('user_id', $userId)->sum('amount');
        $bankGpPayments = Payment::whereIn('mode', ['Bank', 'GP'])->where('user_id', $userId)->sum('amount');
        $cashWithdrawals = Transaction::where('type', 'Payment')->where('mode', 'Cash')->where('source', 'Cash')->where('user_id', $userId)->where(function ($query) { $query->where('notes', 'like', '%withdrawal%')->orWhere('notes', 'like', '%Withdrawal%')->orWhereNull('transactionable_id'); })->sum('amount');

        return max(0, $bankGpReceipts + $cashDepositsToBank + $lumpsumBankDeposits + $loanReceipts - $bankGpPayments - $cashWithdrawals);
    }

    public function getBuyerPending(): float
    {
        return Invoice::where('user_id', auth()->id())->sum('pending_amount');
    }

    public function getBoatOwnerPending(): float
    {
        $userId = auth()->id();
        $pending = 0;
        $landings = Landing::with('payments', 'expenses')->where('user_id', $userId)->get();

        foreach ($landings as $landing) {
            $totalExpenses = $landing->expenses->sum('amount');
            $netPayable = $landing->gross_value - $totalExpenses;
            $ownerPaid = $landing->payments()
                ->where('payment_for', '!=', 'Expense')
                ->sum('amount');
            $pending += max(0, $netPayable - $ownerPaid);
        }

        return $pending;
    }

    public function getExpensePending(): float
    {
        return Expense::where('user_id', auth()->id())->sum('pending_amount');
    }

    public function getPersonalFundUsed(): float
    {
        return Payment::where('payment_for', 'Personal')->where('user_id', auth()->id())->sum('amount');
    }

    public function getLoanPayments(): float
    {
        return Payment::where('payment_for', 'Loan')->where('user_id', auth()->id())->sum('amount');
    }

    public function getBasheerPending(): array
    {
        $outstandingLoans = $this->getOutstandingLoans();

        return [
            'borrowed' => 0,
            'repaid' => 0,
            'pending' => $outstandingLoans['Basheer'],
        ];
    }

public function getOverdueLandings(): array
    {
        $userId = auth()->id();
        $landings = Landing::where('user_id', $userId)
            ->where('status', '!=', 'Settled')
            ->with('expenses')
            ->get();

        $count = $landings->count();
        $pendingExpenses = $landings->sum(function ($landing) {
            return $landing->expenses->sum('pending_amount');
        });

        return [
            'count' => $count,
            'pending_expenses' => $pendingExpenses,
        ];
    }

    public function getRecentLandings(int $limit = 5): array
    {
        $landings = Landing::with('boat')->where('user_id', auth()->id())->orderBy('date', 'desc')->limit($limit)->get();

        return $landings->map(function ($landing) {
            return [
                'id' => $landing->id,
                'date' => $landing->date->format('Y-m-d'),
                'gross_value' => $landing->gross_value,
                'status' => $landing->status,
                'boat' => ['name' => $landing->boat->name ?? 'N/A'],
            ];
        })->toArray();
    }

    public function getRecentReceipts(int $limit = 5): array
    {
        $receipts = Receipt::with(['buyer', 'invoice'])->where('user_id', auth()->id())->orderBy('date', 'desc')->limit($limit)->get();

        return $receipts->map(function ($receipt) {
            return [
                'id' => $receipt->id,
                'date' => $receipt->date->format('Y-m-d'),
                'amount' => $receipt->amount,
                'mode' => $receipt->mode,
                'buyer_name' => $receipt->buyer->name ?? 'N/A',
                'invoice_date' => $receipt->invoice?->invoice_date?->format('Y-m-d') ?? 'N/A',
            ];
        })->toArray();
    }

    public function getRecentPayments(int $limit = 5): array
    {
        $payments = Payment::with(['boat', 'landing'])->where('user_id', auth()->id())->orderBy('date', 'desc')->limit($limit)->get();

        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->date->format('Y-m-d'),
                'amount' => $payment->amount,
                'mode' => $payment->mode,
                'payment_for' => $payment->payment_for,
                'loan_reference' => $payment->loan_reference,
                'boat' => ['name' => $payment->boat->name ?? 'N/A'],
            ];
        })->toArray();
    }

    public function getPendingSettlements(int $limit = 5): array
    {
        $landings = Landing::with(['boat', 'expenses', 'payments'])
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'Settled')
            ->orderBy('date', 'asc')
            ->limit($limit)->get();

        return $landings->map(function ($landing) {
            return [
                'id' => $landing->id,
                'date' => $landing->date->format('Y-m-d'),
                'gross_value' => $landing->gross_value,
                'total_expenses' => $landing->expenses->sum('amount'),
                'total_owner_paid' => $landing->payments->where('payment_for', '!=', 'Expense')->sum('amount'),
                'boat' => ['name' => $landing->boat->name ?? 'N/A'],
                'status' => $landing->status,
            ];
        })->toArray();
    }
}
