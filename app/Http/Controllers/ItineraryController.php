<?php
namespace App\Http\Controllers;
use App\Models\Trip;
use App\Models\ItineraryDay;
use App\Models\ItineraryEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItineraryController extends Controller
{
    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);
        $trip->load('itineraryDays.events');
        return Inertia::render('Trips/Itinerary', ['trip' => $trip]);
    }

    public function generateDays(Trip $trip)
    {
        $this->authorize('update', $trip);
        if (!$trip->start_date || !$trip->end_date) {
            return back()->withErrors(['dates' => 'Dates manquantes.']);
        }
        $existingDates = $trip->itineraryDays()->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $current = $trip->start_date->copy();
        $order = 0;
        while ($current->lte($trip->end_date)) {
            $dateStr = $current->format('Y-m-d');
            if (!in_array($dateStr, $existingDates)) {
                $trip->itineraryDays()->create(['date' => $dateStr, 'sort_order' => $order]);
            }
            $current->addDay(); $order++;
        }
        return back()->with('success', 'Jours générés.');
    }

    public function storeDay(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $trip->itineraryDays()->create($request->validate([
            'date' => 'required|date', 'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string', 'sort_order' => 'nullable|integer',
        ]));
        return back();
    }

    public function updateDay(Request $request, Trip $trip, ItineraryDay $day)
    {
        $this->authorize('update', $trip);
        $day->update($request->validate([
            'title' => 'nullable|string|max:255', 'notes' => 'nullable|string', 'sort_order' => 'nullable|integer',
        ]));
        return back();
    }

    public function destroyDay(Trip $trip, ItineraryDay $day)
    {
        $this->authorize('update', $trip); $day->delete(); return back();
    }

    public function storeEvent(Request $request, Trip $trip, ItineraryDay $day)
    {
        $this->authorize('update', $trip);
        $day->events()->create($request->validate([
            'title'          => 'required|string|max:255',
            'location'       => 'nullable|string|max:255',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
            'place_id'       => 'nullable|string',
            'time_start'     => 'nullable|date_format:H:i',
            'time_end'       => 'nullable|date_format:H:i',
            'type'           => 'nullable|in:activity,transport,accommodation,food,rest,other',
            'icon'           => 'nullable|string|max:10',
            'notes'          => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'toll_cost'      => 'nullable|numeric|min:0',
            'travel_minutes' => 'nullable|integer|min:0',
            'rest_minutes'   => 'nullable|integer|min:0',
            'places_to_visit'=> 'nullable|array',
            'travel_notes'   => 'nullable|string',
        ]));
        return back();
    }

    public function updateEvent(Request $request, Trip $trip, ItineraryDay $day, ItineraryEvent $event)
    {
        $this->authorize('update', $trip);
        $event->update($request->validate([
            'title'          => 'required|string|max:255',
            'location'       => 'nullable|string|max:255',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
            'place_id'       => 'nullable|string',
            'time_start'     => 'nullable|date_format:H:i',
            'time_end'       => 'nullable|date_format:H:i',
            'type'           => 'nullable|in:activity,transport,accommodation,food,rest,other',
            'icon'           => 'nullable|string|max:10',
            'notes'          => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'toll_cost'      => 'nullable|numeric|min:0',
            'travel_minutes' => 'nullable|integer|min:0',
            'rest_minutes'   => 'nullable|integer|min:0',
            'places_to_visit'=> 'nullable|array',
            'travel_notes'   => 'nullable|string',
        ]));
        return back();
    }

    public function destroyEvent(Trip $trip, ItineraryDay $day, ItineraryEvent $event)
    {
        $this->authorize('update', $trip); $event->delete(); return back();
    }
}
