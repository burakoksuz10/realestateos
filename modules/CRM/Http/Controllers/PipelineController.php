<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;

class PipelineController extends Controller
{
    public function index()
    {
        $pipelines = Pipeline::withCount('stages', 'deals')->get();
        return view('crm::pipelines.index', compact('pipelines'));
    }

    public function create()
    {
        return view('crm::pipelines.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Pipeline::where('is_default', true)->update(['is_default' => false]);
        }

        $pipeline = Pipeline::create($validated);

        return redirect()->route('admin.pipelines.show', $pipeline)
            ->with('success', 'Pipeline başarıyla oluşturuldu.');
    }

    public function show(Pipeline $pipeline)
    {
        $pipeline->load(['stages' => fn($q) => $q->orderBy('order'), 'stages.deals']);
        return view('crm::pipelines.show', compact('pipeline'));
    }

    public function edit(Pipeline $pipeline)
    {
        return view('crm::pipelines.edit', compact('pipeline'));
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'type' => 'nullable|in:deal,lead',
            'stages' => 'nullable|array',
            'stages.*.id' => 'nullable|integer',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.color' => 'nullable|string|max:32',
            'stages.*.probability' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validated['is_default'] ?? false) {
            Pipeline::where('id', '!=', $pipeline->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $pipeline->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default'  => (bool) ($validated['is_default'] ?? false),
            'is_active'   => (bool) ($validated['is_active'] ?? true),
            'type'        => $validated['type'] ?? $pipeline->type ?? 'deal',
        ]);

        $stages = $validated['stages'] ?? [];
        if (!empty($stages)) {
            $keptIds = [];
            foreach ($stages as $idx => $row) {
                $payload = [
                    'name'        => $row['name'],
                    'color'       => $row['color'] ?? '#0ea5e9',
                    'probability' => (int) ($row['probability'] ?? 50),
                    'order'       => $idx,
                ];
                if (!empty($row['id'])) {
                    PipelineStage::where('id', $row['id'])
                        ->where('pipeline_id', $pipeline->id)
                        ->update($payload);
                    $keptIds[] = (int) $row['id'];
                } else {
                    $new = $pipeline->stages()->create($payload);
                    $keptIds[] = $new->id;
                }
            }
            // Sadece deal'ı olmayan eski stage'leri sil
            $pipeline->stages()
                ->whereNotIn('id', $keptIds)
                ->whereDoesntHave('deals')
                ->delete();
        }

        return redirect()->route('admin.pipelines.show', $pipeline)
            ->with('success', 'Pipeline başarıyla güncellendi.');
    }

    /**
     * Bir stage için auto_actions JSON düzenleme sayfası.
     */
    public function editStageAutoActions(Pipeline $pipeline, PipelineStage $stage)
    {
        abort_unless($stage->pipeline_id === $pipeline->id, 404);

        $actions = $stage->auto_actions ?? [];

        return view('crm::pipelines.auto-actions', [
            'pipeline' => $pipeline,
            'stage'    => $stage,
            'actions'  => $actions,
        ]);
    }

    /**
     * Bir stage için auto_actions JSON kaydet.
     */
    public function updateStageAutoActions(Request $request, Pipeline $pipeline, PipelineStage $stage)
    {
        abort_unless($stage->pipeline_id === $pipeline->id, 404);

        $validated = $request->validate([
            'actions' => 'nullable|array',
            'actions.*.type' => 'required|string|in:create_task,notify_agent,notify_office,set_field,enroll_campaign,update_probability',
            'actions.*.title' => 'nullable|string|max:255',
            'actions.*.description' => 'nullable|string',
            'actions.*.due_in_hours' => 'nullable|integer|min:0|max:8760',
            'actions.*.priority' => 'nullable|string|in:low,medium,high,urgent',
            'actions.*.message' => 'nullable|string',
            'actions.*.field' => 'nullable|string|max:64',
            'actions.*.value' => 'nullable',
            'actions.*.campaign_slug' => 'nullable|string|max:128',
            'actions.*.probability' => 'nullable|integer|min:0|max:100',
        ]);

        $clean = [];
        foreach ($validated['actions'] ?? [] as $a) {
            $clean[] = array_filter($a, fn ($v) => $v !== null && $v !== '');
        }

        $stage->update(['auto_actions' => $clean]);

        return redirect()
            ->route('admin.pipelines.show', $pipeline)
            ->with('success', "'{$stage->name}' için " . count($clean) . " aksiyon kaydedildi.");
    }

    public function destroy(Pipeline $pipeline)
    {
        if ($pipeline->deals()->exists()) {
            return back()->with('error', 'Bu pipeline\'da fırsatlar var, silinemez.');
        }

        $pipeline->delete();
        return redirect()->route('admin.pipelines.index')
            ->with('success', 'Pipeline başarıyla silindi.');
    }

    public function reorderStages(Request $request, Pipeline $pipeline)
    {
        $validated = $request->validate([
            'stages' => 'required|array',
            'stages.*' => 'exists:pipeline_stages,id',
        ]);

        foreach ($validated['stages'] as $order => $stageId) {
            PipelineStage::where('id', $stageId)->update(['order' => $order]);
        }

        return response()->json(['success' => true]);
    }
}
