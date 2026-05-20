<?php

namespace Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;
use Modules\RealEstate\Models\PortalSyncLog;
use Modules\RealEstate\Services\Portals\PortalManager;

class PortalSyncController extends Controller
{
    public function __construct(protected PortalManager $portals) {}

    public function index()
    {
        $listings = Listing::where('status', 'active')
            ->with('agent')
            ->latest()
            ->paginate(20);

        $portalInfo = [];
        foreach ($this->portals->all() as $key => $connector) {
            $portalInfo[$key] = [
                'name'       => $connector->name(),
                'configured' => $connector->isConfigured(),
                'last_sync'  => PortalSyncLog::where('portal', $key)->latest('synced_at')->first()?->synced_at,
            ];
        }

        $logs = PortalSyncLog::with('listing:id,reference_no,title')
            ->latest('synced_at')
            ->limit(50)
            ->get();

        return view('realestate::portal-sync.index', compact('listings', 'portalInfo', 'logs'));
    }

    public function sync(Request $request, Listing $listing)
    {
        $portal = $request->input('portal', 'sahibinden');

        if (!$this->portals->has($portal)) {
            return response()->json(['success' => false, 'message' => "Bilinmeyen portal: {$portal}"], 422);
        }

        $result = $this->portals->syncTo($listing, $portal);

        return response()->json([
            'success'           => $result['success'] ?? false,
            'message'           => $result['message'] ?? '',
            'portal'            => $portal,
            'listing_id'        => $listing->id,
            'portal_listing_id' => $result['portal_listing_id'] ?? null,
            'portal_url'        => $result['portal_url'] ?? null,
            'synced_at'         => now()->toIso8601String(),
        ]);
    }

    public function syncAll(Request $request, Listing $listing)
    {
        $results = $this->portals->syncToAll($listing);
        $success = collect($results)->filter(fn ($r) => $r['success'] ?? false)->count();
        $total = count($results);

        return response()->json([
            'success' => $success > 0,
            'message' => "{$success}/{$total} portala yayınlandı.",
            'results' => $results,
        ]);
    }

    public function remove(Request $request, Listing $listing)
    {
        $portal = $request->input('portal');
        if (!$portal || !$this->portals->has($portal)) {
            return response()->json(['success' => false, 'message' => 'Geçersiz portal.'], 422);
        }

        $result = $this->portals->removeFrom($listing, $portal);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
        ]);
    }

    public function logs(Request $request)
    {
        $portal = $request->input('portal');
        $logs = PortalSyncLog::with('listing:id,reference_no,title')
            ->when($portal, fn ($q) => $q->where('portal', $portal))
            ->latest('synced_at')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data'    => $logs->items(),
        ]);
    }
}
