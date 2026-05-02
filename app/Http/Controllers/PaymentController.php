<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Boat;
use App\Models\Expense;
use App\Models\Landing;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\CashSourceTrackingService;
use App\Services\ExpenseSettlementService;
use App\Services\LandingSummaryService;
use App\Services\PaymentPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentPostingService $postingService;

    protected ExpenseSettlementService $expenseService;

    protected LandingSummaryService $landingService;

    protected CashSourceTrackingService $cashService;

    public function __construct(
        PaymentPostingService $postingService,
        ExpenseSettlementService $expenseService,
        LandingSummaryService $landingService,
        CashSourceTrackingService $cashService
    ) {
        $this->postingService = $postingService;
        $this->expenseService = $expenseService;
        $this->landingService = $landingService;
        $this->cashService = $cashService;
    }

    public function index(Request $request): View
    {
        $query = Payment::with(['boat', 'landing', 'allocations'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc');

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }
        if ($request->filled('vendor')) {
            $query->where('vendor_name', $request->vendor);
        }

        $payments = $query->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();
        $modes = ['Cash', 'GP', 'Bank'];
        
        // Get unique vendors from payments
        $vendors = Payment::where('user_id', auth()->id())
            ->whereNotNull('vendor_name')
            ->where('vendor_name', '!=', '')
            ->distinct()
            ->orderBy('vendor_name')
            ->pluck('vendor_name')
            ->toArray();
        
        $paymentTypes = PaymentType::orderBy('name')->get();
        $totalAmount = $payments->sum('amount');

        return view('payments.index', compact('payments', 'boats', 'landings', 'modes', 'vendors', 'paymentTypes', 'totalAmount'));
    }

    public function create(Request $request): View
    {
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $modes = ['Cash', 'GP', 'Bank'];
        $paymentFors = PaymentType::pluck('name')->toArray();
        $paymentTypes = PaymentType::orderBy('name')->get();
        $availableCashReceipts = $this->cashService->getAvailableCashReceipts();
        $bankBalance = $this->cashService->getBankBalance();
        $totalAvailableCash = $this->cashService->getTotalAvailableCash();

        $preselectedBoatId = null;
        $preselectedExpense = null;
        $landing = null;

        if ($request->filled('expense_id')) {
            $expense = Expense::where('id', $request->expense_id)->where('user_id', auth()->id())->with('boat')->first();
            if ($expense) {
                $preselectedBoatId = $expense->boat_id;
                $preselectedExpense = [
                    'id' => $expense->id,
                    'type' => $expense->type,
                    'vendor_name' => $expense->vendor_name,
                    'pending_amount' => $expense->pending_amount,
                ];
            }
        }

        return view('payments.create', compact(
            'boats', 'modes', 'paymentFors', 'paymentTypes',
            'landing', 'preselectedBoatId', 'preselectedExpense',
            'availableCashReceipts', 'bankBalance', 'totalAvailableCash'
        ));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['source'] = $data['mode'];
        $data['user_id'] = auth()->id();

        // Validate balances
        if ($data['mode'] === 'Cash' && isset($data['cash_source_receipt_id']) && $data['cash_source_receipt_id']) {
            $receipt = Receipt::where('id', $data['cash_source_receipt_id'])->where('user_id', auth()->id())->first();
            if ($receipt) {
                $utilized = $this->cashService->getUtilizedAmount($receipt->id);
                $deposited = $this->cashService->getDepositedAmount($receipt->id);
                $balance = $receipt->amount - $utilized - $deposited;
                if ($data['amount'] > $balance) {
                    return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds available cash balance for the selected receipt.']);
                }
            }
        }

        if (in_array($data['mode'], ['Bank', 'GP'])) {
            $bankBalance = $this->cashService->getBankBalance();
            if ($data['amount'] > $bankBalance) {
                return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds available bank balance.']);
            }
        }

        if ($data['mode'] === 'Cash' && (!isset($data['cash_source_receipt_id']) || !$data['cash_source_receipt_id'])) {
            $totalAvailableCash = $this->cashService->getTotalAvailableCash();
            if ($data['amount'] > $totalAvailableCash) {
                return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds total available cash balance.']);
            }
        }

        $this->postingService->postPayment($data);

        return redirect()->route('payments.index')
            ->with('success', "Payment of {$data['amount']} recorded successfully.");
    }

    public function show(Payment $payment): View
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $payment->load(['boat', 'landing', 'allocations']);

        $allocations = $payment->allocations->map(function ($allocation) {
            $allocation->target = $allocation->allocatable;

            return $allocation;
        });

        return view('payments.show', compact('payment', 'allocations'));
    }

    public function getLandingsByBoat(string $boat): array
    {
        $landings = Landing::where('boat_id', $boat)
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($landing) {
                $summary = $this->landingService->getSummary($landing);

                return [
                    'id' => $landing->id,
                    'date' => $landing->date->format('Y-m-d'),
                    'status' => $landing->status,
                    'net_owner_payable' => $summary['net_owner_payable'],
                    'owner_pending' => $summary['owner_pending'],
                ];
            });

        return ['landings' => $landings];
    }

    public function getExpensesByBoat(string $boat): array
    {
        $expenses = Expense::where('boat_id', $boat)
            ->where('user_id', auth()->id())
            ->whereIn('payment_status', ['Pending', 'Partial'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'date' => $expense->date->format('Y-m-d'),
                    'type' => $expense->type,
                    'vendor_name' => $expense->vendor_name,
                    'amount' => $expense->amount,
                    'paid_amount' => $expense->paid_amount,
                    'pending_amount' => $expense->pending_amount,
                    'payment_status' => $expense->payment_status,
                ];
            });

        return ['expenses' => $expenses];
    }

    public function getLandingExpenses(Request $request): array
    {
        $landing = Landing::where('id', $request->landing_id)->where('user_id', auth()->id())->with('boat')->first();

        if (! $landing) {
            return ['landing' => null, 'pending_expenses' => []];
        }

        $pendingExpenses = Expense::where('boat_id', $landing->boat_id)
            ->where(function ($q) use ($landing) {
                $q->where('landing_id', $landing->id)
                    ->orWhereNull('landing_id');
            })
            ->where('payment_status', '!=', 'Paid')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'type' => $expense->type,
                    'vendor_name' => $expense->vendor_name,
                    'amount' => $expense->amount,
                    'pending_amount' => $expense->pending_amount,
                ];
            });

        $summary = $this->landingService->getSummary($landing);

        return [
            'landing' => [
                'id' => $landing->id,
                'date' => $landing->date->format('Y-m-d'),
                'gross_value' => $landing->gross_value,
                'boat_name' => $landing->boat->name,
                'net_owner_payable' => $summary['net_owner_payable'],
                'owner_pending' => $summary['owner_pending'],
            ],
            'pending_expenses' => $pendingExpenses,
        ];
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->postingService->reversePayment($payment);

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    public function edit(Payment $payment): View
    {
        $payment->load(['boat', 'landing', 'allocations.allocatable']);
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $modes = ['Cash', 'GP', 'Bank'];
        $sources = ['Cash', 'Personal', 'Bank', 'Basheer', 'Other'];
        $paymentFors = PaymentType::pluck('name')->toArray();

        $existingAllocations = $payment->allocations->map(function ($allocation) {
            return [
                'type' => $allocation->allocatable_type === Expense::class ? 'expense' : 'landing',
                'id' => $allocation->allocatable_id,
                'amount' => $allocation->amount,
            ];
        })->toArray();

        $currentLanding = null;
        if ($payment->landing) {
            $summary = $this->landingService->getSummary($payment->landing);
            $currentLanding = [
                'id' => $payment->landing->id,
                'date' => $payment->landing->date->format('Y-m-d'),
                'status' => $payment->landing->status,
                'net_owner_payable' => $summary['net_owner_payable'],
                'owner_pending' => $summary['owner_pending'],
            ];
        }

        return view('payments.edit', compact(
            'payment', 'boats', 'modes', 'sources', 'paymentFors', 'existingAllocations', 'currentLanding'
        ));
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $this->postingService->updatePayment($payment, $request->validated());

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function export(Request $request)
    {
        $query = Payment::with(['boat', 'landing', 'allocations.allocatable']);

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('payment_for')) {
            $query->where('payment_for', $request->payment_for);
        }

        $payments = $query->orderBy('date', 'desc')->get();

        $lines = [];
        $lines[] = 'ID,Date,Boat,Landing Date,Amount,Mode,Source,Payment For,Notes,Allocations';

        foreach ($payments as $payment) {
            $allocationDetails = $payment->allocations->map(function ($alloc) {
                $type = class_basename($alloc->allocatable_type ?? '');
                $name = '';

                if ($alloc->allocatable) {
                    $name = $alloc->allocatable->vendor_name ?? $alloc->allocatable->type ??
                           ($alloc->allocatable->boat ? $alloc->allocatable->boat->name : '') ?? '';
                }

                return "{$type}: {$name} (₹{$alloc->amount})";
            })->implode('; ');

            $lines[] = sprintf('%d,%s,%s,%s,%s,%s,%s,%s,"%s","%s"',
                $payment->id,
                $payment->date->format('Y-m-d'),
                $payment->boat->name ?? '',
                $payment->landing ? $payment->landing->date->format('Y-m-d') : '',
                number_format($payment->amount, 2),
                $payment->mode,
                $payment->source,
                $payment->payment_for,
                $payment->notes ?? '',
                $allocationDetails
            );
        }

        $content = implode("\n", $lines);
        $filename = 'payments_export_'.date('Y-m-d_His').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(): View
    {
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();
        $modes = ['Cash', 'GP', 'Bank'];
        $sources = ['Cash', 'Personal', 'Bank', 'Basheer', 'Other'];
        $paymentFors = PaymentType::pluck('name')->toArray();

        return view('payments.import', compact('boats', 'landings', 'modes', 'sources', 'paymentFors'));
    }

    public function previewImport(Request $request)
    {
        $pasteMode = $request->filled('paste_mode') && $request->paste_mode == '1';

        $boatId = $request->input('boat_id');
        $landingId = $request->input('landing_id');
        $date = $request->input('date', date('Y-m-d'));
        $mode = $request->input('mode', 'Cash');
        $source = $request->input('source', 'Cash');
        $paymentFor = $request->input('payment_for', 'Owner');

        if ($pasteMode) {
            $validated = $request->validate([
                'boat_id' => 'required|exists:boats,id',
                'date' => 'required|date',
                'mode' => 'required|string',
                'source' => 'required|string',
                'payment_for' => 'required|string',
                'paste_data' => 'required|string|min:1',
            ]);

            $results = [];
            $errors = [];
            $lines = explode("\n", $validated['paste_data']);

            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $amount = trim($line);

                if (! is_numeric(str_replace(',', '', $amount))) {
                    $errors[] = 'Line '.($lineNum + 1).": Invalid amount '{$amount}'";

                    continue;
                }

                $amount = (float) str_replace(',', '', $amount);

                if ($amount <= 0) {
                    $errors[] = 'Line '.($lineNum + 1).': Amount must be greater than 0';

                    continue;
                }

                $results[] = [
                    'line' => $lineNum + 1,
                    'amount' => $amount,
                    'boat_id' => $boatId,
                    'landing_id' => $landingId,
                    'date' => $date,
                    'mode' => $mode,
                    'source' => $source,
                    'payment_for' => $paymentFor,
                ];
            }
        } else {
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
                'boat_id' => 'required|exists:boats,id',
                'date' => 'required|date',
                'mode' => 'required|string',
                'source' => 'required|string',
                'payment_for' => 'required|string',
            ]);

            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            $results = [];
            $errors = [];
            $lineNumber = 0;

            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if ($lineNumber == 1 && strtolower($data[0] ?? '') === 'amount') {
                    continue;
                }

                $amount = trim($data[0] ?? '');

                if (empty($amount) || ! is_numeric(str_replace(',', '', $amount))) {
                    if ($lineNumber > 1) {
                        $errors[] = "Line {$lineNumber}: Invalid amount '{$amount}'";
                    }

                    continue;
                }

                $amount = (float) str_replace(',', '', $amount);

                if ($amount <= 0) {
                    $errors[] = "Line {$lineNumber}: Amount must be greater than 0";

                    continue;
                }

                $results[] = [
                    'line' => $lineNumber,
                    'amount' => $amount,
                    'boat_id' => $boatId,
                    'landing_id' => $landingId,
                    'date' => $date,
                    'mode' => $mode,
                    'source' => $source,
                    'payment_for' => $paymentFor,
                ];
            }

            fclose($handle);
        }

        if (empty($results) && ! empty($errors)) {
            return redirect()->back()->with('error', 'No valid rows found. '.implode(', ', $errors));
        }

        $request->session()->put('payment_import_data', $results);

        return view('payments.import-preview', [
            'results' => $results,
            'errors' => $errors,
        ]);
    }

    public function processImport(Request $request)
    {
        $importData = $request->session()->get('payment_import_data', []);

        if (empty($importData)) {
            return redirect()->route('payments.index')
                ->with('error', 'No data to import.');
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($importData as $row) {
            $landing = $row['landing_id'] ? Landing::find($row['landing_id']) : null;
            $boatId = $row['boat_id'] ?? ($landing ? $landing->boat_id : null);

            if (! $boatId) {
                $errors[] = "Row {$row['line']}: Boat not specified";
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($row, $boatId, &$created) {
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'boat_id' => $boatId,
                    'landing_id' => $row['landing_id'],
                    'date' => $row['date'],
                    'amount' => $row['amount'],
                    'mode' => $row['mode'],
                    'source' => $row['source'],
                    'payment_for' => $row['payment_for'],
                ]);

                Transaction::create([
                    'user_id' => auth()->id(),
                    'type' => 'Payment',
                    'mode' => $row['mode'],
                    'source' => $row['source'],
                    'amount' => $row['amount'],
                    'boat_id' => $boatId,
                    'landing_id' => $row['landing_id'],
                    'transactionable_type' => Payment::class,
                    'transactionable_id' => $payment->id,
                    'date' => $row['date'],
                ]);

                $created++;
            });
        }

        $request->session()->forget('payment_import_data');

        $message = "Imported {$created} payments.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped}.";
        }

        return redirect()->route('payments.index')
            ->with('success', $message);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
        ]);

        PaymentType::create($validated);

        return redirect()->back()->with('success', 'Payment type added successfully.');
    }
}
