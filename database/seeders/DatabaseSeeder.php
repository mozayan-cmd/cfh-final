<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Receipt;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Expense Types
        $types = ['Diesel', 'Ice', 'Food', 'Wages', 'Maintenance', 'Other'];
        foreach ($types as $type) {
            ExpenseType::firstOrCreate(['name' => $type]);
        }

        // Create Payment Types
        $paymentTypes = ['Owner', 'Expense', 'Loan', 'Basheer', 'Personal', 'Mixed', 'Other'];
        foreach ($paymentTypes as $type) {
            PaymentType::firstOrCreate(['name' => $type]);
        }

        // Create Boats
        $amala = Boat::firstOrCreate(['name' => 'Amala']);
        $ramzan = Boat::firstOrCreate(['name' => 'Ramzan']);

        // Create Buyers
        $buyerNames = ['Ali', 'Najeeb', 'Hisham', 'Shifas', 'Shameer', 'Ksh', 'Pys', 'Ajish', 'Rc'];
        foreach ($buyerNames as $name) {
            Buyer::firstOrCreate(['name' => $name]);
        }

        // Create Amala Landing (2026-03-14) - Settled
        $amalaLanding = Landing::firstOrCreate(
            ['boat_id' => $amala->id, 'date' => '2026-03-14'],
            ['gross_value' => 2104200.00, 'status' => 'Settled']
        );

        // Create Ramzan Landing (2026-03-31) - Open
        $ramzanLanding = Landing::firstOrCreate(
            ['boat_id' => $ramzan->id, 'date' => '2026-03-31'],
            ['gross_value' => 970600.00, 'status' => 'Open']
        );

        // Create Amala Invoices
        $this->createAmalaInvoices($amalaLanding, $amala);

        // Create Ramzan Invoices
        $this->createRamzanInvoices($ramzanLanding, $ramzan);

        // Create Expenses
        $this->createExpenses($amalaLanding, $ramzanLanding);

        // Create Receipts
        $this->createReceipts($amalaLanding, $ramzanLanding);

        // Create Payments
        $this->createPayments($amala, $ramzan, $amalaLanding, $ramzanLanding);
    }

    private function createAmalaInvoices(Landing $landing, Boat $boat)
    {
        $invoices = [
            ['buyer' => 'Ali', 'original' => 45000, 'received' => 45000, 'pending' => 0],
            ['buyer' => 'Najeeb', 'original' => 90000, 'received' => 90000, 'pending' => 0],
            ['buyer' => 'Hisham', 'original' => 75000, 'received' => 75000, 'pending' => 0],
            ['buyer' => 'Shifas', 'original' => 50000, 'received' => 50000, 'pending' => 0],
            ['buyer' => 'Shameer', 'original' => 135000, 'received' => 135000, 'pending' => 0],
            ['buyer' => 'Ksh', 'original' => 180000, 'received' => 80000, 'pending' => 100000],
            ['buyer' => 'Pys', 'original' => 125000, 'received' => 0, 'pending' => 125000],
            ['buyer' => 'Ajish', 'original' => 157500, 'received' => 0, 'pending' => 157500],
            ['buyer' => 'Rc', 'original' => 450000, 'received' => 0, 'pending' => 450000],
            ['buyer' => 'Ali', 'original' => 40000, 'received' => 40000, 'pending' => 0],
            ['buyer' => 'Najeeb', 'original' => 35000, 'received' => 35000, 'pending' => 0],
            ['buyer' => 'Hisham', 'original' => 45000, 'received' => 45000, 'pending' => 0],
            ['buyer' => 'Shifas', 'original' => 50000, 'received' => 50000, 'pending' => 0],
            ['buyer' => 'Ali', 'original' => 50000, 'received' => 50000, 'pending' => 0],
            ['buyer' => 'Najeeb', 'original' => 50000, 'received' => 50000, 'pending' => 0],
            ['buyer' => 'Hisham', 'original' => 90000, 'received' => 90000, 'pending' => 0],
            ['buyer' => 'Shifas', 'original' => 45000, 'received' => 45000, 'pending' => 0],
            ['buyer' => 'Shameer', 'original' => 90000, 'received' => 90000, 'pending' => 0],
            ['buyer' => 'Ksh', 'original' => 135000, 'received' => 0, 'pending' => 135000],
            ['buyer' => 'Pys', 'original' => 112500, 'received' => 0, 'pending' => 112500],
            ['buyer' => 'Ajish', 'original' => 157500, 'received' => 157500, 'pending' => 0],
            ['buyer' => 'Rc', 'original' => 450000, 'received' => 0, 'pending' => 450000],
        ];

        foreach ($invoices as $inv) {
            $buyer = Buyer::where('name', $inv['buyer'])->first();
            Invoice::firstOrCreate(
                ['buyer_id' => $buyer->id, 'landing_id' => $landing->id],
                [
                    'boat_id' => $boat->id,
                    'invoice_date' => $landing->date,
                    'original_amount' => $inv['original'],
                    'received_amount' => $inv['received'],
                    'pending_amount' => $inv['pending'],
                    'status' => $inv['pending'] == 0 ? 'Paid' : ($inv['received'] > 0 ? 'Partial' : 'Pending'),
                ]
            );
        }
    }

    private function createRamzanInvoices(Landing $landing, Boat $boat)
    {
        $invoices = [
            ['buyer' => 'Ali', 'original' => 225000, 'received' => 0, 'pending' => 225000],
            ['buyer' => 'Najeeb', 'original' => 180000, 'received' => 0, 'pending' => 180000],
            ['buyer' => 'Hisham', 'original' => 157500, 'received' => 0, 'pending' => 157500],
            ['buyer' => 'Shifas', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Shameer', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Ksh', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Pys', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Ajish', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Rc', 'original' => 50000, 'received' => 0, 'pending' => 50000],
            ['buyer' => 'Ali', 'original' => 225000, 'received' => 0, 'pending' => 225000],
        ];

        foreach ($invoices as $inv) {
            $buyer = Buyer::where('name', $inv['buyer'])->first();
            Invoice::firstOrCreate(
                ['buyer_id' => $buyer->id, 'landing_id' => $landing->id],
                [
                    'boat_id' => $boat->id,
                    'invoice_date' => $landing->date,
                    'original_amount' => $inv['original'],
                    'received_amount' => $inv['received'],
                    'pending_amount' => $inv['pending'],
                    'status' => 'Pending',
                ]
            );
        }
    }

    private function createExpenses(Landing $amalaLanding, Landing $ramzanLanding)
    {
        // Amala Expenses
        $amalaExpenses = [
            ['type' => 'Diesel', 'vendor' => 'Bejoy', 'amount' => 591000, 'paid' => 591000, 'pending' => 0],
            ['type' => 'Petty Cash Advance', 'vendor' => 'Basheer', 'amount' => 210000, 'paid' => 210000, 'pending' => 0],
            ['type' => 'Jonettan', 'vendor' => 'Jonettan', 'amount' => 1400, 'paid' => 1400, 'pending' => 0],
            ['type' => 'Rishad', 'vendor' => 'Rishad', 'amount' => 9900, 'paid' => 9900, 'pending' => 0],
            ['type' => 'CMM A', 'vendor' => 'Basheer', 'amount' => 49450, 'paid' => 49450, 'pending' => 0],
            ['type' => 'CMM R', 'vendor' => 'Rishad', 'amount' => 49450, 'paid' => 49450, 'pending' => 0],
            ['type' => 'Other', 'vendor' => 'OTHER', 'amount' => 200, 'paid' => 200, 'pending' => 0],
        ];

        foreach ($amalaExpenses as $exp) {
            Expense::firstOrCreate(
                ['landing_id' => $amalaLanding->id, 'type' => $exp['type'], 'vendor_name' => $exp['vendor']],
                [
                    'boat_id' => $amalaLanding->boat_id,
                    'amount' => $exp['amount'],
                    'paid_amount' => $exp['paid'],
                    'pending_amount' => $exp['pending'],
                    'payment_status' => $exp['pending'] > 0 ? 'Partial' : 'Paid',
                    'date' => $amalaLanding->date,
                ]
            );
        }

        // Ramzan Expenses
        $ramzanExpenses = [
            ['type' => 'Diesel', 'vendor' => 'Fuel Station', 'amount' => 150000, 'paid' => 0, 'pending' => 150000],
            ['type' => 'Ice', 'vendor' => 'Ice Factory', 'amount' => 50000, 'paid' => 0, 'pending' => 50000],
        ];

        foreach ($ramzanExpenses as $exp) {
            Expense::firstOrCreate(
                ['landing_id' => $ramzanLanding->id, 'type' => $exp['type'], 'vendor_name' => $exp['vendor']],
                [
                    'boat_id' => $ramzanLanding->boat_id,
                    'amount' => $exp['amount'],
                    'paid_amount' => $exp['paid'],
                    'pending_amount' => $exp['pending'],
                    'payment_status' => 'Pending',
                    'date' => $ramzanLanding->date,
                ]
            );
        }
    }

    private function createReceipts(Landing $amalaLanding, Landing $ramzanLanding)
    {
        $receipts = [
            ['buyer' => 'Ali', 'amount' => 49600, 'mode' => 'Cash', 'date' => '2026-03-30', 'landing' => $amalaLanding],
            ['buyer' => 'Najeeb', 'amount' => 25600, 'mode' => 'GP', 'date' => '2026-03-30', 'landing' => $amalaLanding],
            ['buyer' => 'Hisham', 'amount' => 28400, 'mode' => 'GP', 'date' => '2026-03-30', 'landing' => $amalaLanding],
            ['buyer' => 'Shifas', 'amount' => 200, 'mode' => 'Cash', 'date' => '2026-03-30', 'landing' => $amalaLanding],
            ['buyer' => 'Shameer', 'amount' => 19000, 'mode' => 'GP', 'date' => '2026-03-28', 'landing' => $amalaLanding],
            ['buyer' => 'Ksh', 'amount' => 68800, 'mode' => 'GP', 'date' => '2026-03-28', 'landing' => $amalaLanding],
            ['buyer' => 'Pys', 'amount' => 44700, 'mode' => 'GP', 'date' => '2026-03-28', 'landing' => $amalaLanding],
            ['buyer' => 'Ajish', 'amount' => 55000, 'mode' => 'GP', 'date' => '2026-03-28', 'landing' => $amalaLanding],
            ['buyer' => 'Rc', 'amount' => 388800, 'mode' => 'GP', 'date' => '2026-03-28', 'landing' => $amalaLanding],
            ['buyer' => 'Ali', 'amount' => 100000, 'mode' => 'Cash', 'date' => '2026-03-31', 'landing' => $ramzanLanding],
        ];

        foreach ($receipts as $rec) {
            $buyer = Buyer::where('name', $rec['buyer'])->first();
            $invoice = Invoice::where('buyer_id', $buyer->id)->where('landing_id', $rec['landing']->id)->first();
            if ($invoice) {
                Receipt::firstOrCreate(
                    ['buyer_id' => $buyer->id, 'invoice_id' => $invoice->id, 'amount' => $rec['amount']],
                    [
                        'boat_id' => $rec['landing']->boat_id,
                        'landing_id' => $rec['landing']->id,
                        'date' => $rec['date'],
                        'mode' => $rec['mode'],
                    ]
                );
            }
        }
    }

    private function createPayments(Boat $amala, Boat $ramzan, Landing $amalaLanding, Landing $ramzanLanding)
    {
        $payments = [
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 1000, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 425000, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 5000, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 150, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 20000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-15'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 8500, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Expense', 'date' => '2026-03-19'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 100000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-21'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 200000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-21'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 600, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Expense', 'date' => '2026-03-23'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 100000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 100000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 100000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 50000, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Expense', 'date' => '2026-03-30'],
            ['boat_id' => $amala->id, 'landing_id' => $amalaLanding->id, 'amount' => 20000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Expense', 'date' => '2026-03-31'],
            ['boat_id' => $ramzan->id, 'landing_id' => $ramzanLanding->id, 'amount' => 50000, 'mode' => 'Cash', 'source' => 'Cash', 'payment_for' => 'Owner', 'date' => '2026-03-30'],
            ['boat_id' => null, 'landing_id' => null, 'amount' => 11500, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Loan', 'date' => '2026-03-17', 'loan_reference' => 'aapa loan'],
            ['boat_id' => null, 'landing_id' => null, 'amount' => 1000, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Expense', 'date' => '2026-03-17'],
            ['boat_id' => null, 'landing_id' => null, 'amount' => 2124, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Expense', 'date' => '2026-03-25'],
            ['boat_id' => null, 'landing_id' => null, 'amount' => 553, 'mode' => 'Bank', 'source' => 'Bank', 'payment_for' => 'Expense', 'date' => '2026-03-14'],
        ];

        foreach ($payments as $pay) {
            Payment::firstOrCreate(
                ['boat_id' => $pay['boat_id'], 'landing_id' => $pay['landing_id'], 'amount' => $pay['amount'], 'date' => $pay['date']],
                [
                    'mode' => $pay['mode'],
                    'source' => $pay['source'],
                    'payment_for' => $pay['payment_for'],
                    'loan_reference' => $pay['loan_reference'] ?? null,
                ]
            );
        }
    }
}
