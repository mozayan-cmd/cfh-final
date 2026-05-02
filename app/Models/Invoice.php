<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'buyer_id', 'boat_id', 'landing_id', 'invoice_date',
        'original_amount', 'received_amount', 'pending_amount', 'status', 'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'original_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function landing(): BelongsTo
    {
        return $this->belongsTo(Landing::class);
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
