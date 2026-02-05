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
        ]);

        if ($validated['is_default'] ?? false) {
            Pipeline::where('id', '!=', $pipeline->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $pipeline->update($validated);

        return redirect()->route('admin.pipelines.show', $pipeline)
            ->with('success', 'Pipeline başarıyla güncellendi.');
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
