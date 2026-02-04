<?php

namespace Modules\BI\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Deal;
use Modules\RealEstate\Models\Listing;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get conversion funnel data
     */
    public function getConversionFunnel(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();
        $officeId = $filters['office_id'] ?? null;
        $agentId = $filters['agent_id'] ?? null;

        $leadQuery = Lead::whereBetween('created_at', [$dateFrom, $dateTo]);
        $dealQuery = Deal::whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($officeId) {
            $leadQuery->where('office_id', $officeId);
            $dealQuery->where('office_id', $officeId);
        }

        if ($agentId) {
            $leadQuery->where('assigned_to', $agentId);
            $dealQuery->where('assigned_to', $agentId);
        }

        $totalLeads = $leadQuery->count();
        $contactedLeads = (clone $leadQuery)->whereNotNull('first_response_at')->count();
        $qualifiedLeads = (clone $leadQuery)->where('is_qualified', true)->count();
        $showings = DB::table('activities')
            ->where('type', 'showing')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $proposals = (clone $dealQuery)->count();
        $wonDeals = (clone $dealQuery)->where('status', 'won')->count();

        return [
            'stages' => [
                ['name' => 'Leads', 'count' => $totalLeads, 'percentage' => 100],
                ['name' => 'İletişim Kuruldu', 'count' => $contactedLeads, 'percentage' => $totalLeads > 0 ? round(($contactedLeads / $totalLeads) * 100, 1) : 0],
                ['name' => 'Nitelikli', 'count' => $qualifiedLeads, 'percentage' => $totalLeads > 0 ? round(($qualifiedLeads / $totalLeads) * 100, 1) : 0],
                ['name' => 'Gösterim', 'count' => $showings, 'percentage' => $totalLeads > 0 ? round(($showings / $totalLeads) * 100, 1) : 0],
                ['name' => 'Teklif', 'count' => $proposals, 'percentage' => $totalLeads > 0 ? round(($proposals / $totalLeads) * 100, 1) : 0],
                ['name' => 'Satış', 'count' => $wonDeals, 'percentage' => $totalLeads > 0 ? round(($wonDeals / $totalLeads) * 100, 1) : 0],
            ],
            'conversion_rate' => $totalLeads > 0 ? round(($wonDeals / $totalLeads) * 100, 2) : 0,
        ];
    }

    /**
     * Get agent performance data
     */
    public function getAgentPerformance(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now();
        $officeId = $filters['office_id'] ?? null;

        $query = \App\Models\User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['agent', 'office-manager']);
        });

        if ($officeId) {
            $query->where('office_id', $officeId);
        }

        $agents = $query->get();

        return $agents->map(function ($agent) use ($dateFrom, $dateTo) {
            $leads = Lead::where('assigned_to', $agent->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo]);
            
            $deals = Deal::where('assigned_to', $agent->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            $wonDeals = (clone $deals)->where('status', 'won');

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'avatar' => $agent->avatar,
                'office' => $agent->office?->name,
                'metrics' => [
                    'leads' => $leads->count(),
                    'leads_converted' => (clone $leads)->where('status', 'converted')->count(),
                    'deals' => $deals->count(),
                    'deals_won' => $wonDeals->count(),
                    'revenue' => $wonDeals->sum('value'),
                    'commission' => $wonDeals->sum('commission_amount'),
                    'activities' => \Modules\CRM\Models\Activity::where('user_id', $agent->id)
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->count(),
                    'showings' => \Modules\CRM\Models\Activity::where('user_id', $agent->id)
                        ->where('type', 'showing')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->count(),
                ],
                'conversion_rate' => $leads->count() > 0 
                    ? round(((clone $leads)->where('status', 'converted')->count() / $leads->count()) * 100, 1) 
                    : 0,
            ];
        })->sortByDesc('metrics.revenue')->values()->toArray();
    }

    /**
     * Get lead source performance
     */
    public function getLeadSourcePerformance(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        $sources = Lead::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('source, COUNT(*) as total, 
                SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted,
                AVG(score) as avg_score')
            ->groupBy('source')
            ->get();

        return $sources->map(function ($source) {
            return [
                'source' => $source->source ?? 'Bilinmiyor',
                'total' => $source->total,
                'converted' => $source->converted,
                'conversion_rate' => $source->total > 0 ? round(($source->converted / $source->total) * 100, 1) : 0,
                'avg_score' => round($source->avg_score ?? 0, 1),
            ];
        })->sortByDesc('total')->values()->toArray();
    }

    /**
     * Get portal performance
     */
    public function getPortalPerformance(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        $portals = ['sahibinden', 'hepsiemlak', 'emlakjet', 'website', 'meta', 'google'];

        return collect($portals)->map(function ($portal) use ($dateFrom, $dateTo) {
            $leads = Lead::where('source', $portal)
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            $total = $leads->count();
            $converted = (clone $leads)->where('status', 'converted')->count();
            $revenue = Deal::whereHas('lead', function ($q) use ($portal) {
                $q->where('source', $portal);
            })->where('status', 'won')->sum('value');

            return [
                'portal' => $portal,
                'leads' => $total,
                'converted' => $converted,
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
                'revenue' => $revenue,
                'cost_per_lead' => 0, // Would need ad spend data
                'roi' => 0, // Would need ad spend data
            ];
        })->sortByDesc('leads')->values()->toArray();
    }

    /**
     * Get listing performance
     */
    public function getListingPerformance(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        $listings = Listing::withCount([
            'inquiries' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]),
            'showings' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]),
        ])
        ->where('status', 'active')
        ->orderByDesc('view_count')
        ->take(20)
        ->get();

        return $listings->map(function ($listing) {
            return [
                'id' => $listing->id,
                'title' => $listing->title,
                'reference_no' => $listing->reference_no,
                'price' => $listing->formatted_price,
                'location' => $listing->full_location,
                'views' => $listing->view_count,
                'inquiries' => $listing->inquiries_count,
                'showings' => $listing->showings_count,
                'quality_score' => $listing->quality_score,
                'days_on_market' => $listing->published_at ? $listing->published_at->diffInDays(now()) : 0,
            ];
        })->toArray();
    }

    /**
     * Get revenue trends
     */
    public function getRevenueTrends(array $filters = []): array
    {
        $months = $filters['months'] ?? 12;
        
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $deals = Deal::where('status', 'won')
                ->whereBetween('closed_at', [$startOfMonth, $endOfMonth]);

            $data[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
                'revenue' => $deals->sum('value'),
                'deals' => $deals->count(),
                'commission' => $deals->sum('commission_amount'),
            ];
        }

        return $data;
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // This month stats
        $leadsThisMonth = Lead::where('created_at', '>=', $thisMonth)->count();
        $leadsLastMonth = Lead::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $dealsThisMonth = Deal::where('status', 'won')->where('closed_at', '>=', $thisMonth)->count();
        $dealsLastMonth = Deal::where('status', 'won')->whereBetween('closed_at', [$lastMonth, $lastMonthEnd])->count();

        $revenueThisMonth = Deal::where('status', 'won')->where('closed_at', '>=', $thisMonth)->sum('value');
        $revenueLastMonth = Deal::where('status', 'won')->whereBetween('closed_at', [$lastMonth, $lastMonthEnd])->sum('value');

        return [
            'leads' => [
                'total' => Lead::count(),
                'this_month' => $leadsThisMonth,
                'change' => $leadsLastMonth > 0 ? round((($leadsThisMonth - $leadsLastMonth) / $leadsLastMonth) * 100, 1) : 0,
                'today' => Lead::where('created_at', '>=', $today)->count(),
            ],
            'deals' => [
                'total' => Deal::where('status', 'won')->count(),
                'this_month' => $dealsThisMonth,
                'change' => $dealsLastMonth > 0 ? round((($dealsThisMonth - $dealsLastMonth) / $dealsLastMonth) * 100, 1) : 0,
                'pipeline_value' => Deal::where('status', 'open')->sum('value'),
            ],
            'revenue' => [
                'total' => Deal::where('status', 'won')->sum('value'),
                'this_month' => $revenueThisMonth,
                'change' => $revenueLastMonth > 0 ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1) : 0,
            ],
            'listings' => [
                'total' => Listing::count(),
                'active' => Listing::where('status', 'active')->count(),
                'sold_this_month' => Listing::where('status', 'sold')->where('sold_at', '>=', $thisMonth)->count(),
            ],
        ];
    }
}
