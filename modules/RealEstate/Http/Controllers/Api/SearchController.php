<?php

namespace Modules\RealEstate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;
use Modules\RealEstate\Models\Project;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $q = $request->q;

        $listings = Listing::where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhere('city', 'like', "%{$q}%")
                      ->orWhere('district', 'like', "%{$q}%");
            })
            ->with('media')
            ->take(10)
            ->get();

        $projects = Project::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('city', 'like', "%{$q}%")
                      ->orWhere('district', 'like', "%{$q}%");
            })
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'listings' => $listings,
                'projects' => $projects,
            ],
        ]);
    }

    public function suggestions(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $q = $request->q;

        $cities = Listing::where('city', 'like', "%{$q}%")
            ->distinct()->pluck('city')->take(5);

        $districts = Listing::where('district', 'like', "%{$q}%")
            ->distinct()->pluck('district')->take(5);

        $projects = Project::where('name', 'like', "%{$q}%")
            ->pluck('name')->take(5);

        return response()->json([
            'success' => true,
            'data' => [
                'cities' => $cities,
                'districts' => $districts,
                'projects' => $projects,
            ],
        ]);
    }

    public function filters()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'cities'          => Listing::where('status', 'active')->distinct()->whereNotNull('city')->pluck('city')->sort()->values(),
                'listing_types'   => ['sale', 'rent'],
                'categories'      => Listing::where('status', 'active')->distinct()->whereNotNull('category')->pluck('category')->sort()->values(),
                'price_range'     => [
                    'min' => Listing::where('status', 'active')->min('price'),
                    'max' => Listing::where('status', 'active')->max('price'),
                ],
                'sqm_range'       => [
                    'min' => Listing::where('status', 'active')->min('net_sqm'),
                    'max' => Listing::where('status', 'active')->max('net_sqm'),
                ],
                'room_counts'     => Listing::where('status', 'active')->distinct()->whereNotNull('room_count')->orderBy('room_count')->pluck('room_count'),
            ],
        ]);
    }
}
