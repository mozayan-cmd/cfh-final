<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->enum('source', ['Basheer', 'Personal', 'Others']);
            $table->decimal('amount', 15, 2);
            $table->decimal('repaid_amount', 15, 2)->default(0);
            $table->date('date');
            $table->enum('mode', ['Cash', 'GP', 'Bank'])->default('Cash');
            $table->text('notes')->nullable();
            $table->timestamp('repaid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};