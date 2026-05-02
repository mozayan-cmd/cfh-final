<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuyerRequest;
use App\Http\Requests\UpdateBuyerRequest;
use App\Models\Buyer;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerController extends Controller
{
    public function index(Request $request): View
    {
        // Get aggregated invoice data using database queries (matches InvoiceController approach)
        $invoiceStats = Invoice::where('user_id', auth()->id())
            ->select('buyer_id')
            ->selectRaw('SUM(original_amount) as total_purchased')
            ->selectRaw('SUM(received_amount) as total_received')
            ->selectRaw('SUM(pending_amount) as total_pending')
            ->selectRaw('COUNT(CASE WHEN status != "Paid" THEN 1 END) as unpaid_invoice_count')
            ->groupBy('buyer_id')
            ->get()
            ->keyBy('buyer_id');

        // Get all buyers and map invoice stats to them
        $buyers = Buyer::where('user_id', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(function ($buyer) use ($invoiceStats) {
                $stats = $invoiceStats->get($buyer->id) ?? (object)[
                    'total_purchased' => 0,
                    'total_received' => 0,
                    'total_pending' => 0,
                    'unpaid_invoice_count' => 0,
                ];

                $buyer->total_purchased = $stats->total_purchased ?? 0;
                $buyer->total_received = $stats->total_received ?? 0;
                $buyer->total_pending = $stats->total_pending ?? 0;
                $buyer->unpaid_invoice_count = $stats->unpaid_invoice_count ?? 0;

                return $buyer;
            });

        if ($request->filled('filter')) {
            if ($request->filter === 'pending') {
                $buyers = $buyers->filter(function ($buyer) {
                    return $buyer->total_pending > 0;
                });
            } elseif ($request->filter === 'no_pending') {
                $buyers = $buyers->filter(function ($buyer) {
                    return $buyer->total_pending == 0;
                });
            }
        }

        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('direction', 'asc');

        $buyers = $buyers->sortBy(function ($buyer) use ($sortBy) {
            switch ($sortBy) {
                case 'pending':
                    return $buyer->total_pending;
                case 'purchased':
                    return $buyer->total_purchased;
                case 'received':
                    return $buyer->total_received;
                case 'invoices':
                    return $buyer->unpaid_invoice_count;
                default:
                    return strtolower($buyer->name);
            }
        }, SORT_REGULAR, $sortDir === 'desc');

        $totalPurchased = $buyers->sum('total_purchased');
        $totalReceived = $buyers->sum('total_received');
        $totalPending = $buyers->sum('total_pending');

        return view('buyers.index', compact('buyers', 'totalPurchased', 'totalReceived', 'totalPending'));
    }

    public function store(StoreBuyerRequest $request): RedirectResponse
    {
        Buyer::create(array_merge($request->validated(), ['user_id' => auth()->id()]));

        return redirect()->route('buyers.index')->with('success', 'Buyer created successfully.');
    }

    public function show(Buyer $buyer): View
    {
        if ($buyer->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $buyer->load(['invoices.boat', 'invoices.landing', 'receipts.invoice']);

        $invoices = $buyer->invoices->map(function ($invoice) {
            $invoice->linked_landings = $invoice->landing ? [$invoice->landing] : [];

            return $invoice;
        });

        // Use database aggregation for accurate totals (not in-memory sums)
        $totals = Invoice::where('user_id', auth()->id())
            ->where('buyer_id', $buyer->id)
            ->selectRaw('SUM(original_amount) as total_purchased')
            ->selectRaw('SUM(received_amount) as total_received')
            ->selectRaw('SUM(pending_amount) as total_pending')
            ->first();

        return view('buyers.show', compact('buyer', 'invoices', 'totals'));
    }

    public function update(UpdateBuyerRequest $request, Buyer $buyer): RedirectResponse
    {
        $buyer->update($request->validated());

        return redirect()->route('buyers.show', $buyer)->with('success', 'Buyer updated successfully.');
    }

    public function destroy(Buyer $buyer): RedirectResponse
    {
        if ($buyer->invoices()->exists()) {
            return redirect()->route('buyers.index')
                ->with('error', 'Cannot delete buyer with existing invoices. Please delete or reassign invoices first.');
        }

        if ($buyer->receipts()->exists()) {
            return redirect()->route('buyers.index')
                ->with('error', 'Cannot delete buyer with existing receipts. Please delete receipts first.');
        }

        $buyer->delete();

        return redirect()->route('buyers.index')->with('success', 'Buyer deleted successfully.');
    }
}
