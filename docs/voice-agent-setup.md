# Sesli AI Sekreter — Kurulum Rehberi

> Bu rehber, RE-OS'un sesli AI sekreter özelliğini canlıya almak için adım adım yapılacakları içerir.
>
> Toplam süre: ~30-45 dakika. Bunun çoğu ElevenLabs ve Netgsm tarafında.

## Ön koşullar

- [ ] **ElevenLabs hesabı** — Conversational AI (Agents) özelliği aktif olmalı. Free plan'da yok; **Creator** veya **Pro** plan gerekir.
- [ ] **Netgsm hattı** — sabit hat numaran olmalı + Netgsm panelden SIP erişimi açık olmalı. (Burak: bayi olduğun için bu konfigürasyona aşinasın.)
- [ ] **HTTPS public URL** — RE-OS'un dış dünyadan erişilebilir adresi (ngrok, cloudflare tunnel veya prod domain).
- [ ] RE-OS'ta `php artisan migrate` koşulmuş (voice_agent_configs tablosu kurulmuş olmalı).

---

## 1. Adım — `.env` Değişkenleri

`.env` dosyasına ekle ve `php artisan config:clear` çalıştır:

```env
# ElevenLabs Conversational AI
ELEVENLABS_API_KEY=sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
ELEVENLABS_DEFAULT_VOICE_ID=EXAVITQu4vr4xnSDxMaL   # Türkçe iyi konuşan bir ses (opsiyonel — admin paneldekiyle override edilir)

# Netgsm voice / telekom
NETGSM_USERCODE=xxxxxxxxxx
NETGSM_PASSWORD=xxxxxxxxxx
NETGSM_SENDER_ID=YOURBRAND           # Netgsm onayladığın başlık
NETGSM_DEFAULT_NUMBER=905555555555   # Outbound aramalarda görünen no

# Sesli ajan güvenlik
VOICE_AGENT_SHARED_SECRET=cok-uzun-rastgele-bir-deger
# Bu değeri ElevenLabs Agent dashboard'da "Authorization Header" olarak gireceksin.
# Üretmek için: php -r "echo bin2hex(random_bytes(32));"

# Voice agent webhook URL (post-call)
VOICE_AGENT_WEBHOOK_URL=https://senin-domain.com/api/voice-agent/webhook
```

---

## 2. Adım — RE-OS Admin Paneli

1. `/admin/ai/voice-agent`'i aç.
2. **Routing modu** seç (önerilen: "Önce ilan danışmanı → sonra sekreter").
3. **Sekreter telefonu** ve **varsayılan danışman telefonu** gir (E.164 formatı: +90...).
4. **Mesai saatleri** ayarla (mesai dışı çağrılar otomatik randevu modeline geçer).
5. **Karşılama cümlesini** yaz: örnek "Merhaba, X Emlak'a hoş geldiniz. Size nasıl yardımcı olabilirim?"
6. **Sistem promptunu** kontrol et — varsayılan Türkçe profesyonel emlak promptu hazır. İstersen düzenle.
7. **Ofis Telegram kanal ID'sini** gir — pre-call brifing ve post-call özetler buraya düşecek. (Telegram bot ofise eklenmiş ve `/start KOD` ile bağlanmış olmalı.)
8. "Yayın Durumu"nu **Aktif** yap → Kaydet.

Bu aşamada **Agent ID** alanı henüz boş — ElevenLabs'tan dönüşte dolduracağız.

---

## 3. Adım — ElevenLabs Conversational Agent Kurulumu

### 3.1 — Yeni Agent oluştur

1. https://elevenlabs.io/app/conversational-ai → "**Create Agent**"
2. **Name:** "RE-OS — [Ofis Adı]"
3. **First message:** Admin panelde girdiğin karşılama cümlesi
4. **System prompt:** Admin paneldeki **Sistem Promptu** alanını oraya yapıştır
5. **Voice:** Türkçe iyi konuşan bir ses seç (örn. "Bella Turkish", "Antoni Multilingual"). Voice ID'yi admin paneldeki "Varsayılan ses" alanına geri yapıştır.
6. **Language:** Turkish (tr)
7. **LLM model:** `gpt-4o` veya `gpt-4o-mini` (mini daha hızlı + ucuz, emlak senaryosu için yeter)
8. **Max conversation duration:** 10 dakika (önerilen tavan)

### 3.2 — Tools ekle (5 adet)

ElevenLabs Agent panelinde **Tools** sekmesi → "**Add Tool**" → **Webhook**.

#### Tool 1: search_listing
```
Name:        search_listing
Description: Search property listings in the database. Returns up to 5 matching listings with summary info to speak aloud.
URL:         https://senin-domain.com/api/voice-agent/tools/search-listing
Method:      POST
Headers:     X-Voice-Agent-Token: <VOICE_AGENT_SHARED_SECRET değeri>
Parameters:
  query (string, required) — User's natural language query (e.g., "Bağdat Caddesi 2+1 kiralık")
  office_id (number, required) — Office ID, always "1" for this agent
  limit (number, optional) — Max results, default 5
```

#### Tool 2: create_lead
```
Name:        create_lead
Description: Save the caller as a new lead with their interest, budget, and contact info.
URL:         https://senin-domain.com/api/voice-agent/tools/create-lead
Method:      POST
Headers:     X-Voice-Agent-Token: <secret>
Parameters:
  office_id (number, required)
  caller_phone (string, required)
  caller_name (string, optional)
  interested_listing_ref (string, optional) — Property reference number if specific
  intent (string, optional) — One of: buy, rent, sell, inquiry, follow_up, other
  budget (number, optional)
  notes (string, optional) — Free-text summary of what the caller wants
```

#### Tool 3: request_transfer
```
Name:        request_transfer
Description: Decide who to transfer the call to based on the office's routing mode. Returns a phone number to transfer to, or instructs to take a callback.
URL:         https://senin-domain.com/api/voice-agent/tools/request-transfer
Method:      POST
Headers:     X-Voice-Agent-Token: <secret>
Parameters:
  office_id (number, required)
  caller_phone (string, required)
  listing_ref (string, optional)
  lead_id (number, optional) — Use lead_id returned from create_lead
```

#### Tool 4: pre_call_brief
```
Name:        pre_call_brief
Description: Send a Telegram briefing to the target agent BEFORE transferring the call, so they know who is calling and why. Call this just before request_transfer.
URL:         https://senin-domain.com/api/voice-agent/tools/pre-call-brief
Method:      POST
Headers:     X-Voice-Agent-Token: <secret>
Parameters:
  office_id (number, required)
  target_user_id (number, optional)
  target_phone (string, optional)
  caller_phone (string, required)
  listing_ref (string, optional)
  lead_id (number, optional)
  summary (string, required) — 2-3 sentence summary of the conversation so far
```

#### Tool 5: book_callback
```
Name:        book_callback
Description: Schedule a callback when transfer isn't possible (after hours, no one available, or callback-only mode).
URL:         https://senin-domain.com/api/voice-agent/tools/book-callback
Method:      POST
Headers:     X-Voice-Agent-Token: <secret>
Parameters:
  office_id (number, required)
  caller_phone (string, required)
  caller_name (string, optional)
  requested_at (string, optional) — ISO8601 datetime preferred by caller
  intent (string, optional)
  notes (string, optional)
```

### 3.3 — Post-call Webhook

ElevenLabs Agent → **Settings** → **Webhooks** → **Post-call Webhook**

```
URL:     https://senin-domain.com/api/voice-agent/webhook
Method:  POST
Headers: X-Voice-Agent-Token: <secret>
Payload: Default (ElevenLabs full conversation payload)
```

### 3.4 — Agent ID'yi kaydet

ElevenLabs'ta agent kurulduktan sonra **Agent ID**'yi kopyala (agent_xxxxxxxxxxxxxxxxx şeklinde).

RE-OS'ta `/admin/ai/voice-agent` → **ElevenLabs Agent ID** alanına yapıştır → Kaydet.

---

## 4. Adım — Netgsm SIP Routing

> Bu kısım Netgsm panelinde yapılır. Sen bayisi olduğun için aşinasın, sadece ana hatları yazıyorum.

1. Netgsm Müşteri Paneli → **Santral / IVR yönlendirme**.
2. Sabit hattını seç → **Gelen Çağrı Yönlendirme** → **SIP'e yönlendir**.
3. SIP hedef adresi: ElevenLabs Agent'ın verdiği SIP URL'si (Agent settings → Telephony → Twilio SIP).
4. ElevenLabs'ta **Twilio SIP integration** kurulumu gerekir — alternatif olarak ElevenLabs'ın yerel **Phone Number** özelliği de Türkiye numarası kabul etmeye başladıysa Twilio'yu atlayabilirsin.

> ⚠️ Netgsm SIP trunk + ElevenLabs eşleştirmesi henüz olgun değil. Eğer doğrudan eşleşmezse middleware bir Twilio SIP trunk (orta katman) gerekir. Bu kısım için Netgsm destekle birlikte yapılmalı.

**Alternatif (basit):** Netgsm aramayı **callback URL**'e yönlendirir → bizim sistem ElevenLabs Agent'ı **outbound** olarak müşteriye geri arar. Bu yaklaşım daha basit ama müşteriye 5-10 saniye gecikme olur.

---

## 5. Adım — Test

### 5.1 — Tool endpoint'lerini doğrudan test

```bash
curl -X POST https://senin-domain.com/api/voice-agent/tools/search-listing \
  -H "X-Voice-Agent-Token: <secret>" \
  -H "Content-Type: application/json" \
  -d '{"query":"Bağdat Caddesi 2+1","office_id":1}'
```

Yanıt: `{"count": N, "listings": [...]}`.

### 5.2 — ElevenLabs Agent simülatörü

ElevenLabs Agent panelinde **"Test Conversation"** butonu var — tarayıcıdan mikrofonla konuş, agent'i test et. Tool çağrılarının çalıştığını **Logs** sekmesinden göreceksin.

### 5.3 — Gerçek çağrı

Netgsm hattını ara → Türkçe konuş → "Bağdat Caddesi 2+1 daire arıyorum" → agent ilanı okumalı, "ilgileniyorum" deyince lead oluşturmalı, "danışmanla görüşmek istiyorum" deyince Telegram brifing + bağlama olmalı.

---

## Sorun giderme

| Belirti | Olası neden |
|---------|------------|
| Agent tool çağırınca 401 dönüyor | `VOICE_AGENT_SHARED_SECRET` ile ElevenLabs header değeri eşleşmiyor |
| `request_transfer` her zaman callback dönüyor | Mesai saati dışındasın **veya** secretary/agent phone boş **veya** is_active = false |
| Pre-call brifing Telegram'a düşmüyor | `telegram_office_channel` boş **veya** danışmanın `TelegramUser` kaydı yok / pairing yapmamış |
| Çağrı bitince Activity oluşmuyor | Webhook URL yanlış / shared_secret mismatch — `storage/logs/laravel.log`'a bak |
| Türkçe ses kalitesi zayıf | ElevenLabs'ta `eleven_multilingual_v2` model ve Türkçe-optimized voice seç (örn. Bella, Domi multi) |

---

## Maliyet özeti (yaklaşık aylık)

- ElevenLabs Creator plan: $22/ay → 100k karakter STT/TTS + Agents
- ElevenLabs Pro plan: $99/ay → 500k karakter + öncelikli destek
- Netgsm hat: ~50-100 TL/ay + dakika başı
- OpenAI gpt-4o-mini (Agent LLM): ~$0.15/M token → 1000 çağrı/ay ≈ $5-10

Bayilik teklif paketinizde bu maliyetler + margin = aylık fiyat.

---

## İlgili dosyalar

- `modules/VoiceAgent/Http/Controllers/ToolController.php` — Tool endpoint logic
- `modules/VoiceAgent/Http/Controllers/WebhookController.php` — Post-call webhook handler
- `modules/VoiceAgent/Services/TransferRouter.php` — Routing decision logic
- `modules/VoiceAgent/Services/PreCallBriefService.php` — Pre-call Telegram brief
- `modules/VoiceAgent/Models/VoiceAgentConfig.php` — Office-level config
- `config/services.php` → `voice_agent` section
- `.env` → ELEVENLABS_*, NETGSM_*, VOICE_AGENT_*
