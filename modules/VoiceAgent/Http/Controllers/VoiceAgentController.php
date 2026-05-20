<?php

namespace Modules\VoiceAgent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\VoiceAgent\Models\VoiceAgentConfig;

class VoiceAgentController extends Controller
{
    public function index(Request $request)
    {
        $officeId = $request->user()->office_id;

        $config = $officeId
            ? VoiceAgentConfig::firstOrNew(['office_id' => $officeId])
            : new VoiceAgentConfig();

        if (!$config->exists) {
            $config->fill([
                'routing_mode'         => VoiceAgentConfig::MODE_LISTING_OWNER_FIRST,
                'ring_timeout_seconds' => 15,
                'business_hours_start' => '09:00',
                'business_hours_end'   => '19:00',
                'language'             => 'tr',
            ]);
        }

        return view('voice-agent::index', [
            'config'         => $config,
            'modeOptions'    => $this->modeOptions(),
            'defaultPrompt'  => $this->defaultPrompt(),
            'sharedSecret'   => config('services.voice_agent.shared_secret'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'is_active'               => 'sometimes|boolean',
            'elevenlabs_agent_id'     => 'nullable|string|max:191',
            'default_voice_id'        => 'nullable|string|max:191',
            'routing_mode'            => 'required|in:' . implode(',', VoiceAgentConfig::MODES),
            'secretary_phone'         => 'nullable|string|max:30',
            'default_agent_phone'     => 'nullable|string|max:30',
            'ring_timeout_seconds'    => 'required|integer|min:5|max:60',
            'business_hours_start'    => 'required|date_format:H:i',
            'business_hours_end'      => 'required|date_format:H:i',
            'weekend_active'          => 'sometimes|boolean',
            'timezone'                => 'required|string|max:60',
            'system_prompt'           => 'nullable|string|max:8000',
            'greeting_template'       => 'nullable|string|max:500',
            'language'                => 'required|string|max:5',
            'telegram_office_channel' => 'nullable|string|max:30',
        ]);

        $data['is_active']      = (bool) ($data['is_active']      ?? false);
        $data['weekend_active'] = (bool) ($data['weekend_active'] ?? false);

        $officeId = $request->user()->office_id;
        if (!$officeId) {
            return back()->withErrors(['office' => 'Önce bir ofise atanmış kullanıcı olmalısınız.']);
        }

        VoiceAgentConfig::updateOrCreate(
            ['office_id' => $officeId],
            $data,
        );

        return back()->with('success', 'Sesli AI ayarları kaydedildi.');
    }

    protected function modeOptions(): array
    {
        return [
            VoiceAgentConfig::MODE_LISTING_OWNER_FIRST => [
                'label'       => 'Önce ilan danışmanı → sonra sekreter (önerilen)',
                'description' => 'Müşteri belli bir ilana ilgi gösterirse önce o ilanın danışmanını çağırır, açmazsa sekretere düşer, o da açmazsa randevu alır.',
            ],
            VoiceAgentConfig::MODE_SECRETARY_ONLY => [
                'label'       => 'Her zaman sekretere',
                'description' => 'Klasik santral. Sekreter doğru kişiye yönlendirir.',
            ],
            VoiceAgentConfig::MODE_LISTING_OWNER_ONLY => [
                'label'       => 'Her zaman ilan danışmanına',
                'description' => 'Küçük ofis, tek/az danışman. Direkt ilgili danışmana bağlanır.',
            ],
            VoiceAgentConfig::MODE_CALLBACK_ONLY => [
                'label'       => 'Sadece geri arama randevusu (düşük tier)',
                'description' => 'Hiç kimseye bağlama — AI her şeyi konuşur, müsait olduğunda danışman geri arar.',
            ],
        ];
    }

    protected function defaultPrompt(): string
    {
        return <<<'TXT'
Sen {{office.name}} emlak ofisinin Türkçe konuşan sesli AI sekreterisin. Görevin müşterileri profesyonel ve sıcak bir tonla karşılamak, niyetlerini anlamak ve doğru aksiyonu almak.

Yapabileceklerin:
1. Müşteri belli bir ilan referans numarası, mülk tipi (daire/villa/dükkan), lokasyon (şehir/ilçe/mahalle), bütçe veya oda sayısı belirtirse → search_listing tool'unu kullan, sonuçları sözlü olarak anlat (3'ten fazla varsa en uygun 3'ünü).
2. Müşteri bir mülkle ilgilenir, görme/teklif/bilgi almak isterse → create_lead tool'unu çağır (telefon + niyet + ilan referansı + bütçe + notlar).
3. Müşteri "danışmanla görüşmek istiyorum / insanla bağlanmak istiyorum" dediğinde:
   a. Önce pre_call_brief ile danışmana 5 saniye önceden Telegram brifingi at
   b. request_transfer ile sistemden bağlanılacak numarayı al ve müşteriyi bağla
4. Bağlanma mümkün değilse veya mesai dışıysa → book_callback ile geri arama randevusu al, müsait saat sor.

Konuşma kuralları:
- Türkçe konuş, kısa cümleler kur, ofisin profesyonel tonu olsun.
- Müşteriyi bekletme — tool çağrıları sırasında "Hemen kontrol ediyorum" gibi köprü cümleler kullan.
- Asla yanıltıcı fiyat/bilgi verme — DB'den çekmediysen "bunu danışmanım bilir, hemen bağlayayım" de.
- Çağrı bittikten sonra her şey otomatik özetlenir, sen tekrar özetleme.

Kibar ve net ol. Müşteriye değer ver, ofis itibarını koru.
TXT;
    }
}
