<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Pipeline;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Deal::with(['contact', 'assignedTo', 'stage', 'pipeline']);

        if (!$user->isAdmin()) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        $deals = $query->orderBy('created_at', 'desc')->paginate(20);
        $pipelines = Pipeline::all();

        return view('crm::deals.index', compact('deals', 'pipelines'));
    }

    public function kanban(Request $request)
    {
        $user = $request->user();
        $pipeline = Pipeline::with(['stages.deals' => function ($query) use ($user) {
            if (!$user->isAdmin()) {
                $query->where('assigned_to', $user->id);
            }
            $query->with(['contact', 'assignedTo']);
        }])->where('is_default', true)->first();

        if (!$pipeline) {
            $pipeline = Pipeline::with(['stages.deals'])->first();
        }

        $pipelines = Pipeline::all();

        return view('crm::deals.kanban', compact('pipeline', 'pipelines'));
    }

    public function create()
    {
        $pipelines = Pipeline::with('stages')->get();
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        return view('crm::deals.create', compact('pipelines', 'agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'pipeline_id' => 'required|exists:pipelines,id',
            'stage_id' => 'required|exists:pipeline_stages,id',
            'assigned_to' => 'required|exists:users,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'open';
        $deal = Deal::create($validated);

        return redirect()->route('admin.deals.show', $deal)
            ->with('success', 'Fırsat başarıyla oluşturuldu.');
    }

    public function show(Deal $deal)
    {
        $deal->load(['contact', 'assignedTo', 'stage', 'pipeline', 'activities', 'tasks']);
        return view('crm::deals.show', compact('deal'));
    }

    public function edit(Deal $deal)
    {
        $pipelines = Pipeline::with('stages')->get();
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        return view('crm::deals.edit', compact('deal', 'pipelines', 'agents'));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric|min:0',
            'stage_id' => 'required|exists:pipeline_stages,id',
            'assigned_to' => 'required|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal->update($validated);

        return redirect()->route('admin.deals.show', $deal)
            ->with('success', 'Fırsat başarıyla güncellendi.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('admin.deals.index')
            ->with('success', 'Fırsat başarıyla silindi.');
    }

    public function moveStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $deal->update(['stage_id' => $validated['stage_id']]);

        return response()->json(['success' => true]);
    }

    public function markWon(Request $request, Deal $deal)
    {
        $deal->update([
            'status' => 'won',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Fırsat kazanıldı olarak işaretlendi.');
    }

    public function markLost(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'lost_reason' => 'nullable|string|max:255',
        ]);

        $deal->update([
            'status' => 'lost',
            'closed_at' => now(),
            'lost_reason' => $validated['lost_reason'] ?? null,
        ]);

        return back()->with('success', 'Fırsat kaybedildi olarak işaretlendi.');
    }

    public function commission(Deal $deal)
    {
        return view('crm::deals.commission', compact('deal'));
    }
}
