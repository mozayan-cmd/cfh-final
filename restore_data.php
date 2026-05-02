<?php

use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Disable foreign key checks for truncate
DB::statement('PRAGMA foreign_keys = OFF');

// Delete all records in correct order
Payment::truncate();
Receipt::truncate();
Invoice::truncate();
Expense::truncate();
Transaction::truncate();
Landing::truncate();
Buyer::truncate();
Boat::truncate();

// Re-enable foreign key checks
DB::statement('PRAGMA foreign_keys = ON');

echo "All data cleared\n";

// Create boats
$amala = Boat::create(['name' => 'Amala']);
$ramzan = Boat::create(['name' => 'Ramzan']);
echo "Boats created\n";

// Create buyers
$buyerNames = ['Ali', 'Najeeb', 'Hisham', 'Shifas', 'Shameer', 'Ksh', 'Pys', 'Ajish', 'Rc'];
$buyers = [];
foreach ($buyerNames as $name) {
    $buyers[$name] = Buyer::create(['name' => $name]);
}
echo "Buyers created\n";

// Create landings
$amalaLanding = Landing::create([
    'boat_id' => $amala->id,
    'date' => '2026-03-14',
    'gross_value' => 2104200.00,
    'status' => 'Settled',
]);

$ramzanLanding = Landing::create([
    'boat_id' => $ramzan->id,
    'date' => '2026-03-31',
    'gross_value' => 970600.00,
    'status' => 'Open',
]);
echo "Landings created\n";

// Create Amala Invoices
$amalaInvoices = [
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

foreach ($amalaInvoices as $inv) {
    Invoice::create([
        'buyer_id' => $buyers[$inv['buyer']]->id,
        'boat_id' => $amala->id,
        'landing_id' => $amalaLanding->id,
        'invoice_date' => '2026-03-14',
        'original_amount' => $inv['original'],
        'received_amount' => $inv['received'],
        'pending_amount' => $inv['pending'],
        'status' => $inv['pending'] == 0 ? 'Paid' : 'Pending',
    ]);
}
echo "Amala invoices created\n";

// Create Ramzan Invoices
$ramzanInvoices = [
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

foreach ($ramzanInvoices as $inv) {
    Invoice::create([
        'buyer_id' => $buyers[$inv['buyer']]->id,
        'boat_id' => $ramzan->id,
        'landing_id' => $ramzanLanding->id,
        'invoice_date' => '2026-03-31',
        'original_amount' => $inv['original'],
        'received_amount' => $inv['received'],
        'pending_amount' => $inv['pending'],
        'status' => 'Pending',
    ]);
}
echo "Ramzan invoices created\n";

// Create Amala Expenses
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
    Expense::create([
        'boat_id' => $amala->id,
        'landing_id' => $amalaLanding->id,
        'date' => '2026-03-14',
        'type' => $exp['type'],
        'vendor_name' => $exp['vendor'],
        'amount' => $exp['amount'],
        'paid_amount' => $exp['paid'],
        'pending_amount' => $exp['pending'],
        'payment_status' => $exp['pending'] > 0 ? 'Partial' : 'Paid',
    ]);
}
echo "Amala expenses created\n";

// Create Ramzan Expenses (pending)
$ramzanExpenses = [
    ['type' => 'Diesel', 'vendor' => 'Fuel Station', 'amount' => 150000, 'paid' => 0, 'pending' => 150000],
    ['type' => 'Ice', 'vendor' => 'Ice Factory', 'amount' => 50000, 'paid' => 0, 'pending' => 50000],
];

foreach ($ramzanExpenses as $exp) {
    Expense::create([
        'boat_id' => $ramzan->id,
        'landing_id' => $ramzanLanding->id,
        'date' => '2026-03-31',
        'type' => $exp['type'],
        'vendor_name' => $exp['vendor'],
        'amount' => $exp['amount'],
        'paid_amount' => $exp['paid'],
        'pending_amount' => $exp['pending'],
        'payment_status' => 'Pending',
    ]);
}
echo "Ramzan expenses created\n";

// Create Amala Receipts
$amalaReceipts = [
    ['buyer' => 'Ali', 'amount' => 49600, 'mode' => 'Cash'],
    ['buyer' => 'Najeeb', 'amount' => 25600, 'mode' => 'GP'],
    ['buyer' => 'Hisham', 'amount' => 28400, 'mode' => 'GP'],
    ['buyer' => 'Shifas', 'amount' => 200, 'mode' => 'Cash'],
    ['buyer' => 'Shameer', 'amount' => 19000, 'mode' => 'GP'],
    ['buyer' => 'Ksh', 'amount' => 68800, 'mode' => 'GP'],
    ['buyer' => 'Pys', 'amount' => 44700, 'mode' => 'GP'],
    ['buyer' => 'Ajish', 'amount' => 55000, 'mode' => 'GP'],
    ['buyer' => 'Rc', 'amount' => 388800, 'mode' => 'GP'],
];

foreach ($amalaReceipts as $rec) {
    $buyer = $buyers[$rec['buyer']];
    $invoice = Invoice::where('buyer_id', $buyer->id)->where('landing_id', $amalaLanding->id)->first();
    if ($invoice) {
        Receipt::create([
            'buyer_id' => $buyer->id,
            'invoice_id' => $invoice->id,
            'boat_id' => $amala->id,
            'landing_id' => $amalaLanding->id,
            'date' => '2026-03-30',
            'mode' => $rec['mode'],
            'amount' => $rec['amount'],
        ]);
    }
}
echo "Amala receipts created\n";

// Create Ramzan Receipt
$ramzanBuyer = $buyers['Ali'];
$ramzanInvoice = Invoice::where('buyer_id', $ramzanBuyer->id)->where('landing_id', $ramzanLanding->id)->first();
if ($ramzanInvoice) {
    Receipt::create([
        'buyer_id' => $ramzanBuyer->id,
        'invoice_id' => $ramzanInvoice->id,
        'boat_id' => $ramzan->id,
        'landing_id' => $ramzanLanding->id,
        'date' => '2026-03-31',
        'mode' => 'Cash',
        'amount' => 100000,
    ]);
}
echo "Ramzan receipts created\n";

// Create Amala Payments (settled)
$amalaPayments = [
    ['amount' => 1000, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
    ['amount' => 425000, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
    ['amount' => 5000, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
    ['amount' => 150, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
    ['amount' => 20000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-15'],
    ['amount' => 8500, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Expense', 'date' => '2026-03-19'],
    ['amount' => 100000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-21'],
    ['amount' => 200000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-21'],
    ['amount' => 600, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Expense', 'date' => '2026-03-23'],
    ['amount' => 100000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
    ['amount' => 100000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
    ['amount' => 100000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-25'],
    ['amount' => 50000, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Expense', 'date' => '2026-03-30'],
    ['amount' => 20000, 'mode' => 'Bank', 'source' => 'Other', 'payment_for' => 'Expense', 'date' => '2026-03-31'],
    ['amount' => 62550, 'mode' => 'Cash', 'source' => 'Other', 'payment_for' => 'Owner', 'date' => '2026-03-14'],
];

foreach ($amalaPayments as $pay) {
    Payment::create([
        'boat_id' => $amala->id,
        'landing_id' => $amalaLanding->id,
        'date' => $pay['date'],
        'amount' => $pay['amount'],
        'mode' => $pay['mode'],
        'source' => $pay['source'],
        'payment_for' => $pay['payment_for'],
    ]);
}
echo "Amala payments created\n";

// Create Ramzan Payment (partial - owner paid 50000)
Payment::create([
    'boat_id' => $ramzan->id,
    'landing_id' => $ramzanLanding->id,
    'date' => '2026-03-30',
    'amount' => 50000,
    'mode' => 'Cash',
    'source' => 'Other',
    'payment_for' => 'Owner',
]);
echo "Ramzan payment created\n";

echo "\nData restored successfully!\n";
echo 'Amala: Gross '.number_format($amalaLanding->gross_value).", Settled\n";
echo 'Ramzan: Gross '.number_format($ramzanLanding->gross_value).", Pending 715,150\n";
