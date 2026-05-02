<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLandingRequest;
use App\Http\Requests\UpdateLandingRequest;
use App\Models\Boat;
use App\Models\Expense;
use App\Models\Landing;
use App\Services\LandingSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    protected LandingSummaryService $summaryService;

    public function __construct(LandingSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(Request $request): View
    {
        $query = Landing::with('boat')->where('user_id', auth()->id())->orderBy('date', 'desc');

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $landings = $query->get()->map(function ($landing) {
            $landing->summary = $this->summaryService->getSummary($landing);

            return $landing;
        });

        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $unlinkedExpenses = Expense::whereNull('landing_id')
            ->where('user_id', auth()->id())
            ->with('boat')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('boat_id');

        return view('landings.index', compact('landings', 'boats', 'unlinkedExpenses'));
    }

    public function store(StoreLandingRequest $request): RedirectResponse
    {
        $data = array_merge($request->validated(), ['user_id' => auth()->id()]);
        $landing = Landing::create($data);

        $expenseIds = $request->input('expense_ids', []);
        if (! is_array($expenseIds) && ! empty($expenseIds)) {
            $expenseIds = array_filter(explode(',', $expenseIds));
        }

        if (! empty($expenseIds)) {
            Expense::whereIn('id', $expenseIds)
                ->where('user_id', auth()->id())
                ->whereNull('landing_id')
                ->update(['landing_id' => $landing->id]);
        }

        $this->summaryService->updateLandingStatus($landing);

        return redirect()->route('landings.show', $landing)->with('success', 'Landing created successfully.');
    }

    public function show(Landing $landing): View
    {
        if ($landing->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $landing->load(['boat', 'expenses', 'invoices.buyer', 'payments', 'receipts.buyer']);

        $summary = $this->summaryService->getSummary($landing);

        $ownerPayments = $landing->payments->where('payment_for', '!=', 'Expense');
        $cashReceipts = $landing->receipts->where('mode', 'Cash');
        $bankReceipts = $landing->receipts->whereIn('mode', ['Bank', 'GP']);
        $cashPayments = $ownerPayments->where('mode', 'Cash');
        $bankPayments = $ownerPayments->whereIn('mode', ['Bank', 'GP']);

        $receiptSummary = [
            'cash' => $cashReceipts->sum('amount'),
            'bank' => $bankReceipts->sum('amount'),
            'total' => $cashReceipts->sum('amount') + $bankReceipts->sum('amount'),
        ];

        $paymentSummary = [
            'cash' => $cashPayments->sum('amount'),
            'bank' => $bankPayments->sum('amount'),
            'total' => $cashPayments->sum('amount') + $bankPayments->sum('amount'),
        ];

        $unassignedExpenses = Expense::where('boat_id', $landing->boat_id)
            ->where('user_id', auth()->id())
            ->whereNull('landing_id')
            ->orderBy('date', 'desc')
            ->get();

        $boats = Boat::where('user_id', auth()->id())->orderBy('name')->get();
        $expenseTypes = ['Ice', 'Diesel', 'Food', 'Labour', 'Loading', 'Harbour', 'Repair', 'Equipment', 'Insurance', 'License', 'Other'];

        return view('landings.show', compact(
            'landing', 'summary', 'unassignedExpenses', 'boats', 'expenseTypes',
            'receiptSummary', 'paymentSummary', 'ownerPayments'
        ));
    }

    public function update(UpdateLandingRequest $request, Landing $landing): RedirectResponse
    {
        $landing->update($request->validated());
        $this->summaryService->updateLandingStatus($landing);

        return redirect()->route('landings.show', $landing)->with('success', 'Landing updated successfully.');
    }

    public function attachExpenses(Request $request, Landing $landing): RedirectResponse
    {
        $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'required|integer|exists:expenses,id',
        ]);

        $expenseIds = $request->input('expense_ids', []);
        if (! is_array($expenseIds) && ! empty($expenseIds)) {
            $expenseIds = array_filter(explode(',', $expenseIds));
        }

        Expense::whereIn('id', $expenseIds)
            ->whereNull('landing_id')
            ->update(['landing_id' => $landing->id]);

        $this->summaryService->updateLandingStatus($landing);

        return redirect()->route('landings.show', $landing)->with('success', 'Expenses attached successfully.');
    }

    public function destroy(Landing $landing): RedirectResponse
    {
        if ($landing->expenses()->exists()) {
            return redirect()->route('landings.index')
                ->with('error', 'Cannot delete landing with attached expenses. Please detach expenses first.');
        }

        if ($landing->invoices()->exists()) {
            return redirect()->route('landings.index')
                ->with('error', 'Cannot delete landing with existing invoices. Please delete invoices first.');
        }

        if ($landing->payments()->exists()) {
            return redirect()->route('landings.index')
                ->with('error', 'Cannot delete landing with existing payments. Please delete payments first.');
        }

        $landing->delete();

        return redirect()->route('landings.index')->with('success', 'Landing deleted successfully.');
    }
}
