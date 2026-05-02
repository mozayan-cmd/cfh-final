<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('loan_sources')->insert([
            ['name' => 'Basheer'],
            ['name' => 'Personal'],
            ['name' => 'Others'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_sources');
    }
};