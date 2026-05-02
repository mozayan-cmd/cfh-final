<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Receipt', 'Payment']);
            $table->enum('mode', ['Cash', 'GP', 'Bank']);
            $table->enum('source', ['CashFromSales', 'PersonalFund', 'Bank', 'Other'])->nullable();
            $table->decimal('amount', 12, 2);
            $table->foreignId('boat_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('landing_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('buyer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transactionable_type')->nullable();
            $table->unsignedBigInteger('transactionable_id')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['transactionable_type', 'transactionable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
