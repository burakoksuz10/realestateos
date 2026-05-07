<?php

namespace Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request)
    {
        $query = Project::withCount('listings');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $projects = $query->paginate(20);

        $cities = Project::distinct()->pluck('city')->filter();

        return view('realestate::projects.index', compact('projects', 'cities'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        return view('realestate::projects.create');
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'developer' => 'nullable|string|max:255',
            'city' => 'required|string',
            'district' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_units' => 'nullable|integer|min:1',
            'available_units' => 'nullable|integer|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:planning,under_construction,completed',
            'features' => 'nullable|array',
            'amenities' => 'nullable|array',
        ]);

        $project = Project::create($validated);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Proje başarıyla oluşturuldu.');
    }

    /**
     * Display the specified project
     */
    public function show(Project $project)
    {
        $project->load(['listings' => fn($q) => $q->latest()->take(10)]);

        return view('realestate::projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project)
    {
        return view('realestate::projects.edit', compact('project'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'developer' => 'nullable|string|max:255',
            'city' => 'required|string',
            'district' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_units' => 'nullable|integer|min:1',
            'available_units' => 'nullable|integer|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:planning,under_construction,completed',
            'features' => 'nullable|array',
            'amenities' => 'nullable|array',
        ]);

        $project->update($validated);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Proje başarıyla güncellendi.');
    }

    /**
     * Remove the specified project
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proje başarıyla silindi.');
    }

    public function toggleFeatured(Project $project)
    {
        $project->update(['is_featured' => !$project->is_featured]);

        return back()->with('success', $project->is_featured ? 'Proje öne çıkarıldı.' : 'Proje öne çıkarmadan kaldırıldı.');
    }
}
