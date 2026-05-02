<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->id === 1 || $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->is_active === true || $this->is_active === 1 || $this->is_active === '1';
    }

    public function landings()
    {
        return $this->hasMany(Landing::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function boats()
    {
        return $this->hasMany(Boat::class);
    }

    public function buyers()
    {
        return $this->hasMany(Buyer::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            if (! User::where('role', 'admin')->exists() && User::count() === 0) {
                $user->role = 'admin';
                $user->is_active = true;
            }
        });
    }
}
