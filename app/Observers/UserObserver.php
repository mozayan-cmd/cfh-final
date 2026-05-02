<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! $adminExists) {
            $user->role = 'admin';
            $user->is_active = true;
        }
    }
}
