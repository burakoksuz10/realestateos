<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiUsageLog;
use App\Services\AI\AiCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AI\Services\AIService;
use Modules\AI\Services\CopilotService;
use Modules\AI\Services\MatchingService;
use Modules\CRM\Models\Lead;

class CopilotController extends Controller
{
    public function __construct(
        protected CopilotService $copilot,
        protected MatchingService $matching,
        protected AIService $ai,
        protected AiCreditService $credits,
    ) {
    }

    public function index()
    {
        $officeId = auth()->user()?->office_id;
        $credit = $officeId ? $this->credits->ensure($officeId) : null;
        $recentUsage = AiUsageLog::forOffice($officeId)
            ->thisMonth()
            ->latest()
            ->take(10)
            ->get();

        return view('ai::copilot.index', compact('credit', 'recentUsage'));
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'nullable|array',
            'history' => 'nullable|array',
        ]);

        $user = Auth::user();
        $this->ai->withContext($user?->office_id, $user?->id, 'copilot.chat');

        $systemPrompt = "Sen RE-OS adlı emlak yönetim platformunda çalışan akıllı bir asistansın. "
            . "Türkçe cevap ver. Kullanıcı bir emlak danışmanı. "
            . "Sana lead, ilan, görev, randevu, müşteri yönetimi hakkında soru sorabilir. "
            . "Cevaplarını kısa, eyleme dönük ve net tut. "
            . "Eğer gerçek veri çekmen gerekiyorsa, kullanıcıya hangi sayfaya gitmesi gerektiğini söyle.";

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach (($validated['history'] ?? []) as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        $reply = $this->ai->chat($messages, [
            'temperature' => 0.6,
            'max_tokens' => 800,
        ]);

        if (!$reply) {
            return response()->json([
                'message' => $this->ai->isConfigured()
                    ? 'AI servisi şu anda yanıt veremiyor. Lütfen birkaç saniye sonra tekrar deneyin.'
                    : 'AI Copilot için OpenAI API anahtarınız yapılandırılmadı. Ayarlar > AI bölümünden ekleyebilirsiniz.',
                'configured' => $this->ai->isConfigured(),
            ], 200);
        }

        return response()->json([
            'message' => $reply,
            'configured' => true,
        ]);
    }

    public function leadSuggestions(Lead $lead)
    {
        $this->ai->withContext($lead->office_id, auth()->id(), 'copilot.lead');
        $suggestions = $this->copilot->getLeadSuggestions($lead);
        return response()->json($suggestions);
    }

    public function analyzeCall(Request $request)
    {
        $validated = $request->validate([
            'transcript' => 'required|string|min:50',
            'lead_id' => 'nullable|integer|exists:leads,id',
        ]);

        $lead = $validated['lead_id'] ? Lead::find($validated['lead_id']) : null;
        $officeId = $lead?->office_id ?? auth()->user()?->office_id;

        $this->ai->withContext($officeId, auth()->id(), 'copilot.call');
        $result = $this->copilot->analyzeCall($validated['transcript']);

        return response()->json($result);
    }

    public function suggestAppointments(Lead $lead)
    {
        $user = Auth::user();
        $this->ai->withContext($lead->office_id, $user->id, 'copilot.appointments');
        $suggestions = $this->copilot->suggestAppointments($lead, $user);
        return response()->json($suggestions);
    }

    /**
     * Natural-language property search. Used by the Copilot widget.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
        ]);
        $this->ai->withContext(auth()->user()?->office_id, auth()->id(), 'copilot.search');
        $listings = $this->matching->semanticSearch($validated['query'], 10);
        return response()->json([
            'count' => $listings->count(),
            'listings' => $listings->map(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'price' => $l->formatted_price ?? $l->price,
                'city' => $l->city,
                'district' => $l->district,
                'rooms' => $l->room_count,
                'sqm' => $l->gross_sqm,
                'url' => route('admin.listings.show', $l->id),
                'thumbnail' => method_exists($l, 'getFirstMediaUrl') ? $l->getFirstMediaUrl('photos', 'thumb') : null,
            ]),
        ]);
    }

    /**
     * Generic suggestion lists used by various screens.
     */
    public function suggestions(Request $request)
    {
        $type = $request->get('type', 'general');

        $suggestions = match ($type) {
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
