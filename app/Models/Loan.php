<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source',
        'amount',
        'repaid_amount',
        'date',
        'mode',
        'notes',
        'repaid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'repaid_amount' => 'decimal:2',
        'date' => 'date',
        'mode' => 'string',
        'repaid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereNull('repaid_at');
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeBySources($query, array $sources)
    {
        return $query->whereIn('source', $sources);
    }

    public function isOutstanding(): bool
    {
        return is_null($this->repaid_at);
    }

    public function isRepaid(): bool
    {
        return ! is_null($this->repaid_at);
    }

    public function markAsRepaid(): void
    {
        $this->update(['repaid_at' => now()]);
    }

    public function getOutstandingAmountAttribute(): float
    {
        return $this->amount - ($this->repaid_amount ?? 0);
    }

    public function addRepayment(float $amount): void
    {
        $newRepaid = ($this->repaid_amount ?? 0) + $amount;
        $this->update(['repaid_amount' => $newRepaid]);

        if ($newRepaid >= $this->amount) {
            $this->update(['repaid_at' => now()]);
        }
    }
}
