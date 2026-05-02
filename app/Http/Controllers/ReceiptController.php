<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceiptRequest;
use App\Models\Boat;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\Landing;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\InvoicePostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    protected InvoicePostingService $postingService;

    public function __construct(InvoicePostingService $postingService)
    {
        $this->postingService = $postingService;
    }

    public function index(Request $request): View
    {
        $query = Receipt::with(['buyer', 'invoice', 'boat', 'landing'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc');

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('mode')) {
            if ($request->mode === 'Bank') {
                $query->whereIn('mode', ['Bank', 'GP']);
            } else {
                $query->where('mode', $request->mode);
            }
        }

        $receipts = $query->get();
        $buyers = Buyer::where('user_id', auth()->id())->orderBy('name')->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();

        $totalCash = $receipts->where('mode', 'Cash')->sum('amount');
        $totalBank = $receipts->whereIn('mode', ['Bank', 'GP'])->sum('amount');
        $totalAmount = $receipts->sum('amount');

        $buyerBreakdown = null;
        if ($request->filled('boat_id')) {
            $buyerBreakdown = Receipt::where('boat_id', $request->boat_id)
                ->where('user_id', auth()->id())
                ->when($request->filled('landing_id'), fn ($q) => $q->where('landing_id', $request->landing_id))
                ->when($request->filled('mode'), function ($q) use ($request) {
                    if ($request->mode === 'Bank') {
                        return $q->whereIn('mode', ['Bank', 'GP']);
                    }

                    return $q->where('mode', $request->mode);
                })
                ->select('buyer_id')
                ->selectRaw('COUNT(*) as receipt_count')
                ->selectRaw('SUM(CASE WHEN mode = "Cash" THEN amount ELSE 0 END) as cash_total')
                ->selectRaw('SUM(CASE WHEN mode IN ("Bank", "GP") THEN amount ELSE 0 END) as bank_total')
                ->selectRaw('SUM(amount) as total_amount')
                ->with('buyer:id,name')
                ->groupBy('buyer_id')
                ->orderByDesc('total_amount')
                ->get();
        }

        return view('receipts.index', compact(
            'receipts', 'buyers', 'boats', 'landings',
            'totalCash', 'totalBank', 'totalAmount', 'buyerBreakdown'
        ));
    }

    public function create(): View
    {
        $buyers = Buyer::where('user_id', auth()->id())->orderBy('name')->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();
        $modes = ['Cash', 'GP', 'Bank'];

        return view('receipts.create', compact('buyers', 'boats', 'landings', 'modes'));
    }

    public function store(StoreReceiptRequest $request): RedirectResponse
    {
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::where('id', $request->invoice_id)->where('user_id', auth()->id())->first();
            if (! $invoice) {
                return back()->with('error', 'Invoice not found.')->withInput();
            }
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $this->postingService->postReceipt($data);

        return redirect()->route('receipts.index')
            ->with('success', "Receipt of {$request->amount} recorded successfully.");
    }

    public function getInvoicesByBuyer($buyer): JsonResponse
    {
        $invoices = Invoice::where('buyer_id', $buyer)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Partial'])
            ->with('landing.boat')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                    'original_amount' => $invoice->original_amount,
                    'received_amount' => $invoice->received_amount,
                    'pending_amount' => $invoice->pending_amount,
                    'boat_name' => $invoice->landing->boat->name ?? 'N/A',
                    'landing_date' => $invoice->landing->date->format('Y-m-d') ?? 'N/A',
                ];
            });

        return response()->json(['invoices' => $invoices]);
    }

    public function getBuyersByBoat(Request $request): JsonResponse
    {
        $boatId = $request->input('boat_id');
        $landingId = $request->input('landing_id');

        $query = Invoice::where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Partial'])
            ->with('buyer', 'landing.boat');

        if ($boatId) {
            $query->where('boat_id', $boatId);
        }

        if ($landingId) {
            $query->where('landing_id', $landingId);
        }

        $invoices = $query->get();

        $buyers = $invoices->pluck('buyer')->unique('id')->map(function ($buyer) {
            return [
                'id' => $buyer->id,
                'name' => $buyer->name,
            ];
        })->sortBy('name')->values();

        return response()->json(['buyers' => $buyers]);
    }

    public function getInvoicesByBuyerAndLanding(Request $request): JsonResponse
    {
        $buyerId = $request->input('buyer_id');
        $boatId = $request->input('boat_id');
        $landingId = $request->input('landing_id');

        $query = Invoice::where('buyer_id', $buyerId)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Partial'])
            ->with('landing.boat');

        if ($boatId) {
            $query->where('boat_id', $boatId);
        }

        if ($landingId) {
            $query->where('landing_id', $landingId);
        }

        $invoices = $query->get()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'original_amount' => $invoice->original_amount,
                'received_amount' => $invoice->received_amount,
                'pending_amount' => $invoice->pending_amount,
                'boat_name' => $invoice->landing->boat->name ?? 'N/A',
                'landing_date' => $invoice->landing->date->format('Y-m-d') ?? 'N/A',
            ];
        });

        return response()->json(['invoices' => $invoices]);
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['buyer', 'invoice', 'boat', 'landing']);

        return view('receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt): View
    {
        $receipt->load(['buyer', 'invoice', 'boat', 'landing']);
        $buyers = Buyer::orderBy('name')->get();
        $modes = ['Cash', 'GP', 'Bank'];

        return view('receipts.edit', compact('receipt', 'buyers', 'modes'));
    }

    public function update(Request $request, Receipt $receipt): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'required|in:Cash,GP,Bank',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($receipt, $validated) {
            $oldAmount = $receipt->amount;
            $invoice = $receipt->invoice;

            if (! $invoice) {
                throw new \Exception('Invoice not found for this receipt.');
            }

            $invoice->received_amount = $invoice->received_amount - $oldAmount + $validated['amount'];
            $invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
            $invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : ($invoice->received_amount > 0 ? 'Partial' : 'Pending');
            $invoice->save();

            $receipt->update([
                'date' => $validated['date'],
                'amount' => $validated['amount'],
                'mode' => $validated['mode'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($receipt->transaction && $validated['mode'] === 'Cash') {
                $receipt->transaction->update([
                    'date' => $validated['date'],
                    'mode' => $validated['mode'],
                    'amount' => $validated['amount'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            } elseif ($receipt->transaction && $validated['mode'] !== 'Cash') {
                // If mode changed from Cash to Bank/GP, delete the incorrect transaction
                $receipt->transaction->delete();
            }
        });

        return redirect()->route('receipts.show', $receipt)
            ->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Receipt $receipt): RedirectResponse
    {
        DB::transaction(function () use ($receipt) {
            $invoice = $receipt->invoice;

            if (! $invoice) {
                throw new \Exception('Invoice not found for this receipt.');
            }

            $invoice->received_amount = $invoice->received_amount - $receipt->amount;
            $invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
            $invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : ($invoice->received_amount > 0 ? 'Partial' : 'Pending');
            $invoice->save();

            if ($receipt->transaction) {
                $receipt->transaction->delete();
            }
            $receipt->delete();
        });

        return redirect()->route('receipts.index')
            ->with('success', 'Receipt deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Receipt::with(['buyer', 'invoice', 'boat', 'landing']);

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('mode')) {
            if ($request->mode === 'Bank') {
                $query->whereIn('mode', ['Bank', 'GP']);
            } else {
                $query->where('mode', $request->mode);
            }
        }

        $receipts = $query->orderBy('date', 'desc')->get();

        $lines = [];
        $lines[] = 'ID,Date,Buyer,Invoice Date,Boat,Landing Date,Amount,Mode,Source,Notes';

        foreach ($receipts as $receipt) {
            $lines[] = sprintf('%d,%s,"%s",%s,%s,%s,%s,%s,%s,"%s"',
                $receipt->id,
                $receipt->date->format('Y-m-d'),
                $receipt->buyer->name ?? '',
                $receipt->invoice ? $receipt->invoice->invoice_date->format('Y-m-d') : '',
                $receipt->boat->name ?? '',
                $receipt->landing ? $receipt->landing->date->format('Y-m-d') : '',
                number_format($receipt->amount, 2),
                $receipt->mode,
                $receipt->source,
                $receipt->notes ?? ''
            );
        }

        $content = implode("\n", $lines);
        $filename = 'receipts_export_'.date('Y-m-d_His').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(): View
    {
        $buyers = Buyer::orderBy('name')->get();
        $invoices = Invoice::with(['buyer', 'landing.boat'])
            ->whereIn('status', ['Pending', 'Partial'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        return view('receipts.import', compact('buyers', 'invoices'));
    }

    public function previewImport(Request $request)
    {
        $pasteMode = $request->filled('paste_mode') && $request->paste_mode == '1';

        $buyerName = $request->input('buyer_name');
        $invoiceId = $request->input('invoice_id');
        $date = $request->input('date', date('Y-m-d'));
        $mode = $request->input('mode', 'Cash');

        if ($pasteMode) {
            $validated = $request->validate([
                'buyer_name' => 'required|string',
                'invoice_id' => 'required|exists:invoices,id',
                'date' => 'required|date',
                'mode' => 'required|in:Cash,Bank,GP',
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
                ];
            }
        } else {
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
                'buyer_name' => 'required|string',
                'invoice_id' => 'required|exists:invoices,id',
                'date' => 'required|date',
                'mode' => 'required|in:Cash,Bank,GP',
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
                ];
            }
            fclose($handle);
        }

        $enrichedResults = [];
        foreach ($results as $row) {
            $enrichedResults[] = array_merge($row, [
                'buyer_name' => $buyerName,
                'invoice_id' => $invoiceId,
                'date' => $date,
                'mode' => $mode,
            ]);
        }

        if (empty($enrichedResults) && ! empty($errors)) {
            return redirect()->back()->with('error', 'No valid rows found. '.implode(', ', $errors));
        }

        $request->session()->put('receipt_import_data', $enrichedResults);

        return view('receipts.import-preview', [
            'results' => $results,
            'errors' => $errors,
        ]);
    }

    public function processImport(Request $request)
    {
        $importData = $request->session()->get('receipt_import_data', []);

        if (empty($importData)) {
            return redirect()->route('receipts.index')
                ->with('error', 'No data to import.');
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        $validRows = [];
        foreach ($importData as $row) {
            $invoice = Invoice::find($row['invoice_id']);

            if (! $invoice) {
                $errors[] = "Row {$row['line']}: Invoice not found";
                $skipped++;

                continue;
            }

            if ($row['amount'] > $invoice->pending_amount) {
                $errors[] = "Row {$row['line']}: Amount ₹{$row['amount']} exceeds pending ₹{$invoice->pending_amount}";
                $skipped++;

                continue;
            }

            $validRows[] = ['row' => $row, 'invoice' => $invoice];
        }

        DB::transaction(function () use (&$created, $validRows) {
            foreach ($validRows as $item) {
                $row = $item['row'];
                $invoice = $item['invoice'];

                $receipt = Receipt::create([
                    'user_id' => auth()->id(),
                    'buyer_id' => $invoice->buyer_id,
                    'invoice_id' => $invoice->id,
                    'boat_id' => $invoice->boat_id,
                    'landing_id' => $invoice->landing_id,
                    'date' => $row['date'],
                    'amount' => $row['amount'],
                    'mode' => $row['mode'],
                ]);

                $invoice->received_amount = $invoice->received_amount + $row['amount'];
                $invoice->pending_amount = max(0, $invoice->original_amount - $invoice->received_amount);
                $invoice->status = $invoice->pending_amount <= 0 ? 'Paid' : ($invoice->received_amount > 0 ? 'Partial' : 'Pending');
                $invoice->save();

                // Only create Transaction for Cash receipts (cash deposit to bank)
                // Bank/GP receipts are direct bank payments - no cash transaction needed
                if ($row['mode'] === 'Cash') {
                    Transaction::create([
                        'user_id' => auth()->id(),
                        'type' => 'Receipt',
                        'mode' => $row['mode'],
                        'amount' => $row['amount'],
                        'boat_id' => $invoice->boat_id,
                        'landing_id' => $invoice->landing_id,
                        'buyer_id' => $invoice->buyer_id,
                        'invoice_id' => $invoice->id,
                        'transactionable_type' => Receipt::class,
                        'transactionable_id' => $receipt->id,
                        'date' => $row['date'],
                    ]);
                }

                $created++;
            }
        });

        $request->session()->forget('receipt_import_data');

        $message = "Imported {$created} receipts.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped}.";
        }

        return redirect()->route('receipts.index')
            ->with('success', $message);
    }
}
