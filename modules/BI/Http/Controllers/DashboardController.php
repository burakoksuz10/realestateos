<?php

namespace Modules\BI\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;
use Modules\RealEstate\Models\Listing;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_listings'  => Listing::count(),
            'active_listings' => Listing::where('status', 'active')->count(),
            'total_leads'     => Lead::count(),
            'won_deals'       => Deal::where('status', 'won')->count(),
            'total_revenue'   => Deal::where('status', 'won')->sum('value'),
        ];

        return view('bi::dashboard', compact('stats'));
    }
}
