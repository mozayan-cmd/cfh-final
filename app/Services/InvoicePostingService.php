<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class InvoicePostingService
{
    public function postReceipt(array $data): Receipt
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::findOrFail($data['invoice_id']);

            if (isset($data['buyer_id']) && $data['buyer_id'] != $invoice->buyer_id) {
                throw new \InvalidArgumentException('Receipt buyer does not match invoice buyer.');
            }

            $receipt = Receipt::create([
                'user_id' => auth()->id(),
                'buyer_id' => $invoice->buyer_id,
                'invoice_id' => $data['invoice_id'],
                'boat_id' => $invoice->boat_id,
                'landing_id' => $invoice->landing_id,
                'date' => $data['date'],
                'amount' => $data['amount'],
                'mode' => $data['mode'],
                'source' => $data['source'] ?? 'Cash',
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->received_amount = $invoice->received_amount + $data['amount'];
            $invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
            $invoice->status = $this->calculateStatus($invoice->received_amount, $invoice->pending_amount);
            $invoice->save();

            // Only create Transaction for Cash receipts (cash deposit to bank)
            // Bank/GP receipts are direct bank payments - no cash transaction needed
            if ($data['mode'] === 'Cash') {
                Transaction::create([
                    'user_id' => auth()->id(),
                    'type' => 'Receipt',
                    'mode' => $data['mode'],
                    'source' => $data['source'] ?? 'Cash',
                    'amount' => $data['amount'],
                    'boat_id' => $invoice->boat_id,
                    'landing_id' => $invoice->landing_id,
                    'buyer_id' => $data['buyer_id'],
                    'invoice_id' => $data['invoice_id'],
                    'transactionable_type' => Receipt::class,
                    'transactionable_id' => $receipt->id,
                    'date' => $data['date'],
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            return $receipt;
        });
    }

    public function depositCashToBank(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['cash_source_receipt_id'])) {
                throw new \InvalidArgumentException('cash_source_receipt_id is required for cash deposit.');
            }

            $receipt = Receipt::find($data['cash_source_receipt_id']);
            if (! $receipt) {
                throw new \InvalidArgumentException('Source receipt not found.');
            }

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'type' => 'Receipt',
                'mode' => $data['mode'] ?? 'Bank',
                'source' => 'Cash',
                'amount' => $data['amount'],
                'cash_source_receipt_id' => $data['cash_source_receipt_id'],
                'date' => $data['date'],
                'notes' => $data['notes'] ?? 'Cash deposited to bank from receipt #'.$data['cash_source_receipt_id'],
            ]);

            return $transaction;
        });
    }

    protected function calculateStatus(float $received, float $pending): string
    {
        if ($pending <= 0) {
            return 'Paid';
        }
        if ($received > 0) {
            return 'Partial';
        }

        return 'Pending';
    }
}
