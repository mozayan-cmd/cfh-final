<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    protected $signature = 'user:reset-password {email? : The user email}';
    protected $description = 'Reset user password to "password"';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@example.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $user->password = Hash::make('password');
        $user->save();

        $this->info("Password for {$email} reset to 'password'");
        return 0;
    }
}