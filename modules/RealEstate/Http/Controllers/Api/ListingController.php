<?php

namespace Modules\RealEstate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['agent', 'project'])
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->when($request->type, fn($q) => $q->where('listing_type', $request->type))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        return $this->paginated($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'listing_type'    => 'required|in:sale,rent',
            'category'        => 'required|string',
            'price'           => 'required|numeric|min:0',
            'city'            => 'required|string',
            'district'        => 'nullable|string',
            'address'         => 'nullable|string',
            'gross_sqm'       => 'nullable|numeric',
            'net_sqm'         => 'nullable|numeric',
            'room_count'      => 'nullable|integer',
            'bathroom_count'  => 'nullable|integer',
            'floor_number'    => 'nullable|integer',
            'total_floors'    => 'nullable|integer',
            'building_age'    => 'nullable|integer',
            'description'     => 'nullable|string',
            'features'        => 'nullable|array',
            'agent_id'        => 'nullable|exists:users,id',
            'project_id'      => 'nullable|exists:projects,id',
        ]);

        return $this->success(Listing::create($data), 'İlan oluşturuldu.', 201);
    }

    public function show(Listing $listing)
    {
        $listing->increment('view_count');

        return $this->success($listing->load(['agent', 'project', 'media']));
    }

    public function update(Request $request, Listing $listing)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'listing_type'   => 'required|in:sale,rent',
            'category'       => 'required|string',
            'price'          => 'required|numeric|min:0',
            'city'           => 'required|string',
            'district'       => 'nullable|string',
            'address'        => 'nullable|string',
            'gross_sqm'      => 'nullable|numeric',
            'net_sqm'        => 'nullable|numeric',
            'room_count'     => 'nullable|integer',
            'bathroom_count' => 'nullable|integer',
            'floor_number'   => 'nullable|integer',
            'total_floors'   => 'nullable|integer',
            'building_age'   => 'nullable|integer',
            'description'    => 'nullable|string',
            'features'       => 'nullable|array',
            'agent_id'       => 'nullable|exists:users,id',
        ]);

        $listing->update($data);

        return $this->success($listing, 'İlan güncellendi.');
    }

    public function destroy(Listing $listing)
    {
        $listing->delete();

        return $this->success(null, 'İlan silindi.');
    }

    public function similar(Listing $listing)
    {
        $similar = Listing::where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->where('listing_type', $listing->listing_type)
            ->whereBetween('price', [
                $listing->price * 0.8,
                $listing->price * 1.2,
            ])
            ->where('status', 'active')
            ->with('media')
            ->take(6)
            ->get();

        return $this->success($similar);
    }

    public function stats()
    {
        return $this->success([
            'total'          => Listing::count(),
            'active'         => Listing::where('status', 'active')->count(),
            'for_sale'       => Listing::where('listing_type', 'sale')->count(),
            'for_rent'       => Listing::where('listing_type', 'rent')->count(),
            'sold'           => Listing::where('status', 'sold')->count(),
            'avg_price_sale' => Listing::where('listing_type', 'sale')->avg('price'),
            'avg_price_rent' => Listing::where('listing_type', 'rent')->avg('price'),
        ]);
    }

    public function recordView(Listing $listing)
    {
        $listing->increment('view_count');

        return $this->success(['view_count' => $listing->view_count]);
    }

    public function toggleFavorite(Listing $listing)
    {
        // Stub — implement with a pivot table when user favorites are needed
        return $this->success([
            'listing_id' => $listing->id,
            'favorited'  => true,
        ], 'Favori durumu güncellendi.');
    }
}
