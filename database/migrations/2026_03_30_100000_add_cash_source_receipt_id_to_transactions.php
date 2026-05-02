<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('cash_source_receipt_id')
                ->nullable()
                ->after('invoice_id')
                ->constrained('receipts')
                ->onDelete('set null');

            $table->index('cash_source_receipt_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['cash_source_receipt_id']);
            $table->dropColumn('cash_source_receipt_id');
        });
    }
};
