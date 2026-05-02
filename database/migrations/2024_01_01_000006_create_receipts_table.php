<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->foreignId('landing_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->enum('mode', ['Cash', 'GP', 'Bank']);
            $table->enum('source', ['CashFromSales', 'PersonalFund', 'Bank', 'Other'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
