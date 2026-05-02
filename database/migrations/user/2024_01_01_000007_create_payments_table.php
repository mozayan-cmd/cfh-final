<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boat_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('landing_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->enum('mode', ['Cash', 'GP', 'Bank']);
            $table->enum('source', ['Cash', 'Personal', 'Bank', 'Other']);
            $table->enum('payment_for', ['Owner', 'Expense', 'Mixed', 'Loan'])->default('Owner');
            $table->string('loan_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};