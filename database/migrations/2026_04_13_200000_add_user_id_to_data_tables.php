<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('transactionable_id')->constrained()->nullOnDelete();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('mode')->constrained()->nullOnDelete();
        });

        Schema::table('boats', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('owner_phone')->constrained()->nullOnDelete();
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('boats', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
