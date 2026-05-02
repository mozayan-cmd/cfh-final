<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Receipt extends Model
{
    protected $fillable = [
        'user_id', 'buyer_id', 'invoice_id', 'boat_id', 'landing_id',
        'date', 'amount', 'mode', 'source', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function landing(): BelongsTo
    {
        return $this->belongsTo(Landing::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'transactionable');
    }

    public function getRelatedRecords(): array
    {
        $related = [];

        if ($this->invoice && ! $this->invoice->trashed()) {
            $related[] = [
                'type' => 'Invoice',
                'count' => 1,
                'amount' => $this->invoice->original_amount,
                'label' => 'Invoice #'.$this->invoice->id.' (₹'.number_format($this->invoice->original_amount, 2).')',
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
