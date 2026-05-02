<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\CashSourceTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankManagementController extends Controller
{
    protected CashSourceTrackingService $cashService;

    public function __construct(CashSourceTrackingService $cashService)
    {
        $this->cashService = $cashService;
    }

    public function index(): View
    {
        $bankReceipts = Receipt::with(['buyer', 'landing.boat'])
            ->whereIn('mode', ['Bank', 'GP'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        $bankPayments = Payment::with(['boat', 'allocations.allocatable'])
            ->whereIn('mode', ['Bank', 'GP'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($payment) {
                $vendorName = '-';
                $type = 'Owner';
                if ($payment->allocations->count() > 0) {
                    $firstAlloc = $payment->allocations->first();
                    if ($firstAlloc && $firstAlloc->allocatable) {
                        $vendorName = $firstAlloc->allocatable->vendor_name
                            ?? $firstAlloc->allocatable->type
                            ?? ($firstAlloc->allocatable->boat ? $firstAlloc->allocatable->boat->name : '-');
                        $type = 'Expense';
                    }
                }
                $payment->vendor_name = $vendorName;
                $payment->type = $type;

                return $payment;
            });

        $cashDepositsToBank = $this->cashService->getCashDepositsToBank();

        $loanReceipts = Transaction::with(['transactionable'])
            ->where('type', 'Receipt')
            ->whereIn('mode', ['Bank', 'GP'])
            ->whereIn('source', ['Basheer', 'Personal', 'Others'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($t) {
                $t->buyer_name = $t->source;
                $t->landing = null;

                return $t;
            });

        $cashWithdrawals = Transaction::where('user_id', auth()->id())
            ->where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('source', 'Cash')
            ->where(function ($query) {
                $query->where('notes', 'like', '%withdrawal%')
                      ->orWhere('notes', 'like', '%Withdrawal%')
                      ->orWhereNull('transactionable_id');
            })
            ->orderBy('date', 'desc')
            ->get();

        $totalBankReceipts = $bankReceipts->sum('amount');
        $totalCashDeposits = $cashDepositsToBank->sum('amount');
        $totalBankPayments = $bankPayments->sum('amount');
        $totalLoanReceipts = $loanReceipts->sum('amount');
        $totalCashWithdrawals = $cashWithdrawals->sum('amount');
        $balance = $totalBankReceipts + $totalCashDeposits + $totalLoanReceipts - $totalBankPayments - $totalCashWithdrawals;

        $summary = [
            'total_bank_receipts' => $totalBankReceipts,
            'total_cash_deposits' => $totalCashDeposits,
            'total_loan_receipts' => $totalLoanReceipts,
            'total_withdrawals' => $totalCashWithdrawals,
            'total_payments' => $totalBankPayments,
            'balance' => $balance,
        ];

        return view('bank-management.index', compact(
            'summary',
            'bankReceipts',
            'bankPayments',
            'cashDepositsToBank',
            'loanReceipts',
            'cashWithdrawals'
        ));
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'Payment',
            'mode' => 'Cash',
            'source' => 'Cash',
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? 'Cash Withdrawal from Bank',
        ]);

        return redirect()->route('bank.index')
            ->with('success', 'Cash withdrawal of Rs. '.number_format($validated['amount'], 2).' recorded.');
    }
}
