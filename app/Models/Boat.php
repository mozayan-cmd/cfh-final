<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boat extends Model
{
    protected $fillable = ['user_id', 'name', 'owner_phone'];

    public function landings(): HasMany
    {
        return $this->hasMany(Landing::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getRelatedRecords(): array
    {
        $related = [];

        if ($this->landings()->exists()) {
            $related[] = [
                'type' => 'Landings',
                'count' => $this->landings()->count(),
                'amount' => $this->landings()->sum('gross_value'),
            ];
        }

        if ($this->expenses()->exists()) {
            $related[] = [
                'type' => 'Expenses',
                'count' => $this->expenses()->count(),
                'amount' => $this->expenses()->sum('amount'),
            ];
        }

        if ($this->invoices()->exists()) {
            $related[] = [
                'type' => 'Invoices',
                'count' => $this->invoices()->count(),
                'amount' => $this->invoices()->sum('original_amount'),
            ];
        }

        if ($this->payments()->exists()) {
            $related[] = [
                'type' => 'Payments',
                'count' => $this->payments()->count(),
                'amount' => $this->payments()->sum('amount'),
            ];
        }

        return $related;
    }
}
