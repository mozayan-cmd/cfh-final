<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\LandingSummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FundFlowReportService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FundFlowReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
{
    protected LandingSummaryService $landingService;
    protected FundFlowReportService $fundFlowService;

    public function __construct(LandingSummaryService $landingService, FundFlowReportService $fundFlowService)
    {
        $this->landingService = $landingService;
        $this->fundFlowService = $fundFlowService;
    }

    public function index()
    {
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();

        $backupsDir = database_path('backups');
        $backups = [];
        if (File::isDirectory($backupsDir)) {
            $files = File::files($backupsDir);
            $backups = array_map(function ($file) {
                return [
                    'filename' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
            }, $files);
        }

        return view('reports.index', compact('boats', 'landings', 'backups'));
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'boat_id' => 'required|exists:boats,id',
            'landing_id' => 'nullable|exists:landings,id',
        ]);

        $boat = Boat::find($request->boat_id);

        if ($request->landing_id) {
            $landings = Landing::where('id', $request->landing_id)
                ->where('boat_id', $request->boat_id)
                ->get();
        } else {
            $landings = Landing::where('boat_id', $request->boat_id)
                ->orderBy('date', 'desc')
                ->get();
        }

        if ($landings->isEmpty()) {
            return back()->with('error', 'No landings found for this boat.');
        }

        $reportData = [];

        foreach ($landings as $landing) {
            $summary = $this->landingService->getSummary($landing);

            $invoices = Invoice::where('landing_id', $landing->id)->get();
            $expenses = Expense::where('landing_id', $landing->id)->get();
            $receipts = Receipt::where('landing_id', $landing->id)->get();
            $payments = Payment::where('landing_id', $landing->id)->get();

            $ownerPayments = $payments->where('payment_for', 'Owner');
            $expensePayments = $payments->where('payment_for', 'Expense');

            $reportData[] = [
                'landing' => $landing,
                'summary' => $summary,
                'invoices' => $invoices,
                'expenses' => $expenses,
                'receipts' => $receipts,
                'payments' => $payments,
                'owner_payments' => $ownerPayments,
                'expense_payments' => $expensePayments,
            ];
        }

        $pdf = Pdf::loadView('reports.settlement-pdf', [
            'boat' => $boat,
            'reportData' => $reportData,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'settlement_'.$boat->name.'_'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    public function fundFlow(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = $this->fundFlowService->getFundFlow($startDate, $endDate);

        return view('reports.fund-flow', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function fundFlowPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = $this->fundFlowService->getFundFlow($startDate, $endDate);

        $pdf = Pdf::loadView('reports.fund-flow-pdf', [
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ]);

        $filename = 'fund-flow-report-'.now()->format('Y-m-d').'.pdf';
        return $pdf->download($filename);
    }

    public function fundFlowExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = $this->fundFlowService->getFundFlow($startDate, $endDate);

        $filename = 'fund-flow-report-'.now()->format('Y-m-d').'.xlsx';
        return Excel::download(new FundFlowReportExport($data), $filename);
    }
}
