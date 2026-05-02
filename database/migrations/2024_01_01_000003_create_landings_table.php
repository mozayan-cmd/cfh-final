<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boat_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('gross_value', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['Open', 'Partial', 'Settled'])->default('Open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landings');
    }
};
