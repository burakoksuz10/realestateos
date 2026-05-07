<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Activity;
use Modules\RealEstate\Models\Listing;

class DashboardController extends Controller
{
    public function stats()
    {
        return $this->success([
            'listings'      => Listing::count(),
            'active_listings' => Listing::where('status', 'active')->count(),
            'leads'         => Lead::count(),
            'new_leads'     => Lead::where('created_at', '>=', now()->startOfMonth())->count(),
            'deals'         => Deal::count(),
            'won_deals'     => Deal::where('status', 'won')->count(),
            'revenue'       => Deal::where('status', 'won')->sum('value'),
            'agents'        => User::count(),
        ]);
    }

    public function charts()
    {
        $months = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month'  => $date->format('M Y'),
                'leads'  => Lead::whereYear('created_at', $date->year)
                                ->whereMonth('created_at', $date->month)->count(),
                'deals'  => Deal::whereYear('created_at', $date->year)
                                ->whereMonth('created_at', $date->month)->count(),
                'revenue' => Deal::where('status', 'won')
                                 ->whereYear('closed_at', $date->year)
                                 ->whereMonth('closed_at', $date->month)->sum('value'),
            ];
        });

        return $this->success(['monthly' => $months]);
    }

    public function recentActivity()
    {
        $activities = Activity::with(['user', 'contact', 'lead', 'deal'])
            ->latest()
            ->take(10)
            ->get();

        return $this->success($activities);
    }
}
