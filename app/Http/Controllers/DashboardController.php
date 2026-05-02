<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummaryService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardSummaryService $summaryService;

    public function __construct(DashboardSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(): View
    {
        $summary = $this->summaryService->getSummary();
        $recentLandings = $this->summaryService->getRecentLandings();
        $recentReceipts = $this->summaryService->getRecentReceipts();
        $recentPayments = $this->summaryService->getRecentPayments();
        $pendingSettlements = $this->summaryService->getPendingSettlements();

        return view('dashboard.index', compact(
            'summary',
            'recentLandings',
            'recentReceipts',
            'recentPayments',
            'pendingSettlements'
        ));
    }
}
