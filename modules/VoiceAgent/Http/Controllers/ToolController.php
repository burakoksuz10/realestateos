<?php

namespace Modules\VoiceAgent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\AI\Services\MatchingService;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;
use Modules\RealEstate\Models\Listing;
use Modules\Telegram\Services\TelegramService;
use Modules\VoiceAgent\Models\VoiceAgentConfig;
use Modules\VoiceAgent\Services\TransferRouter;
use Modules\VoiceAgent\Services\PreCallBriefService;

/**
 * ElevenLabs Conversational AI Agent'in çağıracağı tool endpoint'leri.
 *
 * Her endpoint JSON döner — agent tool result olarak yorumlar.
 * Auth: `X-Voice-Agent-Token` (verify middleware).
 */
class ToolController extends Controller
{
    /**
     * Doğal dilde ilan arama.
     *
     * POST /api/voice-agent/tools/search-listing
     * Body: { query, office_id?, limit? }
     */
    public function searchListing(Request $request, MatchingService $matching): JsonResponse
    {
        $data = $request->validate([
            'query'     => 'required|string|max:500',
            'office_id' => 'nullable|integer',
            'limit'     => 'nullable|integer|min:1|max:10',
        ]);

        $limit = $data['limit'] ?? 5;

        $query = Listing::query()
            ->where('status', 'active')
            ->when(!empty($data['office_id']), fn ($q) => $q->where('office_id', $data['office_id']));

        // Önce hızlı yapısal arama (referans no, başlık, şehir/ilçe)
        $term = $data['query'];
        $words = preg_split('/\s+/', mb_strtolower($term));
        foreach ($words as $word) {
            if (mb_strlen($word) < 2) continue;
            $query->where(function ($q) use ($word) {
                $q->where('title', 'like', "%{$word}%")
                  ->orWhere('reference_no', 'like', "%{$word}%")
                  ->orWhere('city', 'like', "%{$word}%")
                  ->orWhere('district', 'like', "%{$word}%")
                  ->orWhere('neighborhood', 'like', "%{$word}%");
            });
        }

        $listings = $query->limit($limit)->get();

        // Bulunamadıysa semantic search'e düş
        if ($listings->isEmpty() && method_exists($matching, 'semanticSearch')) {
            try {
                $listings = $matching->semanticSearch($term, $limit);
            } catch (\Throwable $e) {
                Log::warning('Voice agent semantic search failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'count'    => $listings->count(),
            'listings' => $listings->map(fn (Listing $l) => $this->formatListing($l))->values(),
        ]);
    }

    /**
     * Sesli arama sırasında müşteri bilgilerini al ve potansiyel müşteri olarak kaydet.
     *
     * POST /api/voice-agent/tools/create-lead
     * Body: { caller_phone, caller_name?, interested_listing_ref?, intent, budget?, notes?, office_id }
     */
    public function createLead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'office_id'              => 'required|integer|exists:offices,id',
            'caller_phone'           => 'required|string|max:30',
            'caller_name'            => 'nullable|string|max:120',
            'interested_listing_ref' => 'nullable|string|max:50',
            'intent'                 => 'nullable|string|in:buy,rent,sell,inquiry,follow_up,other',
            'budget'                 => 'nullable|numeric',
            'notes'                  => 'nullable|string|max:2000',
        ]);

        // Telefondan kontak ara, yoksa oluştur
        $contact = Contact::where('phone', $data['caller_phone'])
            ->where(function ($q) use ($data) {
                $q->whereNull('office_id')->orWhere('office_id', $data['office_id']);
            })
            ->first();

        if (!$contact) {
            $name = trim($data['caller_name'] ?? '') ?: 'Sesli arayan';
            $parts = explode(' ', $name, 2);
            $contact = Contact::create([
                'office_id'  => $data['office_id'],
                'first_name' => $parts[0] ?? $name,
                'last_name'  => $parts[1] ?? '',
                'phone'      => $data['caller_phone'],
                'source'     => 'voice_agent',
            ]);
        }

        // İlgilendiği ilanı bul
        $listing = null;
        if (!empty($data['interested_listing_ref'])) {
            $listing = Listing::where('reference_no', $data['interested_listing_ref'])->first();
        }

        // Aynı kontak için son 24 saatte oluşturulan açık lead varsa onu kullan (dedup)
        $lead = Lead::where('contact_id', $contact->id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where('created_at', '>', now()->subDay())
            ->first();

        if (!$lead) {
            $lead = Lead::create([
                'office_id'   => $data['office_id'],
                'contact_id'  => $contact->id,
                'status'      => 'new',
                'source_type' => 'voice_agent',
                'notes'       => $data['notes'] ?? null,
                'assigned_to' => $listing?->agent_id,
                'listing_id'  => $listing?->id,
                'metadata'    => [
                    'voice_agent_intent' => $data['intent'] ?? null,
                    'budget'             => $data['budget'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'lead_id'    => $lead->id,
            'contact_id' => $contact->id,
            'listing_id' => $listing?->id,
            'message'    => 'Müşteri kaydedildi.',
        ]);
    }

    /**
     * "Beni insanla görüştür" — moda göre nereye bağlanacağını döner.
     *
     * POST /api/voice-agent/tools/request-transfer
     * Body: { office_id, caller_phone, listing_ref?, lead_id? }
     * Resp: { action: 'transfer'|'callback'|'voicemail', phone?, reason, message }
     */
    public function requestTransfer(Request $request, TransferRouter $router): JsonResponse
    {
        $data = $request->validate([
            'office_id'    => 'required|integer|exists:offices,id',
            'caller_phone' => 'required|string|max:30',
            'listing_ref'  => 'nullable|string|max:50',
            'lead_id'      => 'nullable|integer|exists:leads,id',
        ]);

        return response()->json($router->resolve($data));
    }

    /**
     * Çağrı bağlanmadan önce danışmana Telegram brifingi at.
     *
     * POST /api/voice-agent/tools/pre-call-brief
     * Body: { office_id, target_user_id?, target_phone?, caller_phone, listing_ref?, lead_id?, summary }
     */
    public function preCallBrief(Request $request, PreCallBriefService $brief): JsonResponse
    {
        $data = $request->validate([
            'office_id'      => 'required|integer|exists:offices,id',
            'target_user_id' => 'nullable|integer|exists:users,id',
            'target_phone'   => 'nullable|string|max:30',
            'caller_phone'   => 'required|string|max:30',
            'listing_ref'    => 'nullable|string|max:50',
            'lead_id'        => 'nullable|integer|exists:leads,id',
            'summary'        => 'required|string|max:1000',
        ]);

        $result = $brief->dispatch($data);

        return response()->json($result);
    }

    /**
     * Bağlama yapılamadığında / mesai dışında — geri arama randevusu al.
     *
     * POST /api/voice-agent/tools/book-callback
     * Body: { office_id, caller_phone, caller_name?, requested_at?, intent?, notes? }
     */
    public function bookCallback(Request $request, TelegramService $telegram): JsonResponse
    {
        $data = $request->validate([
            'office_id'    => 'required|integer|exists:offices,id',
            'caller_phone' => 'required|string|max:30',
            'caller_name'  => 'nullable|string|max:120',
            'requested_at' => 'nullable|date',
            'intent'       => 'nullable|string|max:60',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $when = isset($data['requested_at'])
            ? \Carbon\Carbon::parse($data['requested_at'])
            : now()->addHours(2);

        $task = Task::create([
            'title'       => 'Sesli AI: Geri arama — ' . ($data['caller_name'] ?? $data['caller_phone']),
            'description' => "Telefon: {$data['caller_phone']}\nNiyet: " . ($data['intent'] ?? '-') . "\nNot: " . ($data['notes'] ?? '-'),
            'type'        => 'call',
            'priority'    => 'high',
            'status'      => 'pending',
            'reminder_at' => $when,
            'due_date'    => $when->toDateString(),
            'due_time'    => $when,
            'metadata'    => [
                'source'       => 'voice_agent',
                'caller_phone' => $data['caller_phone'],
                'intent'       => $data['intent'] ?? null,
                'office_id'    => $data['office_id'],
            ],
        ]);

        // Ofis Telegram kanalına bildirim
        $config = VoiceAgentConfig::where('office_id', $data['office_id'])->first();
        if ($config && $config->telegram_office_channel) {
            $msg = "📞 *Sesli AI — Geri arama randevusu*\n"
                 . "👤 " . ($data['caller_name'] ?? 'İsimsiz') . "\n"
                 . "📱 {$data['caller_phone']}\n"
                 . "🕒 " . $when->format('d.m.Y H:i') . "\n"
                 . "📝 " . ($data['notes'] ?? '-');
            try {
                $telegram->sendMessage($config->telegram_office_channel, $msg);
            } catch (\Throwable $e) {
                Log::warning('Voice agent callback Telegram notify failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'task_id' => $task->id,
            'when'    => $when->toIso8601String(),
            'message' => 'Geri arama randevusu alındı.',
        ]);
    }

    protected function formatListing(Listing $l): array
    {
        return [
            'id'           => $l->id,
            'reference_no' => $l->reference_no,
            'title'        => $l->title,
            'type'         => $l->listing_type, // sale / rent / daily_rent
            'price'        => $l->price ? (int) $l->price : null,
            'currency'     => $l->price_currency ?: 'TRY',
            'rooms'        => $l->room_count . '+' . ($l->living_room_count ?? 1),
            'sqm'          => $l->gross_sqm,
            'city'         => $l->city,
            'district'     => $l->district,
            'neighborhood' => $l->neighborhood,
            'floor'        => $l->floor_number,
            'age'          => $l->building_age,
            'spoken'       => $this->toSpokenSummary($l),
        ];
    }

    /**
     * Konuşulabilir cümle özet — agent direkt müşteriye okuyabilsin.
     */
    protected function toSpokenSummary(Listing $l): string
    {
        $parts = [];
        if ($l->district || $l->city) {
            $parts[] = trim(($l->district ? $l->district . ', ' : '') . ($l->city ?? ''));
        }
        if ($l->room_count) {
            $parts[] = $l->room_count . '+' . ($l->living_room_count ?? 1);
        }
        if ($l->gross_sqm) {
            $parts[] = $l->gross_sqm . ' metrekare';
        }
        if ($l->price) {
            $parts[] = number_format($l->price, 0, ',', '.') . ' ' . ($l->price_currency ?: 'TL')
                . ($l->listing_type === 'rent' ? ' kira' : '');
        }
        return implode(', ', $parts);
    }
}
