<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Collection;

class LoanTrackingService
{
    public const SOURCES = ['Basheer', 'Personal', 'Others'];

    public function getOutstandingLoans(): Collection
    {
        return Loan::outstanding()
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getOutstandingLoansBySource(): array
    {
        $loans = $this->getOutstandingLoans();
        $grouped = [];

        foreach (self::SOURCES as $source) {
            $grouped[$source] = $loans->where('source', $source)->values();
        }

        return $grouped;
    }

    public function getBalanceBySource(string $source): float
    {
        $loans = Loan::outstanding()->bySource($source)->get();
        $total = 0;
        foreach ($loans as $loan) {
            $total += $loan->amount - ($loan->repaid_amount ?? 0);
        }

        return $total;
    }

    public function getTotalOutstanding(): float
    {
        $loans = Loan::outstanding()->get();
        $total = 0;
        foreach ($loans as $loan) {
            $total += $loan->amount - ($loan->repaid_amount ?? 0);
        }

        return $total;
    }

    public function getBalances(): array
    {
        $balances = [];
        foreach (self::SOURCES as $source) {
            $balances[$source] = $this->getBalanceBySource($source);
        }
        $balances['total'] = array_sum($balances);

        return $balances;
    }

    public function recordLoan(array $data): Loan
    {
        return Loan::create([
            'user_id' => auth()->id(),
            'source' => $data['source'],
            'amount' => $data['amount'],
            'date' => $data['date'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function markAsRepaid(Loan $loan): void
    {
        $loan->markAsRepaid();
    }

    public function markAsRepaidBySource(string $source): int
    {
        return Loan::outstanding()->bySource($source)->update(['repaid_at' => now()]);
    }

    public function markAllAsRepaid(): int
    {
        return Loan::outstanding()->update(['repaid_at' => now()]);
    }

    public function getSummary(): array
    {
        return [
            'outstanding' => $this->getTotalOutstanding(),
            'by_source' => $this->getBalances(),
            'count' => Loan::outstanding()->count(),
        ];
    }

    public static function isLoanSource(?string $source): bool
    {
        return in_array($source, self::SOURCES);
    }
}
