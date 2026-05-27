<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\ListTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripController extends Controller
{
    public function index()
    {
        $trips = auth()->user()->trips()
            ->withCount('packingLists')
            ->with(['budget', 'groups'])
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('Trips/Index', ['trips' => $trips]);
    }

    public function create() { return Inertia::render('Trips/Create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'cover_emoji' => 'nullable|string|max:10',
            'notes'       => 'nullable|string',
            'status'      => 'nullable|in:planning,confirmed,ongoing,completed',
        ]);
        $trip = auth()->user()->trips()->create($data);
        $trip->budget()->create(['currency' => 'EUR']);
        return redirect()->route('trips.show', $trip);
    }

    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);
        $trip->load([
            'groups.members.packingLists.items',
            'groups.members.memberBudget',
            'itineraryDays',
            'budget',
        ]);
        $lists = $trip->packingLists()
            ->with(['items', 'member', 'group'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();
        return Inertia::render('Trips/Show', [
            'lists' => $lists,'trip' => $trip]);
    }

    public function edit(Trip $trip)
    {
        $this->authorize('update', $trip);
        return Inertia::render('Trips/Edit', ['trip' => $trip]);
    }

    public function update(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'destination' => 'nullable|string|max:255',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'cover_emoji' => 'nullable|string|max:10',
            'notes'       => 'nullable|string',
            'status'      => 'nullable|in:planning,confirmed,ongoing,completed',
        ]);
        $trip->update($data);
        return redirect()->route('trips.show', $trip);
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();
        return redirect()->route('trips.index');
    }
}
