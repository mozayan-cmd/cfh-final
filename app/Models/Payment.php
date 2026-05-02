<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'boat_id', 'landing_id', 'date', 'amount', 'mode',
        'source', 'payment_for', 'loan_reference', 'notes', 'vendor_name',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function landing(): BelongsTo
    {
        return $this->belongsTo(Landing::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function getRelatedRecords(): array
    {
        $related = [];

        if ($this->allocations()->exists()) {
            $related[] = [
                'type' => 'Payment Allocations',
                'count' => $this->allocations()->count(),
                'amount' => $this->allocations()->sum('amount'),
            ];
        }

        if ($this->transaction) {
            $related[] = [
                'type' => 'Transaction',
                'count' => 1,
                'amount' => $this->amount,
            ];
        }

        return $related;
    }
}
