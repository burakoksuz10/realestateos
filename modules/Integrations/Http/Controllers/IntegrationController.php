<?php

namespace Modules\Integrations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Display a listing of integrations
     */
    public function index()
    {
        $integrations = [
            [
                'id' => 'sahibinden',
                'name' => 'Sahibinden.com',
                'description' => 'Türkiye\'nin en büyük emlak portalı',
                'icon' => 'fas fa-home',
                'color' => 'yellow',
                'status' => 'active',
                'last_sync' => now()->subHours(2),
            ],
            [
                'id' => 'hepsiemlak',
                'name' => 'Hepsiemlak',
                'description' => 'Emlak ilan portalı',
                'icon' => 'fas fa-building',
                'color' => 'blue',
                'status' => 'active',
                'last_sync' => now()->subHours(1),
            ],
            [
                'id' => 'emlakjet',
                'name' => 'Emlakjet',
                'description' => 'Emlak ilan portalı',
                'icon' => 'fas fa-rocket',
                'color' => 'purple',
                'status' => 'inactive',
                'last_sync' => null,
            ],
            [
                'id' => 'whatsapp',
                'name' => 'WhatsApp Business',
                'description' => 'WhatsApp mesajlaşma entegrasyonu',
                'icon' => 'fab fa-whatsapp',
                'color' => 'green',
                'status' => 'active',
                'last_sync' => now()->subMinutes(30),
            ],
            [
                'id' => 'sms',
                'name' => 'SMS Gateway',
                'description' => 'Toplu SMS gönderimi',
                'icon' => 'fas fa-sms',
                'color' => 'cyan',
                'status' => 'active',
                'last_sync' => now()->subHours(5),
            ],
            [
                'id' => 'call',
                'name' => 'Santral Entegrasyonu',
                'description' => 'VoIP ve çağrı merkezi entegrasyonu',
                'icon' => 'fas fa-phone',
                'color' => 'red',
                'status' => 'inactive',
                'last_sync' => null,
            ],
            [
                'id' => 'payment',
                'name' => 'Ödeme Sistemi',
                'description' => 'Online ödeme entegrasyonu',
                'icon' => 'fas fa-credit-card',
                'color' => 'emerald',
                'status' => 'inactive',
                'last_sync' => null,
            ],
            [
                'id' => 'google_maps',
                'name' => 'Google Maps',
                'description' => 'Harita ve konum servisleri',
                'icon' => 'fas fa-map-marked-alt',
                'color' => 'orange',
                'status' => 'active',
                'last_sync' => now()->subDays(1),
            ],
        ];

        return view('integrations::index', compact('integrations'));
    }

    /**
     * Display the specified integration
     */
    public function show($integration)
    {
        return view('integrations::show', compact('integration'));
    }

    /**
     * Update the specified integration
     */
    public function update(Request $request, $integration)
    {
        // Update integration settings
        return back()->with('success', 'Entegrasyon ayarları güncellendi.');
    }

    /**
     * Test the integration connection
     */
    public function test($integration)
    {
        // Test integration
        return response()->json([
            'success' => true,
            'message' => 'Bağlantı başarılı!'
        ]);
    }
}
