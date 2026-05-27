<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripGroup;
use App\Models\TripMember;
use App\Models\PackingList;
use App\Models\ListTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripGroupController extends Controller
{
    public function index(Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load([
            'groups.members.packingLists.items',
            'groups.members.memberBudget',
            'groups.members.activities',
            'budget',
        ]);

        $templates = auth()->user()->listTemplates()->withCount('items')->get();

        return Inertia::render('Groups/Index', [
            'trip'      => $trip,
            'templates' => $templates,
        ]);
    }

    public function storeGroup(Request $request, Trip $trip)
    {
        $this->authorize('update', $trip);
        $data = $request->validate(['name' => 'required|string|max:255', 'icon' => 'nullable|string|max:10', 'notes' => 'nullable|string']);
        $trip->groups()->create($data);
        return back()->with('success', 'Groupe créé.');
    }

    public function updateGroup(Request $request, Trip $trip, TripGroup $group)
    {
        $this->authorize('update', $trip);
        $data = $request->validate(['name' => 'required|string|max:255', 'icon' => 'nullable|string|max:10', 'notes' => 'nullable|string']);
        $group->update($data);
        return back();
    }

    public function destroyGroup(Trip $trip, TripGroup $group)
    {
        $this->authorize('update', $trip);
        $group->delete();
        return back();
    }

    public function storeMember(Request $request, Trip $trip, TripGroup $group)
    {
        $this->authorize('update', $trip);
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'avatar_emoji' => 'nullable|string|max:10',
            'color'        => 'nullable|string|max:20',
            'role'         => 'nullable|in:adult,teen,child,baby',
            'age'          => 'nullable|integer|min:0|max:120',
        ]);
        $member = $group->members()->create($data);
        if ($trip->budget) {
            $trip->budget->memberBudgets()->firstOrCreate(
                ['trip_member_id' => $member->id],
                ['allocated_amount' => 0, 'personal_spending' => 0]
            );
        }
        return back()->with('success', 'Membre ajouté.');
    }

    public function updateMember(Request $request, Trip $trip, TripGroup $group, TripMember $member)
    {
        $this->authorize('update', $trip);
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'avatar_emoji' => 'nullable|string|max:10',
            'color'        => 'nullable|string|max:20',
            'role'         => 'nullable|in:adult,teen,child,baby',
            'age'          => 'nullable|integer|min:0|max:120',
        ]);
        $member->update($data);
        return back();
    }

    public function destroyMember(Trip $trip, TripGroup $group, TripMember $member)
    {
        $this->authorize('update', $trip);
        $member->delete();
        return back();
    }

    public function storeMemberList(Request $request, Trip $trip, TripGroup $group, TripMember $member)
    {
        $this->authorize('update', $trip);
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:packing,grocery,shopping,todo',
            'icon'             => 'nullable|string|max:10',
            'list_template_id' => 'nullable|exists:list_templates,id',
        ]);
        $list = PackingList::create(array_merge($data, [
            'user_id'        => auth()->id(),
            'trip_id'        => $trip->id,
            'trip_group_id'  => $group->id,
            'trip_member_id' => $member->id,
        ]));
        if (!empty($data['list_template_id'])) {
            $template = ListTemplate::with('items')->find($data['list_template_id']);
            if ($template) {
                foreach ($template->items as $item) {
                    $list->items()->create($item->only(['name','category','quantity','unit','unit_price','need_to_buy','sort_order']));
                }
            }
        }
        return back()->with('success', 'Liste créée.');
    }

    public function updateMemberBudget(Request $request, Trip $trip, TripMember $member)
    {
        $this->authorize('update', $trip);
        $data = $request->validate([
            'allocated_amount'  => 'required|numeric|min:0',
            'personal_spending' => 'nullable|numeric|min:0',
        ]);
        $trip->budget->memberBudgets()->updateOrCreate(
            ['trip_member_id' => $member->id],
            $data
        );
        return back();
    }
}
