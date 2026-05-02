<?php

use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $payments = Payment::whereIn('source', ['Basheer', 'Personal', 'Others'])->get();

        foreach ($payments as $payment) {
            Loan::create([
                'source' => $payment->source,
                'amount' => $payment->amount,
                'date' => $payment->date,
                'notes' => 'Backfilled from payment #'.$payment->id,
            ]);
        }
    }

    public function down(): void
    {
        Loan::where('notes', 'like', 'Backfilled from payment #%')->delete();
    }
};
