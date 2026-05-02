<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CashSourceTrackingService
{
    public function getCashReceiptsWithUtilization(): Collection
    {
        return Receipt::with(['buyer', 'transaction'])
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($receipt) {
                $receipt->utilized_amount = $this->getUtilizedAmount($receipt->id);
                $receipt->deposited_amount = $this->getDepositedAmount($receipt->id);
                $receipt->balance = $receipt->amount - $receipt->utilized_amount - $receipt->deposited_amount;

                return $receipt;
            });
    }

    public function getCashPayments(): Collection
    {
        return Payment::with(['boat', 'landing', 'allocations.allocatable'])
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->whereNotIn('source', ['Basheer', 'Personal', 'Others'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($payment) {
                $linkedReceiptId = $payment->cash_source_receipt_id;
                if ($linkedReceiptId) {
                    $receipt = Receipt::find($linkedReceiptId);
                    $payment->source_receipt_amount = $receipt ? $receipt->amount : null;
                } else {
                    $payment->source_receipt_amount = null;
                }

                $vendorName = '-';
                if ($payment->allocations->count() > 0) {
                    $firstAlloc = $payment->allocations->first();
                    if ($firstAlloc && $firstAlloc->allocatable) {
                        $vendorName = $firstAlloc->allocatable->vendor_name
                            ?? $firstAlloc->allocatable->type
                            ?? ($firstAlloc->allocatable->boat ? $firstAlloc->allocatable->boat->name : '-');
                    }
                }
                $payment->vendor_name = $vendorName;

                return $payment;
            });
    }

    public function getUtilizedAmount(int $receiptId): float
    {
        return Transaction::where('cash_source_receipt_id', $receiptId)
            ->where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->sum('amount');
    }

    public function getDepositedAmount(int $receiptId): float
    {
        return Transaction::where('cash_source_receipt_id', $receiptId)
            ->where('type', 'Receipt')
            ->where('mode', 'Bank')
            ->where('user_id', auth()->id())
            ->sum('amount');
    }

    public function getLinkedPayments(int $receiptId): Collection
    {
        return Transaction::with(['transactionable'])
            ->where('cash_source_receipt_id', $receiptId)
            ->where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getLinkedDeposits(int $receiptId): Collection
    {
        return Transaction::with(['transactionable'])
            ->where('cash_source_receipt_id', $receiptId)
            ->where('type', 'Receipt')
            ->where('mode', 'Bank')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getBankBalance(): float
    {
        $userId = auth()->id();

        $totalBankReceipts = Receipt::whereIn('mode', ['Bank', 'GP'])
            ->where('user_id', $userId)
            ->sum('amount');

        $totalCashDeposits = Transaction::where('type', 'Receipt')
            ->whereIn('mode', ['Bank', 'GP'])
            ->where('source', 'Cash')
            ->where('user_id', $userId)
            ->sum('amount');

        $totalLoanReceipts = Transaction::where('type', 'Receipt')
            ->whereIn('mode', ['Bank', 'GP'])
            ->whereIn('source', ['Basheer', 'Personal', 'Others'])
            ->where('user_id', $userId)
            ->sum('amount');

        $totalBankPayments = Payment::whereIn('mode', ['Bank', 'GP'])
            ->where('user_id', $userId)
            ->sum('amount');

        $totalCashWithdrawals = Transaction::where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('source', 'Cash')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('notes', 'like', '%withdrawal%')
                      ->orWhere('notes', 'like', '%Withdrawal%')
                      ->orWhereNull('transactionable_id');
            })
            ->sum('amount');

        return $totalBankReceipts + $totalCashDeposits + $totalLoanReceipts - $totalBankPayments - $totalCashWithdrawals;
    }

    public function getAvailableCashReceipts(): Collection
    {
        $userId = auth()->id();
        
        $cashReceiptsTotal = Receipt::where('mode', 'Cash')->where('user_id', $userId)->sum('amount');
        $cashPaymentsTotal = Payment::where('mode', 'Cash')->where('user_id', $userId)->sum('amount');
        $cashDepositsToBank = Transaction::where('type', 'Receipt')
            ->where('mode', 'Bank')
            ->where('source', 'Cash')
            ->where('user_id', $userId)
            ->sum('amount');
        $loanReceipts = Transaction::where('type', 'Receipt')
            ->where('mode', 'Cash')
            ->whereIn('source', ['Basheer', 'Personal', 'Others'])
            ->where('user_id', $userId)
            ->sum('amount');
        $cashWithdrawals = Transaction::where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('source', 'Cash')
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('notes', 'like', '%withdrawal%')
                      ->orWhere('notes', 'like', '%Withdrawal%')
                      ->orWhereNull('transactionable_id');
            })
            ->sum('amount');
        
        $availableCash = max(0, $cashReceiptsTotal + $loanReceipts + $cashWithdrawals - $cashPaymentsTotal - $cashDepositsToBank);
        
        $receipts = Receipt::with(['buyer', 'transaction'])
            ->where('mode', 'Cash')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get();
        
        if ($receipts->isEmpty() && $availableCash > 0) {
            $fakeReceipt = new \stdClass();
            $fakeReceipt->id = 0;
            $fakeReceipt->amount = $availableCash;
            $fakeReceipt->mode = 'Cash';
            $fakeReceipt->buyer_id = null;
            $fakeReceipt->date = now();
            $fakeReceipt->utilized_amount = 0;
            $fakeReceipt->deposited_amount = 0;
            $fakeReceipt->balance = $availableCash;
            $fakeReceipt->buyer = null;
            return new \Illuminate\Database\Eloquent\Collection([$fakeReceipt]);
        }
        
        return $receipts->map(function ($r) use ($availableCash) {
            $r->utilized_amount = 0;
            $r->deposited_amount = 0;
            $r->balance = $availableCash;
            return $r;
        });
    }

    public function depositCashToBank(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['cash_source_receipt_id'])) {
                $receipt = Receipt::find($data['cash_source_receipt_id']);
                if ($receipt) {
                    $utilized = $this->getUtilizedAmount($receipt->id);
                    $deposited = $this->getDepositedAmount($receipt->id);
                    $available = $receipt->amount - $utilized - $deposited;

                    if (($data['amount'] ?? 0) > $available) {
                        throw new \InvalidArgumentException('Amount exceeds available balance for this receipt.');
                    }
                }
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'Receipt',
                'mode' => $data['mode'] ?? 'Bank',
                'source' => 'Cash',
                'amount' => $data['amount'],
                'cash_source_receipt_id' => $data['cash_source_receipt_id'] ?? null,
                'date' => $data['date'],
                'notes' => $data['notes'] ?? 'Cash deposited to bank',
            ]);

            return $transaction;
        });
    }

    public function getTotalAvailableCash(): float
    {
        return $this->getAvailableCashReceipts()->sum('balance');
    }

    public function depositLumpsumCashToBank(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $amount = $data['amount'];
            $availableCash = $this->getTotalAvailableCash();
            $cashDeposits = $this->getCashDepositsToBank()->sum('amount');
            $currentAvailableLumpsum = $availableCash - $cashDeposits;

            if ($amount > $currentAvailableLumpsum) {
                throw new \InvalidArgumentException(
                    "Amount (₹{$amount}) exceeds available lumpsum cash (₹".number_format($currentAvailableLumpsum, 2).').'
                );
            }

            $notes = $data['notes'] ?? 'Lumpsum cash deposit to bank';

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'Receipt',
                'mode' => $data['mode'] ?? 'Bank',
                'source' => 'Cash',
                'amount' => $amount,
                'cash_source_receipt_id' => null,
                'date' => $data['date'],
                'notes' => $notes,
            ]);

            return $transaction;
        });
    }

    public function getUnallocatedCashBalance(): float
    {
        $receipts = Transaction::where('type', 'Receipt')
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->sum('amount');

        $payments = Transaction::where('type', 'Payment')
            ->where('mode', 'Cash')
            ->where('user_id', auth()->id())
            ->sum('amount');

        return $receipts - $payments;
    }

    public function getCashDepositsToBank(): Collection
    {
        return Transaction::where('type', 'Receipt')
            ->where('mode', 'Bank')
            ->where('source', 'Cash')
            ->whereNull('cash_source_receipt_id')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();
    }
}
