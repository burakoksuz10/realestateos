<?php

namespace Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;
use Modules\RealEstate\Models\Project;
use Modules\RealEstate\Models\ListingVersion;
use Modules\RealEstate\Events\ListingCreated;
use Modules\RealEstate\Events\ListingPublished;

class ListingController extends Controller
{
    /**
     * Display a listing of listings
     */
    public function index(Request $request)
    {
        $query = Listing::with(['agent', 'office', 'project'])
            ->withCount(['inquiries', 'showings']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $listings = $query->paginate(20);

        // Get filter options
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'agent');
        })->get();

        $cities = Listing::distinct()->pluck('city')->filter();

        return view('realestate::listings.index', compact('listings', 'agents', 'cities'));
    }

    /**
     * Show the form for creating a new listing
     */
    public function create()
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        $projects = Project::active()->get();
        $listingTypes = config('reos.listings.types');

        return view('realestate::listings.create', compact('agents', 'projects', 'listingTypes'));
    }

    /**
     * Store a newly created listing
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'category' => 'required|string',
            'listing_type' => 'required|in:sale,rent,daily_rent',
            'price' => 'required|numeric|min:0',
            'price_currency' => 'required|string|size:3',
            'agent_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            
            // Location
            'city' => 'required|string',
            'district' => 'required|string',
            'neighborhood' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            
            // Property details
            'gross_sqm' => 'nullable|numeric|min:0',
            'net_sqm' => 'nullable|numeric|min:0',
            'room_count' => 'nullable|integer|min:0',
            'living_room_count' => 'nullable|integer|min:0',
            'bathroom_count' => 'nullable|integer|min:0',
            'floor_number' => 'nullable|integer',
            'total_floors' => 'nullable|integer|min:1',
            'building_age' => 'nullable|integer|min:0',
            'heating_type' => 'nullable|string',
            'is_furnished' => 'boolean',
            
            // Features
            'features' => 'nullable|array',
            'amenities' => 'nullable|array',
            
            // Authorization
            'authorization_type' => 'nullable|in:exclusive,open',
            'authorization_start' => 'nullable|date',
            'authorization_end' => 'nullable|date|after:authorization_start',
            
            // Media
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:10240',
        ]);

        $validated['office_id'] = auth()->user()->office_id;
        $validated['status'] = 'draft';

        $listing = Listing::create($validated);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $listing->addMedia($photo)
                    ->toMediaCollection('photos');
            }
        }

        // Create initial version
        ListingVersion::createFromListing($listing, 'Initial creation');

        // Fire event
        event(new ListingCreated($listing));

        return redirect()->route('admin.listings.show', $listing)
            ->with('success', 'İlan başarıyla oluşturuldu.');
    }

    /**
     * Display the specified listing
     */
    public function show(Listing $listing)
    {
        $listing->load([
            'agent',
            'office',
            'project',
            'versions' => fn($q) => $q->latest()->take(10),
            'portalSyncLogs' => fn($q) => $q->latest()->take(10),
            'inquiries' => fn($q) => $q->latest()->take(5),
        ]);

        // Get similar listings
        $similarListings = Listing::where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->where('type', $listing->type)
            ->where('listing_type', $listing->listing_type)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Get AI suggestions if available
        $aiSuggestions = $listing->ai_suggestions;

        return view('realestate::listings.show', compact('listing', 'similarListings', 'aiSuggestions'));
    }

    /**
     * Show the form for editing the specified listing
     */
    public function edit(Listing $listing)
    {
        $agents = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager', 'admin']);
        })->get();

        $projects = Project::active()->get();
        $listingTypes = config('reos.listings.types');

        return view('realestate::listings.edit', compact('listing', 'agents', 'projects', 'listingTypes'));
    }

    /**
     * Update the specified listing
     */
    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'category' => 'required|string',
            'listing_type' => 'required|in:sale,rent,daily_rent',
            'price' => 'required|numeric|min:0',
            'price_currency' => 'required|string|size:3',
            'agent_id' => 'required|exists:users,id',
            
            // Location
            'city' => 'required|string',
            'district' => 'required|string',
            'neighborhood' => 'nullable|string',
            'address' => 'nullable|string',
            
            // Property details
            'gross_sqm' => 'nullable|numeric|min:0',
            'net_sqm' => 'nullable|numeric|min:0',
            'room_count' => 'nullable|integer|min:0',
            'living_room_count' => 'nullable|integer|min:0',
            'bathroom_count' => 'nullable|integer|min:0',
            'floor_number' => 'nullable|integer',
            'total_floors' => 'nullable|integer|min:1',
            'building_age' => 'nullable|integer|min:0',
            'heating_type' => 'nullable|string',
            'is_furnished' => 'boolean',
            
            // Features
            'features' => 'nullable|array',
            'amenities' => 'nullable|array',
        ]);

        $listing->update($validated);

        // Create version
        ListingVersion::createFromListing($listing, $request->get('version_reason'));

        return redirect()->route('admin.listings.show', $listing)
            ->with('success', 'İlan başarıyla güncellendi.');
    }

    /**
     * Remove the specified listing
     */
    public function destroy(Listing $listing)
    {
        $listing->delete();

        return redirect()->route('admin.listings.index')
            ->with('success', 'İlan başarıyla silindi.');
    }

    /**
     * Publish the listing
     */
    public function publish(Listing $listing)
    {
        $listing->update([
            'status' => 'active',
            'published_at' => now(),
        ]);

        event(new ListingPublished($listing));

        return back()->with('success', 'İlan yayınlandı.');
    }

    /**
     * Unpublish the listing
     */
    public function unpublish(Listing $listing)
    {
        $listing->update([
            'status' => 'draft',
        ]);

        return back()->with('success', 'İlan yayından kaldırıldı.');
    }

    /**
     * Mark as sold
     */
    public function markAsSold(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'sold_price' => 'nullable|numeric|min:0',
            'sold_date' => 'nullable|date',
        ]);

        $listing->update([
            'status' => 'sold',
            'sold_at' => $validated['sold_date'] ?? now(),
            'price' => $validated['sold_price'] ?? $listing->price,
        ]);

        return back()->with('success', 'İlan satıldı olarak işaretlendi.');
    }

    /**
     * Duplicate listing
     */
    public function duplicate(Listing $listing)
    {
        $newListing = $listing->replicate();
        $newListing->reference_no = null;
        $newListing->slug = null;
        $newListing->status = 'draft';
        $newListing->published_at = null;
        $newListing->view_count = 0;
        $newListing->favorite_count = 0;
        $newListing->inquiry_count = 0;
        $newListing->save();

        // Copy media
        foreach ($listing->getMedia('photos') as $media) {
            $media->copy($newListing, 'photos');
        }

        return redirect()->route('admin.listings.edit', $newListing)
            ->with('success', 'İlan kopyalandı. Düzenleyebilirsiniz.');
    }

    /**
     * Generate brochure
     */
    public function generateBrochure(Listing $listing)
    {
        $pdf = \PDF::loadView('realestate::listings.brochure', compact('listing'));
        
        return $pdf->download("ilan-{$listing->reference_no}.pdf");
    }

    /**
     * Restore version
     */
    public function restoreVersion(Listing $listing, ListingVersion $version)
    {
        $version->restore();

        return back()->with('success', "Versiyon {$version->version_number} geri yüklendi.");
    }
}
