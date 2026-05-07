<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Deal;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::with(['contact', 'assignedTo', 'pipeline', 'stage'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->latest();

        return $this->paginated($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'contact_id'          => 'nullable|exists:contacts,id',
            'pipeline_id'         => 'required|exists:pipelines,id',
            'stage_id'            => 'required|exists:pipeline_stages,id',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to'         => 'nullable|exists:users,id',
            'notes'               => 'nullable|string',
        ]);

        return $this->success(Deal::create($data), 'Fırsat oluşturuldu.', 201);
    }

    public function show(Deal $deal)
    {
        return $this->success($deal->load(['contact', 'assignedTo', 'pipeline', 'stage', 'activities', 'tasks']));
    }

    public function update(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'stage_id'            => 'nullable|exists:pipeline_stages,id',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'status'              => 'nullable|in:open,won,lost',
            'assigned_to'         => 'nullable|exists:users,id',
            'notes'               => 'nullable|string',
        ]);

        $deal->update($data);

        return $this->success($deal, 'Fırsat güncellendi.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return $this->success(null, 'Fırsat silindi.');
    }
}
