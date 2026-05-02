<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->foreignId('landing_id')->constrained()->onDelete('cascade');
            $table->date('invoice_date');
            $table->decimal('original_amount', 12, 2);
            $table->decimal('received_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2);
            $table->enum('status', ['Pending', 'Partial', 'Paid'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
