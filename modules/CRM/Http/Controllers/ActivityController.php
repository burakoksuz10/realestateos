<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Activity::with(['user', 'contact', 'lead', 'deal']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('crm::activities.index', compact('activities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'deal_id' => 'nullable|exists:deals,id',
            'metadata' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();

        $activity = Activity::create($validated);

        return back()->with('success', 'Aktivite kaydedildi.');
    }

    public function show(Activity $activity)
    {
        $activity->load(['user', 'contact', 'lead', 'deal']);
        return view('crm::activities.show', compact('activity'));
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Aktivite silindi.');
    }

    public function timeline(string $type, int $id)
    {
        $modelClass = match($type) {
            'contact' => \Modules\CRM\Models\Contact::class,
            'lead' => \Modules\CRM\Models\Lead::class,
            'deal' => \Modules\CRM\Models\Deal::class,
            default => abort(404),
        };

        $model = $modelClass::findOrFail($id);
        $activities = $model->activities()->with('user')->latest()->paginate(20);

        return view('crm::activities.timeline', compact('model', 'activities', 'type'));
    }
}
