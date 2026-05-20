<?php

namespace Modules\BI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BI\Services\AnalyticsService;

class ReportController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    protected function dateFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from') ? now()->parse($request->input('date_from'))->startOfDay() : now()->subDays(30),
            'date_to'   => $request->input('date_to') ? now()->parse($request->input('date_to'))->endOfDay() : now(),
            'office_id' => auth()->user()?->office_id,
            'agent_id'  => $request->input('agent_id'),
        ];
    }

    public function conversionFunnel(Request $request)
    {
        $filters = $this->dateFilters($request);
        $funnel  = $this->analytics->getConversionFunnel($filters);
        return view('bi::reports.conversion-funnel', compact('funnel', 'filters'));
    }

    public function agentPerformance(Request $request)
    {
        $filters = $this->dateFilters($request);
        $agents  = $this->analytics->getAgentPerformance($filters);
        return view('bi::reports.agent-performance', compact('agents', 'filters'));
    }

    public function leadSources(Request $request)
    {
        $filters = $this->dateFilters($request);
        $sources = $this->analytics->getLeadSourcePerformance($filters);
        return view('bi::reports.lead-sources', compact('sources', 'filters'));
    }

    public function portalPerformance(Request $request)
    {
        $filters = $this->dateFilters($request);
        $portals = $this->analytics->getPortalPerformance($filters);
        return view('bi::reports.portal-performance', compact('portals', 'filters'));
    }

    public function listingPerformance(Request $request)
    {
        $filters = $this->dateFilters($request);
        $listings = $this->analytics->getListingPerformance($filters);
        return view('bi::reports.listing-performance', compact('listings', 'filters'));
    }

    public function revenue(Request $request)
    {
        $months = (int) $request->input('months', 12);
        $trend = $this->analytics->getRevenueTrends(['months' => max(3, min(24, $months))]);
        return view('bi::reports.revenue', compact('trend', 'months'));
    }

    public function export(string $report)
    {
        return response()->json(['success' => false, 'message' => 'Rapor dışa aktarımı henüz aktif değil.']);
    }
}
