<?php

namespace Modules\Integrations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RealEstate\Models\Listing;

class PortalController extends Controller
{
    private array $portals = [
        'sahibinden' => 'Sahibinden.com',
        'hepsiemlak' => 'Hepsiemlak.com',
        'emlakjet'   => 'EmlakJet.com',
        'zingat'     => 'Zingat.com',
    ];

    public function index()
    {
        $listings = Listing::where('status', 'active')->with('agent')->latest()->paginate(20);

        return view('integrations::portals.index', [
            'listings' => $listings,
            'portals'  => $this->portals,
            'logs'     => $this->demoLogs(),
        ]);
    }

    public function logs()
    {
        return response()->json(['success' => true, 'data' => $this->demoLogs()]);
    }

    public function sync(Listing $listing)
    {
        $portal = request('portal', 'sahibinden');

        return response()->json([
            'success'   => true,
            'message'   => ($this->portals[$portal] ?? $portal) . ' portalına senkronizasyon tamamlandı (demo).',
            'synced_at' => now()->toISOString(),
        ]);
    }

    public function syncAll(Listing $listing)
    {
        return response()->json([
            'success'   => true,
            'message'   => 'Tüm portallara senkronizasyon tamamlandı (demo).',
            'portals'   => array_keys($this->portals),
            'synced_at' => now()->toISOString(),
        ]);
    }

    public function remove(Listing $listing, string $portal)
    {
        return response()->json([
            'success' => true,
            'message' => ($this->portals[$portal] ?? $portal) . ' portalından ilan kaldırıldı (demo).',
        ]);
    }

    private function demoLogs(): array
    {
        return [
            ['portal' => 'Sahibinden.com', 'status' => 'success', 'message' => 'Senkronize edildi', 'time' => now()->subMinutes(10)->format('d.m.Y H:i')],
            ['portal' => 'Hepsiemlak.com', 'status' => 'error',   'message' => 'API anahtarı eksik',  'time' => now()->subMinutes(25)->format('d.m.Y H:i')],
        ];
    }
}
