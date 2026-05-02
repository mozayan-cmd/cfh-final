<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanSource;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\LoanTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    protected LoanTrackingService $loanService;

    public function __construct(LoanTrackingService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function index(): View
    {
        $query = Loan::where('user_id', auth()->id())->orderBy('date', 'desc');

        $loans = $query->get();

        $loansBySource = [];
        foreach (LoanTrackingService::SOURCES as $source) {
            $loansBySource[$source] = $loans->where('source', $source)->values();
        }

        $balances = [];
        foreach (LoanTrackingService::SOURCES as $source) {
            $balances[$source] = $loans->where('source', $source)->sum('amount') - $loans->where('source', $source)->sum('repaid_amount');
        }
        $balances['total'] = array_sum($balances);

        $totalOutstanding = $loans->whereNull('repaid_at')->sum('amount') - $loans->whereNull('repaid_at')->sum('repaid_amount');
        $loanSources = LoanSource::orderBy('name')->get();

        return view('loans.index', compact(
            'loansBySource',
            'balances',
            'totalOutstanding',
            'loanSources'
        ));
    }

    public function create(): View
    {
        $loanSources = LoanSource::orderBy('name')->get();

        return view('loans.create', compact('loanSources'));
    }

    public function store(Request $request): RedirectResponse
    {
        $sourceNames = LoanSource::pluck('name')->toArray();

        $validated = $request->validate([
            'source' => 'required|in:'.implode(',', $sourceNames),
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'mode' => 'required|in:Cash,GP,Bank',
            'notes' => 'nullable|string',
        ]);

        $loan = Loan::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'Receipt',
            'mode' => $validated['mode'],
            'source' => $validated['source'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => 'Loan taken: '.($validated['notes'] ?? "Loan from {$validated['source']}"),
            'transactionable_type' => Loan::class,
            'transactionable_id' => $loan->id,
        ]);

        return redirect()->route('loans.index')
            ->with('success', "Loan of Rs. {$validated['amount']} recorded from {$validated['source']} via {$validated['mode']}");
    }

    public function storeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:loan_sources,name',
        ]);

        LoanSource::create($validated);

        return redirect()->back()->with('success', "Loan source '{$validated['name']}' added.");
    }

    public function repay(Request $request, Loan $loan): RedirectResponse
    {
        $sourceNames = LoanSource::pluck('name')->toArray();
        $outstandingAmount = $loan->amount - ($loan->repaid_amount ?? 0);

        $validated = $request->validate([
            'mode' => 'required|in:Cash,GP,Bank',
            'amount' => 'required|numeric|min:0.01|max:'.$outstandingAmount,
            'date' => 'required|date',
            'payment_for' => 'required|in:'.implode(',', $sourceNames),
        ], [
            'amount.max' => 'Repayment cannot exceed outstanding balance of Rs. '.number_format($outstandingAmount, 2),
        ]);

        $validated['notes'] = "Loan repayment for loan #{$loan->id}";
        $validated['loan_id'] = $loan->id;
        $validated['source'] = $validated['payment_for'];

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'mode' => $validated['mode'],
            'source' => $validated['source'],
            'payment_for' => $validated['payment_for'],
            'notes' => $validated['notes'],
        ]);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'Payment',
            'mode' => $validated['mode'],
            'source' => $validated['source'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'],
            'transactionable_type' => Payment::class,
            'transactionable_id' => $payment->id,
        ]);

        $newRepaid = $loan->repaid_amount + $validated['amount'];
        $loan->update(['repaid_amount' => $newRepaid]);

        if ($newRepaid >= $loan->amount) {
            $loan->update(['repaid_at' => now()]);
        }

        return redirect()->route('loans.index')
            ->with('success', "Rs. {$validated['amount']} repaid. Cash/Bank updated.");
    }
}
