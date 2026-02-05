<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CopilotController extends Controller
{
    public function index()
    {
        return view('ai::copilot.index');
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // AI response placeholder
        $response = [
            'message' => 'AI Copilot özelliği yakında aktif olacak. Şu anda geliştirme aşamasındadır.',
            'suggestions' => [
                'Müşteri takibi için hatırlatıcı oluştur',
                'Bugünkü görevlerimi listele',
                'Son eklenen ilanları göster',
            ],
        ];

        return response()->json($response);
    }

    public function suggestions(Request $request)
    {
        $type = $request->get('type', 'general');

        $suggestions = match($type) {
            'lead' => [
                'Bu lead için takip görevi oluştur',
                'Benzer ilanları bul',
                'İletişim geçmişini göster',
            ],
            'listing' => [
                'İlan açıklamasını iyileştir',
                'Fiyat analizi yap',
                'Benzer ilanları karşılaştır',
            ],
            'deal' => [
                'Komisyon hesapla',
                'Sonraki adımları öner',
                'Risk analizi yap',
            ],
            default => [
                'Bugünkü görevlerimi göster',
                'Yeni leadleri listele',
                'Performans raporumu göster',
            ],
        };

        return response()->json(['suggestions' => $suggestions]);
    }
}
