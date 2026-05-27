<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\BudgetIncome;
use App\Models\BudgetExpense;
use App\Models\BudgetActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);

        $budget = $trip->budget()->with(['incomes', 'expenses', 'activities'])->firstOrCreate(
            ['trip_id' => $trip->id], ['currency' => 'EUR']
        );

        $trip->load('groups.members.memberBudget');

        $expensesByCategory = $budget->expenses()
            ->where('type', 'actual')->get()
            ->groupBy('category')->map(fn($g) => $g->sum('amount'));

        return Inertia::render('Budget/Show', [
            'trip'               => $trip,
            'budget'             => $budget,
            'expensesByCategory' => $expensesByCategory,
        ]);
    }

    public function update(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $trip->budget->update($request->validate(['currency' => 'nullable|string|size:3', 'total_target' => 'nullable|numeric|min:0']));
        return back();
    }

    public function storeIncome(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $trip->budget->incomes()->create($request->validate(['label'=>'required|string|max:255','amount'=>'required|numeric|min:0','date'=>'nullable|date','notes'=>'nullable|string']));
        return back();
    }

    public function updateIncome(Request $request, Trip $trip, BudgetIncome $income)
    {
        $this->authorize('update', $trip);
        $income->update($request->validate(['label'=>'required|string|max:255','amount'=>'required|numeric|min:0','date'=>'nullable|date','notes'=>'nullable|string']));
        return back();
    }

    public function destroyIncome(Trip $trip, BudgetIncome $income)
    {
        $this->authorize('update', $trip);
        $income->delete();
        return back();
    }

    public function storeExpense(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $trip->budget->expenses()->create($request->validate(['label'=>'required|string|max:255','category'=>'required|string','amount'=>'required|numeric|min:0','date'=>'nullable|date','type'=>'required|in:actual,planned','notes'=>'nullable|string']));
        return back();
    }

    public function updateExpense(Request $request, Trip $trip, BudgetExpense $expense)
    {
        $this->authorize('update', $trip);
        $expense->update($request->validate(['label'=>'required|string|max:255','category'=>'required|string','amount'=>'required|numeric|min:0','date'=>'nullable|date','type'=>'required|in:actual,planned','notes'=>'nullable|string']));
        return back();
    }

    public function destroyExpense(Trip $trip, BudgetExpense $expense)
    {
        $this->authorize('update', $trip);
        $expense->delete();
        return back();
    }

    public function storeActivity(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $trip->budget->activities()->create($request->validate(['name'=>'required|string|max:255','category'=>'nullable|string','price_per_person'=>'required|numeric|min:0','persons'=>'required|integer|min:1','is_planned'=>'boolean','is_paid'=>'boolean','date'=>'nullable|date','notes'=>'nullable|string']));
        return back();
    }

    public function updateActivity(Request $request, Trip $trip, BudgetActivity $activity)
    {
        $this->authorize('update', $trip);
        $activity->update($request->validate(['name'=>'required|string|max:255','category'=>'nullable|string','price_per_person'=>'required|numeric|min:0','persons'=>'required|integer|min:1','is_planned'=>'boolean','is_paid'=>'boolean','date'=>'nullable|date','notes'=>'nullable|string']));
        return back();
    }

    public function destroyActivity(Trip $trip, BudgetActivity $activity)
    {
        $this->authorize('update', $trip);
        $activity->delete();
        return back();
    }
}
