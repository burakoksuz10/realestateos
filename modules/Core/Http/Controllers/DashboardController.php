<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Deal;
use Modules\RealEstate\Models\Listing;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get stats based on user role
        $stats = $this->getStats($user);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);
        
        // Get upcoming tasks
        $upcomingTasks = $this->getUpcomingTasks($user);
        
        // Get pipeline data
        $pipelineData = $this->getPipelineData($user);
        
        // Get performance metrics
        $performance = $this->getPerformanceMetrics($user);
        
        return view('core::dashboard.index', compact(
            'stats',
            'recentActivities',
            'upcomingTasks',
            'pipelineData',
            'performance'
        ));
    }

    /**
     * Get dashboard stats via AJAX
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $this->getStats($user);
        
        return response()->json($stats);
    }

    /**
     * Get stats based on user role
     */
    protected function getStats($user): array
    {
        $query = fn($model) => $user->isAdmin() 
            ? $model::query() 
            : $model::where('assigned_to', $user->id);

        $listingQuery = $user->isAdmin()
            ? Listing::query()
            : Listing::where('agent_id', $user->id);

        return [
            'total_leads' => $query(Lead::class)->count(),
            'new_leads_today' => $query(Lead::class)->whereDate('created_at', today())->count(),
            'hot_leads' => $query(Lead::class)->where('score', '>=', 80)->count(),
            
            'total_deals' => $query(Deal::class)->count(),
            'deals_this_month' => $query(Deal::class)->whereMonth('created_at', now()->month)->count(),
            'deals_value' => $query(Deal::class)->where('status', 'won')->sum('value'),
            
            'active_listings' => $listingQuery->where('status', 'active')->count(),
            'total_listings' => $listingQuery->count(),
            'listings_views' => $listingQuery->sum('view_count'),
            
            'pending_tasks' => \Modules\CRM\Models\Task::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count(),
            
            'conversion_rate' => $this->calculateConversionRate($user),
        ];
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities($user): \Illuminate\Support\Collection
    {
        return \Modules\CRM\Models\Activity::with(['user', 'contact', 'lead', 'deal'])
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Get upcoming tasks
     */
    protected function getUpcomingTasks($user): \Illuminate\Support\Collection
    {
        return \Modules\CRM\Models\Task::with(['lead', 'deal', 'contact'])
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->take(5)
            ->get();
    }

    /**
     * Get pipeline data for chart
     */
    protected function getPipelineData($user): array
    {
        $stages = \Modules\CRM\Models\Pipeline::with(['stages' => function ($query) use ($user) {
            $query->withCount(['deals' => function ($q) use ($user) {
                if (!$user->isAdmin()) {
                    $q->where('assigned_to', $user->id);
                }
            }]);
        }])->where('is_default', true)->first();

        if (!$stages) {
            return [];
        }

        return $stages->stages->map(function ($stage) {
            return [
                'name' => $stage->name,
                'count' => $stage->deals_count,
                'value' => $stage->deals->sum('value'),
                'color' => $stage->color,
            ];
        })->toArray();
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics($user): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $query = fn($model) => $user->isAdmin()
            ? $model::query()
            : $model::where('assigned_to', $user->id);

        return [
            'leads_this_month' => $query(Lead::class)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
            
            'deals_closed' => $query(Deal::class)
                ->where('status', 'won')
                ->whereBetween('closed_at', [$startOfMonth, $endOfMonth])
                ->count(),
            
            'revenue' => $query(Deal::class)
                ->where('status', 'won')
                ->whereBetween('closed_at', [$startOfMonth, $endOfMonth])
                ->sum('value'),
            
            'activities' => \Modules\CRM\Models\Activity::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate($user): float
    {
        $query = fn($model) => $user->isAdmin()
            ? $model::query()
            : $model::where('assigned_to', $user->id);

        $totalLeads = $query(Lead::class)->count();
        $convertedLeads = $query(Lead::class)->where('status', 'converted')->count();

        if ($totalLeads === 0) {
            return 0;
        }

        return round(($convertedLeads / $totalLeads) * 100, 2);
    }
}
