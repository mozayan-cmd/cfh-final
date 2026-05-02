<?php

namespace App\Services;

use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\Landing;

class InvoiceImportService
{
    public function parseFile($file): array
    {
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", $content), fn ($line) => trim($line) !== '');

        $parsed = [];
        $errors = [];

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $line = trim($line);

            $result = $this->parseLine($line);

            if ($result === null) {
                $errors[] = [
                    'line' => $lineNumber,
                    'content' => $line,
                    'error' => 'Could not parse buyer name and amount',
                ];

                continue;
            }

            $parsed[] = [
                'line' => $lineNumber,
                'buyer_name' => $result['buyer_name'],
                'amount' => $result['amount'],
                'is_valid' => $result['is_valid'],
                'warning' => $result['warning'] ?? null,
            ];
        }

        return [
            'parsed' => $parsed,
            'errors' => $errors,
            'total_lines' => count($lines),
        ];
    }

    protected function parseLine(string $line): ?array
    {
        $line = trim($line);
        if (empty($line)) {
            return null;
        }

        $line = preg_replace('/\s+/', ' ', $line);

        $patterns = [
            '/^(.+?)\s+(\d+[\d,]*\.?\d*)\s*$/' => 2,
            '/^(.+?)\s+Rs\.?\s*(\d+[\d,]*\.?\d*)\s*$/i' => 2,
            '/^(.+?)\s+(\d+[\d,]*\.\d{1,2})$/' => 2,
        ];

        foreach ($patterns as $pattern => $amountGroup) {
            if (preg_match($pattern, $line, $matches)) {
                $buyerName = trim($matches[1]);
                $amount = (float) str_replace(',', '', $matches[$amountGroup]);

                if ($amount <= 0) {
                    continue;
                }

                $isValid = true;
                $warning = null;

                if (strlen($buyerName) < 2) {
                    $warning = 'Buyer name too short';
                }
                if (preg_match('/^\d+$/', $buyerName)) {
                    $isValid = false;
                    $warning = 'Invalid buyer name (numbers only)';
                }

                return [
                    'buyer_name' => $buyerName,
                    'amount' => $amount,
                    'is_valid' => $isValid,
                    'warning' => $warning,
                ];
            }
        }

        return null;
    }

    public function getLandingForImport(int $boatId, ?string $landingDate): ?Landing
    {
        if (! $landingDate) {
            return null;
        }

        return Landing::where('boat_id', $boatId)
            ->whereDate('date', $landingDate)
            ->first();
    }

    public function getOrCreateLanding(int $boatId, string $date): Landing
    {
        $landing = Landing::where('boat_id', $boatId)
            ->whereDate('date', $date)
            ->first();

        if ($landing) {
            return $landing;
        }

        $boat = Boat::findOrFail($boatId);

        return Landing::create([
            'boat_id' => $boatId,
            'date' => $date,
            'gross_value' => 0,
            'status' => 'Pending',
            'notes' => 'Auto-created from invoice import',
        ]);
    }

    public function getOrCreateBuyer(string $name, int $userId): Buyer
    {
        $normalizedName = $this->normalizeBuyerName($name);

        $buyer = Buyer::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])
            ->where('user_id', $userId)
            ->first();

        if ($buyer) {
            return $buyer;
        }

        return Buyer::create([
            'name' => $normalizedName,
            'phone' => null,
            'notes' => 'Created via invoice import',
            'user_id' => $userId,
        ]);
    }

    protected function normalizeBuyerName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = ucwords(strtolower($name));

        return $name;
    }

    public function importInvoices(array $rows, int $boatId, int $landingId, string $invoiceDate, int $userId): array
    {
        $imported = [];
        $skipped = [];
        $errors = [];

        foreach ($rows as $row) {
            if (! empty($row['error'])) {
                $skipped[] = [
                    'line' => $row['line'],
                    'buyer_name' => $row['buyer_name'] ?? $row['content'] ?? '',
                    'amount' => $row['amount'] ?? 0,
                    'reason' => $row['error'],
                ];

                continue;
            }

            if (! empty($row['is_valid']) && $row['is_valid'] === false) {
                $skipped[] = [
                    'line' => $row['line'],
                    'buyer_name' => $row['buyer_name'],
                    'amount' => $row['amount'],
                    'reason' => $row['warning'] ?? 'Invalid data',
                ];

                continue;
            }

            try {
                $buyer = $this->getOrCreateBuyer($row['buyer_name'], $userId);

                $existing = Invoice::where('buyer_id', $buyer->id)
                    ->where('landing_id', $landingId)
                    ->where('original_amount', $row['amount'])
                    ->where('user_id', $userId)
                    ->first();

                if ($existing) {
                    $skipped[] = [
                        'line' => $row['line'],
                        'buyer_name' => $row['buyer_name'],
                        'amount' => $row['amount'],
                        'reason' => 'Duplicate invoice exists',
                    ];

                    continue;
                }

                $invoice = Invoice::create([
                    'buyer_id' => $buyer->id,
                    'boat_id' => $boatId,
                    'landing_id' => $landingId,
                    'invoice_date' => $invoiceDate,
                    'original_amount' => $row['amount'],
                    'received_amount' => 0,
                    'pending_amount' => $row['amount'],
                    'status' => 'Pending',
                    'notes' => "Imported from file (line {$row['line']})",
                    'user_id' => $userId,
                ]);

                $imported[] = [
                    'line' => $row['line'],
                    'buyer_name' => $buyer->name,
                    'amount' => $row['amount'],
                    'invoice_id' => $invoice->id,
                ];

            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $row['line'],
                    'buyer_name' => $row['buyer_name'],
                    'amount' => $row['amount'],
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_imported' => count($imported),
            'total_skipped' => count($skipped) + count($errors),
        ];
    }
}
