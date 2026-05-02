<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    protected $fillable = [
        'user_id', 'boat_id', 'landing_id', 'date', 'type', 'vendor_name',
        'amount', 'paid_amount', 'pending_amount', 'payment_status', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
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

    public function paymentAllocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }

    /**
     * Get the related payment for this expense (via PaymentAllocation).
     */
    public function getPaymentAttribute()
    {
        $allocation = $this->paymentAllocations()->with('payment')->first();
        return $allocation ? $allocation->payment : null;
    }


    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function getAllocatedAmountAttribute()
    {
        return $this->paymentAllocations()->sum('amount');
    }

    public static function types(): array
    {
        return ExpenseType::pluck('name')->toArray();
    }

    public function getRelatedRecords(): array
    {
        $related = [];

        if ($this->paymentAllocations()->exists()) {
            $related[] = [
                'type' => 'Payment Allocations',
                'count' => $this->paymentAllocations()->count(),
                'amount' => $this->paymentAllocations()->sum('amount'),
            ];
        }

        if ($this->transactions()->exists()) {
            $related[] = [
                'type' => 'Transactions',
                'count' => $this->transactions()->count(),
                'amount' => $this->transactions()->sum('amount'),
            ];
        }

        return $related;
    }
}
