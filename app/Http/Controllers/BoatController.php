<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoatRequest;
use App\Http\Requests\UpdateBoatRequest;
use App\Models\Boat;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BoatController extends Controller
{
    public function index(): View
    {
        $boats = Boat::withCount('landings')
            ->with('landings.expenses', 'landings.payments')
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(function ($boat) {
                $boat->total_landings = $boat->landings_count;
                $boat->latest_landing_date = $boat->landings->max('date') ?? null;
                $boat->pending_settlement = $boat->landings->sum(function ($landing) {
                    $totalExpenses = $landing->expenses ? $landing->expenses->sum('amount') : 0;
                    $ownerPaid = $landing->payments ? $landing->payments->where('payment_for', '!=', 'Expense')->sum('amount') : 0;

                    return max(0, $landing->gross_value - $totalExpenses - $ownerPaid);
                });

                return $boat;
            });

        return view('boats.index', compact('boats'));
    }

    public function store(StoreBoatRequest $request): RedirectResponse
    {
        Boat::create(array_merge($request->validated(), ['user_id' => auth()->id()]));

        return redirect()->route('boats.index')->with('success', 'Boat created successfully.');
    }

    public function show(Boat $boat): View
    {
        if ($boat->user_id !== auth()->id()) {
            abort(403, 'Access denied.');
        }

        $boat->load(['landings.expenses', 'landings.payments', 'landings.invoices']);

        $landings = $boat->landings->map(function ($landing) {
            $landing->total_expenses = $landing->expenses ? $landing->expenses->sum('amount') : 0;
            $landing->owner_paid = $landing->payments ? $landing->payments->where('payment_for', '!=', 'Expense')->sum('amount') : 0;
            $landing->owner_pending = max(0, $landing->gross_value - $landing->total_expenses - $landing->owner_paid);

            return $landing;
        });

        return view('boats.show', compact('boat', 'landings'));
    }

    public function update(UpdateBoatRequest $request, Boat $boat): RedirectResponse
    {
        $boat->update($request->validated());

        return redirect()->route('boats.show', $boat)->with('success', 'Boat updated successfully.');
    }

    public function destroy(Boat $boat): RedirectResponse
    {
        if ($boat->landings()->exists()) {
            return redirect()->route('boats.index')
                ->with('error', 'Cannot delete boat with existing landings. Please delete landings first.');
        }

        if ($boat->invoices()->exists()) {
            return redirect()->route('boats.index')
                ->with('error', 'Cannot delete boat with existing invoices. Please delete invoices first.');
        }

        if ($boat->expenses()->exists()) {
            return redirect()->route('boats.index')
                ->with('error', 'Cannot delete boat with existing expenses. Please delete expenses first.');
        }

        if ($boat->payments()->exists()) {
            return redirect()->route('boats.index')
                ->with('error', 'Cannot delete boat with existing payments. Please delete payments first.');
        }

        $boat->delete();

        return redirect()->route('boats.index')->with('success', 'Boat deleted successfully.');
    }
}
