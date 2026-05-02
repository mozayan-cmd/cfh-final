<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Receipt;
use App\Services\CashSourceTrackingService;
use Barryvdh\DomPDF\Facade\Pdf;

class CashReportController extends Controller
{
    protected CashSourceTrackingService $cashService;

    public function __construct(CashSourceTrackingService $cashService)
    {
        $this->cashService = $cashService;
    }

    public function cashReport()
    {
        $cashReceipts = Receipt::with(['buyer'])
            ->where('mode', 'Cash')
            ->orderBy('date', 'desc')
            ->get();

        $cashPayments = Payment::with(['boat', 'allocations.allocatable'])
            ->where('mode', 'Cash')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($payment) {
                $vendorName = '-';
                if ($payment->allocations->count() > 0) {
                    $firstAlloc = $payment->allocations->first();
                    if ($firstAlloc && $firstAlloc->allocatable) {
                        $vendorName = $firstAlloc->allocatable->vendor_name
                            ?? $firstAlloc->allocatable->type
                            ?? ($firstAlloc->allocatable->boat ? $firstAlloc->allocatable->boat->name : '-');
                    }
                }
                $payment->vendor_name = $vendorName;
                $payment->type = $payment->allocations->count() > 0 ? 'Expense' : 'Owner';

                return $payment;
            });

        $cashDeposits = $this->cashService->getCashDepositsToBank();

        $totalReceipts = $cashReceipts->sum('amount');
        $totalPayments = $cashPayments->sum('amount');
        $totalDeposits = $cashDeposits->sum('amount');
        $balance = $totalReceipts - $totalPayments - $totalDeposits;

        $data = [
            'title' => 'Cash Receipts & Utilisation Report',
            'reportDate' => now()->format('d M Y'),
            'totalReceipts' => $totalReceipts,
            'totalPayments' => $totalPayments,
            'totalDeposits' => $totalDeposits,
            'balance' => $balance,
            'receipts' => $cashReceipts,
            'payments' => $cashPayments,
            'deposits' => $cashDeposits,
        ];

        $pdf = Pdf::loadView('reports.cash-report', $data);

        return $pdf->download('cash-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function bankReport()
    {
        $bankReceipts = Receipt::with(['buyer'])
            ->whereIn('mode', ['Bank', 'GP'])
            ->orderBy('date', 'desc')
            ->get();

        $bankPayments = Payment::with(['boat', 'allocations.allocatable'])
            ->whereIn('mode', ['Bank', 'GP'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($payment) {
                $vendorName = '-';
                if ($payment->allocations->count() > 0) {
                    $firstAlloc = $payment->allocations->first();
                    if ($firstAlloc && $firstAlloc->allocatable) {
                        $vendorName = $firstAlloc->allocatable->vendor_name
                            ?? $firstAlloc->allocatable->type
                            ?? ($firstAlloc->allocatable->boat ? $firstAlloc->allocatable->boat->name : '-');
                    }
                }
                $payment->vendor_name = $vendorName;
                $payment->type = $payment->allocations->count() > 0 ? 'Expense' : 'Owner';

                return $payment;
            });

        $cashDepositsToBank = $this->cashService->getCashDepositsToBank();

        $totalReceipts = $bankReceipts->sum('amount');
        $totalPayments = $bankPayments->sum('amount');
        $totalCashDeposits = $cashDepositsToBank->sum('amount');
        $balance = $totalReceipts + $totalCashDeposits - $totalPayments;

        $data = [
            'title' => 'Bank Receipts & Utilisation Report',
            'reportDate' => now()->format('d M Y'),
            'totalReceipts' => $totalReceipts,
            'totalPayments' => $totalPayments,
            'totalCashDeposits' => $totalCashDeposits,
            'balance' => $balance,
            'receipts' => $bankReceipts,
            'payments' => $bankPayments,
            'cashDeposits' => $cashDepositsToBank,
        ];

        $pdf = Pdf::loadView('reports.bank-report', $data);

        return $pdf->download('bank-report-'.now()->format('Y-m-d').'.pdf');
    }
}
