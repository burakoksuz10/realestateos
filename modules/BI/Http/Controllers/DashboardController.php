<?php

namespace Modules\BI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BI\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $officeId = auth()->user()?->office_id;
        $filters = ['office_id' => $officeId];

        $summary  = $this->analytics->getDashboardSummary();
        $funnel   = $this->analytics->getConversionFunnel($filters);
        $agents   = $this->analytics->getAgentPerformance($filters);
        $revenue  = $this->analytics->getRevenueTrends(['months' => 6]);
        $sources  = $this->analytics->getLeadSourcePerformance($filters);

        return view('bi::dashboard', compact('summary', 'funnel', 'agents', 'revenue', 'sources'));
    }
}
