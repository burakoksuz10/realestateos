<?php

namespace Modules\RealEstate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Project;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::withCount('listings')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->when($request->featured, fn($q) => $q->where('is_featured', true))
            ->latest();

        return $this->paginated($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'developer'       => 'nullable|string|max:255',
            'city'            => 'required|string',
            'district'        => 'required|string',
            'address'         => 'nullable|string',
            'total_units'     => 'nullable|integer|min:1',
            'available_units' => 'nullable|integer|min:0',
            'min_price'       => 'nullable|numeric|min:0',
            'max_price'       => 'nullable|numeric|min:0',
            'delivery_date'   => 'nullable|date',
            'status'          => 'required|in:planning,under_construction,completed',
        ]);

        return $this->success(Project::create($data), 'Proje oluşturuldu.', 201);
    }

    public function show(Project $project)
    {
        return $this->success($project->load(['listings', 'media']));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'developer'       => 'nullable|string|max:255',
            'city'            => 'required|string',
            'district'        => 'required|string',
            'address'         => 'nullable|string',
            'total_units'     => 'nullable|integer|min:1',
            'available_units' => 'nullable|integer|min:0',
            'min_price'       => 'nullable|numeric|min:0',
            'max_price'       => 'nullable|numeric|min:0',
            'delivery_date'   => 'nullable|date',
            'status'          => 'required|in:planning,under_construction,completed',
        ]);

        $project->update($data);

        return $this->success($project, 'Proje güncellendi.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return $this->success(null, 'Proje silindi.');
    }

    public function listings(Project $project)
    {
        return $this->paginated(
            $project->listings()->with('media')->latest()->paginate(20)
        );
    }

    public function stats(Project $project)
    {
        return $this->success([
            'total_units'     => $project->total_units,
            'available_units' => $project->available_units,
            'sold_units'      => ($project->total_units ?? 0) - ($project->available_units ?? 0),
            'listings_count'  => $project->listings()->count(),
            'active_listings' => $project->listings()->where('status', 'active')->count(),
            'min_price'       => $project->min_price,
            'max_price'       => $project->max_price,
        ]);
    }
}
