<?php
namespace App\Http\Controllers;
use App\Models\PackingList;
use App\Models\PackingItem;
use App\Models\ListTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackingListController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $lists = $user->packingLists()->with(['items', 'trip'])->get();

        $trips = $user->trips()
            ->with(['groups.members.packingLists.items'])
            ->orderByDesc('start_date')
            ->get();

        // Attacher les listes directes du trip (sans membre) aux trips
        $trips = $trips->map(function ($trip) use ($lists) {
            $trip->direct_lists = $lists->where('trip_id', $trip->id)
                ->whereNull('trip_member_id')
                ->values();
            return $trip;
        });

        $templates = $user->listTemplates()->withCount('items')->get();

        return Inertia::render('Packing/Index', [
            'lists'     => $lists,
            'trips'     => $trips,
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:packing,grocery,shopping,todo',
            'icon'             => 'nullable|string|max:10',
            'trip_id'          => 'nullable|exists:trips,id',
            'list_template_id' => 'nullable|exists:list_templates,id',
        ]);
        $list = auth()->user()->packingLists()->create($data);
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

    public function show(PackingList $packingList)
    {
        $this->authorize('view', $packingList);
        $packingList->load('items');
        $templates = auth()->user()->listTemplates()->withCount('items')->get();
        return Inertia::render('Packing/Show', ['list' => $packingList, 'templates' => $templates]);
    }

    public function destroy(PackingList $packingList)
    {
        $this->authorize('delete', $packingList);
        $packingList->delete();
        return back()->with('success', 'Liste supprimée.');
    }

    public function storeItem(Request $request, PackingList $packingList)
    {
        $this->authorize('update', $packingList);
        $data = $request->validate(['name'=>'required|string|max:255','category'=>'nullable|string|max:100','quantity'=>'nullable|numeric|min:0','unit'=>'nullable|string|max:50','unit_price'=>'nullable|numeric|min:0','need_to_buy'=>'boolean','notes'=>'nullable|string']);
        $packingList->items()->create($data);
        return back()->with('success', 'Item ajouté.');
    }

    public function updateItem(Request $request, PackingList $packingList, PackingItem $item)
    {
        $this->authorize('update', $packingList);
        $data = $request->validate(['name'=>'sometimes|string|max:255','category'=>'nullable|string|max:100','quantity'=>'nullable|numeric|min:0','unit'=>'nullable|string|max:50','unit_price'=>'nullable|numeric|min:0','is_checked'=>'boolean','need_to_buy'=>'boolean','notes'=>'nullable|string']);
        $item->update($data);
        return back();
    }

    public function destroyItem(PackingList $packingList, PackingItem $item)
    {
        $this->authorize('update', $packingList);
        $item->delete();
        return back();
    }

    public function saveAsTemplate(Request $request, PackingList $packingList)
    {
        $this->authorize('view', $packingList);
        $data = $request->validate(['name' => 'required|string|max:255']);
        $template = auth()->user()->listTemplates()->create(['name'=>$data['name'],'type'=>$packingList->type,'icon'=>$packingList->icon,'is_public'=>false]);
        foreach ($packingList->items as $item) {
            $template->items()->create($item->only(['name','category','quantity','unit','unit_price','need_to_buy','sort_order']));
        }
        return back()->with('success', 'Template sauvegardé.');
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:255','type'=>'required|in:packing,grocery,shopping,todo','icon'=>'nullable|string|max:10','is_public'=>'boolean']);
        auth()->user()->listTemplates()->create($data);
        return back();
    }
}
