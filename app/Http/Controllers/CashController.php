<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\CashSourceTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    protected CashSourceTrackingService $cashService;

    public function __construct(CashSourceTrackingService $cashService)
    {
        $this->cashService = $cashService;
    }

    public function utilization(): View
    {
        $receipts = $this->cashService->getCashReceiptsWithUtilization();
        $cashDeposits = $this->cashService->getCashDepositsToBank();
        $cashPayments = $this->cashService->getCashPayments();

        $loanReceipts = Transaction::with(['transactionable'])
            ->where('type', 'Receipt')
            ->where('mode', 'Cash')
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

        $totalLoanReceipts = $loanReceipts->sum('amount');
        $totalCashWithdrawals = $cashWithdrawals->sum('amount');

        $summary = [
            'total_cash_received' => $receipts->sum('amount'),
            'total_loan_receipts' => $totalLoanReceipts,
            'total_withdrawals' => $totalCashWithdrawals,
            'total_utilized' => $cashPayments->sum('amount'),
            'total_deposited' => $cashDeposits->sum('amount'),
            'total_balance' => $receipts->sum('amount') + $totalLoanReceipts + $totalCashWithdrawals - $cashPayments->sum('amount') - $cashDeposits->sum('amount'),
        ];

        return view('cash.utilization', compact('receipts', 'cashDeposits', 'cashPayments', 'summary', 'loanReceipts', 'cashWithdrawals'));
    }

    public function editTransaction(Transaction $transaction): View
    {
        return view('cash.edit-transaction', compact('transaction'));
    }

    public function updateTransaction(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return redirect()->route('cash.utilization')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroyTransaction(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('cash.utilization')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['buyer', 'transaction']);
        $receipt->utilized_amount = $this->cashService->getUtilizedAmount($receipt->id);
        $receipt->deposited_amount = $this->cashService->getDepositedAmount($receipt->id);
        $receipt->balance = $receipt->amount - $receipt->utilized_amount - $receipt->deposited_amount;

        $linkedPayments = $this->cashService->getLinkedPayments($receipt->id);
        $linkedDeposits = $this->cashService->getLinkedDeposits($receipt->id);

        return view('cash.show', compact('receipt', 'linkedPayments', 'linkedDeposits'));
    }

    public function createDeposit(): View
    {
        $availableReceipts = $this->cashService->getAvailableCashReceipts();
        $totalAvailable = $this->cashService->getTotalAvailableCash();
        $modes = ['Bank', 'GP'];

        return view('cash.deposit', compact('availableReceipts', 'totalAvailable', 'modes'));
    }

    public function storeDeposit(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:Bank,GP',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $amount = $request->amount;
        $totalAvailable = $this->cashService->getTotalAvailableCash();

        if ($request->cash_source_type === 'receipt') {
            $request->validate([
                'cash_source_receipt_id' => 'required|exists:receipts,id',
            ]);

            $receipt = Receipt::find($request->cash_source_receipt_id);

            if (! $receipt) {
                return back()->with('error', 'Receipt not found.')->withInput();
            }

            $utilized = $this->cashService->getUtilizedAmount($receipt->id);
            $deposited = $this->cashService->getDepositedAmount($receipt->id);
            $available = $receipt->amount - $utilized - $deposited;

            if ($amount > $available) {
                return back()->withErrors([
                    'amount' => 'Amount exceeds available balance. Maximum available: ₹'.number_format($available, 2),
                ])->withInput();
            }

            $this->cashService->depositCashToBank([
                'cash_source_receipt_id' => $request->cash_source_receipt_id,
                'amount' => $amount,
                'mode' => $request->mode,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);
        } else {
            if ($amount > $totalAvailable) {
                return back()->withErrors([
                    'amount' => 'Amount exceeds available cash balance. Maximum available: ₹'.number_format($totalAvailable, 2),
                ])->withInput();
            }

            $this->cashService->depositLumpsumCashToBank([
                'amount' => $amount,
                'mode' => $request->mode,
                'date' => $request->date,
                'notes' => $request->notes ?? 'Lumpsum cash deposit to '.$request->mode,
            ]);
        }

        return redirect()->route('cash.utilization')
            ->with('success', "₹{$amount} deposited to {$request->mode} successfully.");
    }

    public function getAvailableReceipts(): JsonResponse
    {
        $receipts = $this->cashService->getAvailableCashReceipts();

        return response()->json([
            'receipts' => $receipts->map(function ($receipt) {
                return [
                    'id' => $receipt->id,
                    'date' => $receipt->date->format('Y-m-d'),
                    'amount' => $receipt->amount,
                    'buyer_name' => $receipt->buyer ? $receipt->buyer->name : 'N/A',
                    'balance' => $receipt->balance,
                ];
            }),
        ]);
    }
}
