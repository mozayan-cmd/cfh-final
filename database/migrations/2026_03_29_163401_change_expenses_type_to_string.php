<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('type', ['Diesel', 'Ice', 'Ration', 'Petty Cash Advance', 'Unloading', 'Toll', 'Salary', 'Other', 'Tea Boat', 'Tea Staff'])->nullable()->change();
        });
    }
};
