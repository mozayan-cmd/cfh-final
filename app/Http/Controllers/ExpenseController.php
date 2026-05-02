<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Boat;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Landing;
use App\Services\ExpenseSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    protected ExpenseSettlementService $settlementService;

    public function __construct(ExpenseSettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }

    public function index(Request $request): View
    {
        $query = Expense::with(['boat', 'landing', 'paymentAllocations.payment'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc');

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Vendor status filter: 'paid' = fully paid vendors, 'pending' = vendors with pending payments
        if ($request->filled('vendor_status')) {
            if ($request->vendor_status === 'paid') {
                // Get vendors with total pending = 0
                $paidVendorNames = Expense::where('user_id', auth()->id())
                    ->groupBy('vendor_name')
                    ->selectRaw('vendor_name')
                    ->havingRaw('SUM(pending_amount) = 0')
                    ->pluck('vendor_name')
                    ->toArray();
                $query->whereIn('vendor_name', $paidVendorNames);
            } elseif ($request->vendor_status === 'pending') {
                // Get vendors with pending amount > 0
                $pendingVendorNames = Expense::where('user_id', auth()->id())
                    ->groupBy('vendor_name')
                    ->selectRaw('vendor_name')
                    ->havingRaw('SUM(pending_amount) > 0')
                    ->pluck('vendor_name')
                    ->toArray();
                $query->whereIn('vendor_name', $pendingVendorNames);
            }
        }

        $expenses = $query->get();
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();
        $types = ExpenseType::orderBy('name')->get();

        $totalPending = $expenses->where('payment_status', '!=', 'Paid')->sum('pending_amount');
        $totalAmount = $expenses->sum('amount');

        return view('expenses.index', compact('expenses', 'boats', 'landings', 'types', 'totalPending', 'totalAmount'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Validate boat existence (only if provided)
        if (! empty($data['boat_id']) && ! Boat::where('id', $data['boat_id'])->exists()) {
            return redirect()->back()->with('error', 'Invalid boat selected.')->withInput();
        }

        // Resolve landing_id: allow null, an existing id, or the special string 'next'
        if (empty($data['landing_id'])) {
            $data['landing_id'] = null;
        } elseif ($data['landing_id'] === 'next' && ! empty($data['boat_id'])) {
            // Find the next future landing for this boat
            $nextLanding = Landing::where('boat_id', $data['boat_id'])
                ->where('date', '>', now())
                ->orderBy('date')
                ->first();

            $data['landing_id'] = $nextLanding ? $nextLanding->id : null;

            if (is_null($data['landing_id'])) {
                // No future landing – keep expense unassigned and inform the user
                session()->flash('warning', 'No upcoming landing found for the selected boat; expense will remain unassigned.');
            }
        } elseif (! empty($data['landing_id'])) {
            // Ensure the provided landing_id actually exists for this boat
            if (! Landing::where('id', $data['landing_id'])->where('boat_id', $data['boat_id'])->exists()) {
                return redirect()->back()->with('error', 'Invalid landing selected.')->withInput();
            }
        } else {
            $data['landing_id'] = null;
        }

        $data['paid_amount'] = 0;
        $data['pending_amount'] = $data['amount'];
        $data['payment_status'] = 'Pending';
        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->back()->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense): View
    {
        if ($expense->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $expense->load(['boat', 'landing', 'paymentAllocations.payment']);

        return view('expenses.show', compact('expense'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['boat_id']) && ! empty($data['boat_id']) && ! Boat::where('id', $data['boat_id'])->exists()) {
            return redirect()->back()->with('error', 'Invalid boat selected.')->withInput();
        }

        if (isset($data['landing_id']) && ! empty($data['landing_id']) && ! Landing::where('id', $data['landing_id'])->exists()) {
            return redirect()->back()->with('error', 'Invalid landing selected.')->withInput();
        }

        if (empty($data['boat_id'])) {
            $data['boat_id'] = null;
        }

        if (empty($data['landing_id'])) {
            $data['landing_id'] = null;
        }

        $data['pending_amount'] = $data['amount'] - $expense->paid_amount;

        if ($data['pending_amount'] < 0) {
            $data['pending_amount'] = 0;
            $data['payment_status'] = 'Paid';
        } elseif ($data['pending_amount'] <= 0) {
            $data['payment_status'] = 'Paid';
        } elseif ($expense->paid_amount > 0) {
            $data['payment_status'] = 'Partial';
        } else {
            $data['payment_status'] = 'Pending';
        }

        $expense->update($data);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->paymentAllocations()->exists()) {
            return redirect()->route('expenses.index')
                ->with('error', 'Cannot delete expense with existing payment allocations. Please delete allocations first.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function storeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_types,name',
        ]);

        ExpenseType::create($validated);

        return redirect()->back()->with('success', 'Expense type added successfully.');
    }

    public function export(Request $request)
    {
        $query = Expense::with(['boat', 'landing']);

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('landing_id')) {
            $query->where('landing_id', $request->landing_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        $lines = [];
        $lines[] = 'ID,Date,Boat,Landing Date,Type,Vendor Name,Amount,Paid Amount,Pending Amount,Status,Notes';

        foreach ($expenses as $expense) {
            $lines[] = sprintf('%d,%s,%s,%s,%s,"%s",%s,%s,%s,%s,"%s"',
                $expense->id,
                $expense->date->format('Y-m-d'),
                $expense->boat->name ?? '',
                $expense->landing ? $expense->landing->date->format('Y-m-d') : '',
                $expense->type,
                $expense->vendor_name,
                number_format($expense->amount, 2),
                number_format($expense->paid_amount, 2),
                number_format($expense->pending_amount, 2),
                $expense->payment_status,
                $expense->notes ?? ''
            );
        }

        $content = implode("\n", $lines);
        $filename = 'expenses_export_'.date('Y-m-d_His').'.csv';

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(): View
    {
        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $landings = Landing::where('user_id', auth()->id())->with('boat')->orderBy('date', 'desc')->get();
        $types = ExpenseType::orderBy('name')->get();

        return view('expenses.import', compact('boats', 'landings', 'types'));
    }

    public function previewImport(Request $request)
    {
        $pasteMode = $request->filled('paste_mode') && $request->paste_mode == '1';

        $boatId = $request->input('boat_id');
        $landingId = $request->input('landing_id');
        $type = $request->input('type');
        $expenseDate = $request->input('date', date('Y-m-d'));

        if ($pasteMode) {
            $validated = $request->validate([
                'boat_id' => 'required|exists:boats,id',
                'date' => 'required|date',
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

                $parts = str_getcsv($line);
                if (count($parts) < 4) {
                    $errors[] = 'Line '.($lineNum + 1).': Invalid format (need Date|Type|Vendor|Amount)';

                    continue;
                }

                $date = trim($parts[0]);
                $type = trim($parts[1]);
                $vendorName = trim($parts[2]);
                $amount = trim($parts[3]);

                if (empty($date) || ! strtotime($date)) {
                    $errors[] = 'Line '.($lineNum + 1).': Invalid date';

                    continue;
                }

                if (empty($vendorName)) {
                    $errors[] = 'Line '.($lineNum + 1).': Empty vendor name';

                    continue;
                }

                $amount = str_replace(',', '', $amount);
                if (! is_numeric($amount)) {
                    $errors[] = 'Line '.($lineNum + 1).": Invalid amount '{$amount}'";

                    continue;
                }

                $results[] = [
                    'line' => $lineNum + 1,
                    'date' => $date,
                    'type' => $type ?: 'Other',
                    'vendor_name' => $vendorName,
                    'amount' => (float) $amount,
                ];
            }
        } else {
            $validated = $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');

            $results = [];
            $errors = [];
            $lineNumber = 0;

            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if ($lineNumber == 1 && strtolower(trim($data[0] ?? '')) === 'id') {
                    continue;
                }

                $date = trim($data[1] ?? '');
                $type = trim($data[4] ?? '');
                $vendorName = trim($data[5] ?? '');
                $amount = trim($data[6] ?? '');

                if (empty($date) || ! strtotime($date)) {
                    $errors[] = "Line {$lineNumber}: Invalid date";

                    continue;
                }

                if (empty($vendorName)) {
                    $errors[] = "Line {$lineNumber}: Empty vendor name";

                    continue;
                }

                $amount = str_replace(',', '', $amount);
                if (empty($amount) || ! is_numeric($amount)) {
                    $errors[] = "Line {$lineNumber}: Invalid amount '{$amount}'";

                    continue;
                }

                $amount = (float) $amount;

                if ($amount <= 0) {
                    $errors[] = "Line {$lineNumber}: Amount must be greater than 0";

                    continue;
                }

                $results[] = [
                    'line' => $lineNumber,
                    'date' => $date,
                    'type' => $type ?: 'Other',
                    'vendor_name' => $vendorName,
                    'amount' => $amount,
                ];
            }
            fclose($handle);
        }

        if (empty($results) && ! empty($errors)) {
            return redirect()->back()->with('error', 'No valid rows found. '.implode(', ', $errors));
        }

        $request->session()->put('expense_import_data', [
            'results' => $results,
            'boat_id' => $boatId,
            'landing_id' => $landingId,
        ]);

        return view('expenses.import-preview', [
            'results' => $results,
            'errors' => $errors,
        ]);
    }

    public function processImport(Request $request)
    {
        $importData = $request->session()->get('expense_import_data', []);

        if (empty($importData)) {
            return redirect()->route('expenses.index')
                ->with('error', 'No data to import.');
        }

        $results = $importData['results'];
        $boatId = $importData['boat_id'];
        $landingId = $importData['landing_id'];

        $created = 0;
        $skipped = 0;

        $validRows = [];
        foreach ($results as $row) {
            if (empty($row['date']) || empty($row['amount'])) {
                $skipped++;

                continue;
            }

            $validRows[] = $row;
        }

        foreach ($validRows as $row) {
            Expense::create([
                'user_id' => auth()->id(),
                'boat_id' => $boatId,
                'landing_id' => $landingId,
                'date' => $row['date'],
                'type' => $row['type'] ?? 'Other',
                'vendor_name' => $row['vendor_name'],
                'amount' => $row['amount'],
                'paid_amount' => 0,
                'pending_amount' => $row['amount'],
                'payment_status' => 'Pending',
            ]);
            $created++;
        }

        $request->session()->forget('expense_import_data');

        return redirect()->route('expenses.index')
            ->with('success', "Imported {$created} expenses.");
    }
}
