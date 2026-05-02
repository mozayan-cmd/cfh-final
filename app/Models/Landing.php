<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Landing extends Model
{
    protected $fillable = ['user_id', 'boat_id', 'date', 'gross_value', 'notes', 'status'];

    protected $casts = [
        'date' => 'date',
        'gross_value' => 'decimal:2',
    ];

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
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

    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    public function getTotalExpensesPaidAttribute()
    {
        return $this->expenses()->sum('paid_amount');
    }

    public function getNetOwnerPayableAttribute()
    {
        return $this->gross_value - $this->total_expenses;
    }

    public function getTotalOwnerPaidAttribute()
    {
        return $this->payments()
            ->where('payment_for', '!=', 'Expense')
            ->sum('amount');
    }

    public function getOwnerPendingAttribute()
    {
        return $this->net_owner_payable - $this->total_owner_paid;
    }

    public function getTotalInvoicesAttribute()
    {
        return $this->invoices()->sum('original_amount');
    }

    public function getTotalReceivedAttribute()
    {
        return $this->invoices()->sum('received_amount');
    }

    public function getTotalBuyerPendingAttribute()
    {
        return $this->invoices()->sum('pending_amount');
    }

    public function getRelatedRecords(): array
    {
        $related = [];

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

        if ($this->receipts()->exists()) {
            $related[] = [
                'type' => 'Receipts',
                'count' => $this->receipts()->count(),
                'amount' => $this->receipts()->sum('amount'),
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
