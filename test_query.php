<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== USERS ===\n";
foreach (DB::table('users')->get() as $u) {
    echo "ID: {$u->id}, Email: {$u->email}, Name: {$u->name}, Role: {$u->role}, Active: {$u->is_active}\n";
}

echo "\n=== BOATS ===\n";
foreach (DB::table('boats')->get() as $b) {
    echo "ID: {$b->id}, Name: {$b->name}\n";
}

echo "\n=== BUYERS ===\n";
foreach (DB::table('buyers')->get() as $b) {
    echo "ID: {$b->id}, Name: {$b->name}\n";
}
