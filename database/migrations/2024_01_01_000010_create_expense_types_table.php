<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $defaultTypes = ['Diesel', 'Ice', 'Ration', 'Petty Cash Advance', 'Unloading', 'Toll', 'Salary', 'Other'];
        foreach ($defaultTypes as $type) {
            DB::table('expense_types')->insert(['name' => $type, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
