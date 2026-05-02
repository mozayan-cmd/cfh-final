<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Table already exists with VARCHAR columns, no schema change needed
        // The 'Basheer' value can be stored in the existing source and payment_for columns
    }

    public function down(): void
    {
        // No action needed
    }
};
