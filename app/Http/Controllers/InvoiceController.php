<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\Landing;
use App\Services\InvoiceImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    protected InvoiceImportService $importService;

    public function __construct(InvoiceImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index(Request $request): View
    {
        $query = Invoice::with(['buyer', 'boat', 'landing'])
            ->where('user_id', auth()->id())
            ->orderBy('invoice_date', 'desc');

        if ($request->filled('buyer_id')) {
            if ($request->buyer_id === 'pending_only') {
                $buyerIds = Invoice::where('status', '!=', 'Paid')->pluck('buyer_id')->unique();
                $query->whereIn('buyer_id', $buyerIds);
            } else {
                $query->where('buyer_id', $request->buyer_id);
            }
        }
        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->get();

        // Calculate totals from the filtered invoices
        $totalPending = $invoices->sum('pending_amount');
        $totalOriginal = $invoices->sum('original_amount');
        $totalReceived = $invoices->sum('received_amount');

        $buyers = Buyer::where('user_id', auth()->id())->orderBy('name')->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->orderBy('date', 'desc')->get();

        return view('invoices.index', compact('invoices', 'buyers', 'boats', 'landings', 'totalPending', 'totalOriginal', 'totalReceived'));
    }

    public function create(): View
    {
        $buyers = Buyer::where('user_id', auth()->id())->orderBy('name')->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();

        return view('invoices.create', compact('buyers', 'boats', 'landings'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['received_amount'] = 0;
        $data['pending_amount'] = $data['original_amount'];
        $data['status'] = 'Pending';
        $data['user_id'] = auth()->id();

        Invoice::create($data);

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $invoice->load(['buyer', 'boat', 'landing', 'receipts']);

        return view('invoices.show', compact('invoice'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();

        if ($invoice->landing && $data['original_amount'] > $invoice->landing->gross_value) {
            return redirect()->back()
                ->with('error', 'Invoice amount cannot exceed landing gross value of ₹'.number_format($invoice->landing->gross_value, 2))
                ->withInput();
        }

        $data['pending_amount'] = $data['original_amount'] - $invoice->received_amount;

        if ($data['pending_amount'] < 0) {
            $data['pending_amount'] = 0;
            $data['status'] = 'Paid';
        } elseif ($data['pending_amount'] <= 0) {
            $data['status'] = 'Paid';
        } elseif ($invoice->received_amount > 0) {
            $data['status'] = 'Partial';
        } else {
            $data['status'] = 'Pending';
        }

        $invoice->update($data);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function import(): View
    {
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();

        return view('invoices.import', compact('boats', 'landings'));
    }

    public function preview(Request $request)
    {
        $pasteMode = $request->filled('paste_mode') && $request->paste_mode == '1';

        if ($pasteMode) {
            $validated = $request->validate([
                'boat_id' => ['required', 'exists:boats,id', function ($attribute, $value, $fail) {
                    if (! Boat::where('id', $value)->where('user_id', auth()->id())->exists()) {
                        $fail('Access denied to this boat.');
                    }
                }],
                'landing_id' => ['required', 'exists:landings,id', function ($attribute, $value, $fail) {
                    if (! Landing::where('id', $value)->where('user_id', auth()->id())->exists()) {
                        $fail('Access denied to this landing.');
                    }
                }],
                'invoice_date' => 'required|date',
                'paste_data' => 'required|string|min:1',
            ]);

            $parsed = [];
            $errors = [];
            $lines = explode("\n", $validated['paste_data']);
            $totalLines = count($lines);

            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $parts = explode('|', $line);
                if (count($parts) < 2) {
                    $errors[] = [
                        'line' => $lineNum + 1,
                        'content' => $line,
                        'error' => 'Invalid format'
                    ];

                    continue;
                }

                $buyerName = trim($parts[0]);
                $amount = trim($parts[1]);

                if (empty($buyerName)) {
                    $errors[] = [
                        'line' => $lineNum + 1,
                        'content' => $line,
                        'error' => 'Empty buyer name'
                    ];

                    continue;
                }

                if (! is_numeric(str_replace(',', '', $amount))) {
                    $errors[] = [
                        'line' => $lineNum + 1,
                        'content' => $line,
                        'error' => "Invalid amount '{$amount}'"
                    ];

                    continue;
                }

                $parsed[] = [
                    'buyer_name' => $buyerName,
                    'amount' => (float) str_replace(',', '', $amount),
                ];
            }
        } else {
            $validated = $request->validate([
                'boat_id' => ['required', 'exists:boats,id', function ($attribute, $value, $fail) {
                    if (! Boat::where('id', $value)->where('user_id', auth()->id())->exists()) {
                        $fail('Access denied to this boat.');
                    }
                }],
                'landing_id' => ['required', 'exists:landings,id', function ($attribute, $value, $fail) {
                    if (! Landing::where('id', $value)->where('user_id', auth()->id())->exists()) {
                        $fail('Access denied to this landing.');
                    }
                }],
                'invoice_date' => 'required|date',
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('file');
            $parseResult = $this->importService->parseFile($file);
            $parsed = $parseResult['parsed'];
            $errors = $parseResult['errors'];
            $totalLines = $parseResult['total_lines'];
        }

        $boat = Boat::findOrFail($validated['boat_id']);
        $landing = Landing::with('boat')->findOrFail($validated['landing_id']);

        // Authorization checks
        if ($boat->user_id !== auth()->id()) {
            abort(403, 'Access denied to this boat.');
        }
        if ($landing->user_id !== auth()->id()) {
            abort(403, 'Access denied to this landing.');
        }

        $request->session()->put('import_data', [
            'boat_id' => $validated['boat_id'],
            'landing_id' => $validated['landing_id'],
            'invoice_date' => $validated['invoice_date'],
            'parsed_rows' => $parsed,
            'file_name' => $pasteMode ? 'paste_data' : ($file->getClientOriginalName() ?? 'import.txt'),
        ]);

        return view('invoices.import-preview', [
            'boat' => $boat,
            'landing' => $landing,
            'invoiceDate' => $validated['invoice_date'],
            'parsedRows' => $parsed,
            'parseErrors' => $errors,
            'totalLines' => $totalLines,
            'fileName' => $pasteMode ? 'Pasted Data' : ($file->getClientOriginalName() ?? 'import.txt'),
        ]);
    }

    public function processImport(Request $request): RedirectResponse
    {
        $data = $request->session()->get('import_data');

        if (! $data) {
            return redirect()->route('invoices.import')
                ->with('error', 'Session expired. Please try again.');
        }

        $boat = Boat::findOrFail($data['boat_id']);
        $landing = Landing::findOrFail($data['landing_id']);

        // Authorization checks
        if ($boat->user_id !== auth()->id()) {
            abort(403, 'Access denied to this boat.');
        }
        if ($landing->user_id !== auth()->id()) {
            abort(403, 'Access denied to this landing.');
        }

        $result = $this->importService->importInvoices(
            $data['parsed_rows'],
            $data['boat_id'],
            $data['landing_id'],
            $data['invoice_date'],
            auth()->id()
        );

        $request->session()->forget('import_data');

        $message = "Import complete: {$result['total_imported']} invoices imported";
        if ($result['total_skipped'] > 0) {
            $message .= ", {$result['total_skipped']} skipped";
        }

        return redirect()->route('landings.show', $landing)
            ->with('success', $message);
    }

    public function getLandingsByBoat(Boat $boat): JsonResponse
    {
        $landings = Landing::where('boat_id', $boat->id)
            ->with('invoices')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($landing) {
                return [
                    'id' => $landing->id,
                    'date' => $landing->date->format('Y-m-d'),
                    'status' => $landing->status,
                    'invoice_total' => $landing->invoices->sum('amount'),
                ];
            });

        return response()->json($landings);
    }

    public function getLandingDatesWithPendingInvoices(Boat $boat): JsonResponse
    {
        $landingsWithPending = Landing::where('boat_id', $boat->id)
            ->whereHas('invoices', function ($query) {
                $query->where('status', '!=', 'Paid');
            })
            ->orderBy('date', 'desc')
            ->get(['id', 'date'])
            ->map(function ($landing) {
                $pendingAmount = Invoice::where('landing_id', $landing->id)
                    ->where('status', '!=', 'Paid')
                    ->sum('pending_amount');

                return [
                    'id' => $landing->id,
                    'date' => $landing->date->format('Y-m-d'),
                    'pending_amount' => $pendingAmount,
                ];
            });

        return response()->json($landingsWithPending);
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->receipts()->exists()) {
            return redirect()->route('invoices.index')
                ->with('error', 'Cannot delete invoice with existing receipts. Please delete receipts first.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Invoice::with(['buyer', 'boat', 'landing']);

        if ($request->filled('buyer_id')) {
            if ($request->buyer_id === 'pending_only') {
                $buyerIds = Invoice::where('status', '!=', 'Paid')->pluck('buyer_id')->unique();
                $query->whereIn('buyer_id', $buyerIds);
            } else {
                $query->where('buyer_id', $request->buyer_id);
            }
        }
        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        $lines = [];
        $lines[] = 'ID,Invoice Date,Buyer,Boat,Landing Date,Original Amount,Received Amount,Pending Amount,Status,Notes';

        foreach ($invoices as $invoice) {
            $lines[] = sprintf('%d,%s,"%s",%s,%s,%s,%s,%s,%s,"%s"',
                $invoice->id,
                $invoice->invoice_date->format('Y-m-d'),
                $invoice->buyer->name ?? '',
                $invoice->boat->name ?? '',
                $invoice->landing ? $invoice->landing->date->format('Y-m-d') : '',
                number_format($invoice->original_amount, 2),
                number_format($invoice->received_amount, 2),
                number_format($invoice->pending_amount, 2),
                $invoice->status,
                $invoice->notes ?? ''
            );
        }

        $content = implode("\n", $lines);
        $filename = 'invoices_export_'.date('Y-m-d_His').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
