<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Expense;
use App\Models\Landing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnlinkedExpenseController extends Controller
{
    public function index(): View
    {
        $unlinkedExpenses = Expense::whereNull('landing_id')
            ->with('boat')
            ->orderBy('date', 'desc')
            ->get();

        $groupedExpenses = $unlinkedExpenses->groupBy('boat_id');

        return view('unlinked-expenses.index', compact('unlinkedExpenses', 'groupedExpenses'));
    }

    public function edit(Expense $expense): View
    {
        if ($expense->landing_id !== null) {
            return redirect()->route('unlinked-expenses.index')
                ->with('error', 'This expense is already linked to a landing.');
        }

        $boats = Boat::where('user_id', auth()->id())->with('landings')->get();
        $landings = Landing::where('boat_id', $expense->boat_id)
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->get();

        return view('unlinked-expenses.edit', compact('expense', 'boats', 'landings'));
    }

    public function show(Expense $expense): View
    {
        if ($expense->landing_id !== null) {
            return redirect()->route('unlinked-expenses.index')
                ->with('error', 'This expense is already linked to a landing.');
        }

        $expense->load(['boat', 'paymentAllocations.payment']);

        return view('unlinked-expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->landing_id !== null) {
            return redirect()->route('unlinked-expenses.index')
                ->with('error', 'Cannot delete an expense that is linked to a landing. Unlink it first.');
        }

        if ($expense->paymentAllocations()->exists()) {
            return redirect()->route('unlinked-expenses.index')
                ->with('error', 'Cannot delete expense with existing payment allocations. Please delete allocations first.');
        }

        $expense->delete();

        return redirect()->route('unlinked-expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->landing_id !== null) {
            return redirect()->route('unlinked-expenses.index')
                ->with('error', 'This expense is already linked to a landing.');
        }

        $request->validate([
            'landing_id' => 'required_without:keep_unlinked|nullable|exists:landings,id',
            'keep_unlinked' => 'required_without:landing_id|boolean',
        ]);

        if ($request->boolean('keep_unlinked')) {
            return redirect()->route('unlinked-expenses.index')
                ->with('success', 'Expense kept as unlinked.');
        }

        $landing = Landing::findOrFail($request->landing_id);

        if ($landing->boat_id !== $expense->boat_id) {
            return redirect()->back()
                ->with('error', 'The selected landing does not belong to the same boat.');
        }

        $expense->update(['landing_id' => $landing->id]);

        return redirect()->route('unlinked-expenses.index')
            ->with('success', 'Expense linked to landing successfully.');
    }
}
