<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buyer extends Model
{
    protected $fillable = ['user_id', 'name', 'phone', 'address', 'notes'];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getRelatedRecords(): array
    {
        $related = [];

        if ($this->invoices()->exists()) {
            $related[] = [
                'type' => 'Invoices',
                'count' => $this->invoices()->count(),
                'amount' => $this->invoices()->sum('original_amount'),
            ];
        }

        if ($this->receipts()->exists()) {
            $related[] = [
                'type' => 'Receipts',
                'count' => $this->receipts()->count(),
                'amount' => $this->receipts()->sum('amount'),
            ];
        }

        return $related;
    }
}
