<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $expenseTypes = ['Diesel', 'Ice', 'Ration', 'Petty Cash Advance', 'Unloading', 'Toll', 'Salary', 'Other', 'Tea Boat', 'Tea Staff'];

        Schema::create('expenses_new', function (Blueprint $table) use ($expenseTypes) {
            $table->id();
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->foreignId('landing_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->enum('type', $expenseTypes);
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2);
            $table->enum('payment_status', ['Pending', 'Partial', 'Paid'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO expenses_new (id, boat_id, landing_id, date, type, vendor_name, amount, paid_amount, pending_amount, payment_status, notes, created_at, updated_at) SELECT id, boat_id, landing_id, date, type, vendor_name, amount, paid_amount, pending_amount, payment_status, notes, created_at, updated_at FROM expenses');

        Schema::drop('expenses');
        Schema::rename('expenses_new', 'expenses');
    }

    public function down(): void
    {
        $originalTypes = ['Diesel', 'Ice', 'Ration', 'Petty Cash Advance', 'Unloading', 'Toll', 'Salary', 'Other'];

        Schema::create('expenses_old', function (Blueprint $table) use ($originalTypes) {
            $table->id();
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->foreignId('landing_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->enum('type', $originalTypes);
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2);
            $table->enum('payment_status', ['Pending', 'Partial', 'Paid'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('INSERT INTO expenses_old (id, boat_id, landing_id, date, type, vendor_name, amount, paid_amount, pending_amount, payment_status, notes, created_at, updated_at) SELECT id, boat_id, landing_id, date, type, vendor_name, amount, paid_amount, pending_amount, payment_status, notes, created_at, updated_at FROM expenses');

        Schema::drop('expenses');
        Schema::rename('expenses_old', 'expenses');
    }
};
