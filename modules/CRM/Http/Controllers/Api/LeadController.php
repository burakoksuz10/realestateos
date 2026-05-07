<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['contact', 'assignedTo', 'pipeline'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
            ->latest();

        return $this->paginated($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'    => 'required|exists:contacts,id',
            'pipeline_id'   => 'nullable|exists:pipelines,id',
            'stage_id'      => 'nullable|exists:pipeline_stages,id',
            'interest_type' => 'nullable|string',
            'property_type' => 'nullable|string',
            'budget_min'    => 'nullable|numeric',
            'budget_max'    => 'nullable|numeric',
            'priority'      => 'nullable|in:low,medium,high,urgent',
            'assigned_to'   => 'nullable|exists:users,id',
            'notes'         => 'nullable|string',
        ]);

        return $this->success(Lead::create($data), 'Lead oluşturuldu.', 201);
    }

    public function show(Lead $lead)
    {
        return $this->success($lead->load(['contact', 'assignedTo', 'pipeline', 'stage', 'activities', 'tasks']));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'interest_type' => 'nullable|string',
            'property_type' => 'nullable|string',
            'budget_min'    => 'nullable|numeric',
            'budget_max'    => 'nullable|numeric',
            'priority'      => 'nullable|in:low,medium,high,urgent',
            'status'        => 'nullable|string',
            'score'         => 'nullable|integer|min:0|max:100',
            'assigned_to'   => 'nullable|exists:users,id',
            'notes'         => 'nullable|string',
        ]);

        $lead->update($data);

        return $this->success($lead, 'Lead güncellendi.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return $this->success(null, 'Lead silindi.');
    }
}
