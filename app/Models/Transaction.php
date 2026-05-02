<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'mode', 'source', 'amount', 'boat_id', 'landing_id',
        'buyer_id', 'invoice_id', 'cash_source_receipt_id',
        'transactionable_type', 'transactionable_id', 'date', 'notes',
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

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashSourceReceipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class, 'cash_source_receipt_id');
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
