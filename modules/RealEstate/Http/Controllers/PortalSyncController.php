<?php

namespace Modules\RealEstate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class PortalSyncController extends Controller
{
    private array $portals = [
        'sahibinden' => ['name' => 'Sahibinden.com', 'color' => 'blue'],
        'hepsiemlak' => ['name' => 'Hepsiemlak.com', 'color' => 'green'],
        'emlakjet'   => ['name' => 'EmlakJet.com',   'color' => 'orange'],
        'zingat'     => ['name' => 'Zingat.com',      'color' => 'purple'],
    ];

    public function index()
    {
        $listings = Listing::where('status', 'active')
            ->with('agent')
            ->latest()
            ->paginate(20);

        $portals = $this->portals;

        $logs = $this->getRecentLogs();

        return view('realestate::portal-sync.index', compact('listings', 'portals', 'logs'));
    }

    public function sync(Listing $listing)
    {
        // Stub: real portal API integration requires third-party credentials
        $portal = request('portal', 'sahibinden');
        $portalName = $this->portals[$portal]['name'] ?? $portal;

        return response()->json([
            'success' => true,
            'message' => "{$portalName} portalına senkronizasyon tamamlandı (demo).",
            'listing_id' => $listing->id,
            'portal' => $portal,
            'synced_at' => now()->toISOString(),
        ]);
    }

    public function syncAll()
    {
        $count = Listing::where('status', 'active')->count();

        return response()->json([
            'success' => true,
            'message' => "{$count} aktif ilan tüm portallara senkronize edildi (demo).",
            'synced_count' => $count,
            'portals' => array_keys($this->portals),
            'synced_at' => now()->toISOString(),
        ]);
    }

    public function logs()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getRecentLogs(),
        ]);
    }

    private function getRecentLogs(): array
    {
        // Stub data — replace with DB log table when portal integrations are live
        return [
            ['portal' => 'Sahibinden.com', 'listing' => 'Demo İlan #1', 'status' => 'success', 'message' => 'Senkronize edildi', 'time' => now()->subMinutes(5)->format('d.m.Y H:i')],
            ['portal' => 'Hepsiemlak.com', 'listing' => 'Demo İlan #2', 'status' => 'error',   'message' => 'API anahtarı eksik',  'time' => now()->subMinutes(15)->format('d.m.Y H:i')],
            ['portal' => 'EmlakJet.com',   'listing' => 'Demo İlan #1', 'status' => 'success', 'message' => 'Senkronize edildi', 'time' => now()->subMinutes(30)->format('d.m.Y H:i')],
        ];
    }
}
