<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Expenses with payment_for=Expense or payment_allocations ===\n\n";

$expenses = App\Models\Expense::with(['paymentAllocations.payment', 'boat', 'user'])
    ->orderBy('id', 'desc')
    ->get();

foreach ($expenses as $e) {
    $allocationSum = $e->paymentAllocations->sum('amount');

    echo "Expense {$e->id} | User: {$e->user_id} | Boat: " . ($e->boat ? $e->boat->name : 'N/A') . "\n";
    echo "  Amount: {$e->amount} | Paid: {$e->paid_amount} | Pending: {$e->pending_amount} | Status: {$e->payment_status}\n";
    echo "  Allocations sum: {$allocationSum}\n";

    if ($e->paymentAllocations->count() > 0) {
        foreach ($e->paymentAllocations as $a) {
            $p = $a->payment;
            if ($p) {
                echo "    -> Payment {$a->payment_id}: ₹{$a->amount}, for: {$p->payment_for}\n";
            }
        }
    }
    echo "\n";
}

echo "\n=== Done ===\n";