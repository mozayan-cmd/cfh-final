<?php

namespace Database\Seeders;

use App\Models\Buyer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    public function run(): void
    {
        Buyer::create([
            'user_id' => 1,
            'name' => 'Test Buyer',
            'phone' => '1234567890',
            'address' => 'Test Address',
        ]);
    }
}
