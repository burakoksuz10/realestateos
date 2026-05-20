# RE-OS Master Roadmap & Progress

> **Vizyon:** Bu projeyi guzllik'in salon operasyonları için yaptığını emlak için yapan, dünyanın en gelişmiş AI destekli emlak operasyon platformuna dönüştürmek. Her iş fonksiyonu (lead → görüntüleme → teklif → kapanış → satış sonrası) tek platformda akacak. AI her noktada operasyonel yardım sunacak, Telegram saha danışmanlarını gerçek zamanlı bağlayacak, sosyal medya motoru pazarlamayı otomatikleştirecek.

**Stratejik 3 sütun:**
- **A) AI Co-pilot her yerde** — lead zekası, ilan zekası, içerik üretimi, karar destek
- **B) Telegram operasyon merkezi** — bildirimler, /komutlar, sabah brifingi, foto/ses → CRM
- **C) Sosyal medya motoru** — AI foto iyileştirme, markalı kartlar, çoklu platform yayın

---

## 📊 Genel Durum Özeti

| Faz | Başlık | Durum | Not |
|-----|--------|-------|-----|
| 0 | Altyapı | ✅ Tamam | OpenAI servis, kredi sistemi, ayarlar paneli, async jobs |
| 1 | AI Lead & İlan Zekası | ✅ Tamam | Gerçek OpenAI bağlandı, sidebar Copilot çalışıyor |
| 2 | Telegram Operasyon Merkezi | ✅ Kod tamam | Komutlar/bildirimler/brifing/foto-ses/butonlar canlı — sadece bot token + webhook URL bekliyor |
| 3 | Sosyal Medya Motoru | ✅ Çekirdek tamam | Fal.ai (sky/twilight/declutter/staging/enhance), markalı kart üretici (4 şablon × 3 boyut), ilandan içerik akışı, takvim, hashtag intelligence |
| 4 | İletişim & Yaşam Döngüsü | ✅ Tamam | Unified Inbox + Drip Campaign engine + **Sesli AI Sekreter** (ElevenLabs Agents + Netgsm — bayi paketinin amiral özelliği). Drip cold-lead reactivation + görsel step builder Faz 5'e devir. |
| 5 | Süreç Otomasyon | ✅ Tamam | Pipeline auto-actions executor (6 tip) + stage UI builder, takılan deal uyarıları (`deals:stalled` günlük 09:00), AI Günlük Plan (Copilot daily), Performans dashboard (BI), AI Emlak Uzmanı (prensip tabanlı) |
| 6 | İleri Özellikler | ✅ Çekirdek tamam | AI Broşür PDF + Portal Sync (Sahibinden/Hepsiemlak/EmlakJet, per-office credentials altyapısı) + KVKK Doküman Yönetimi + **AI ile URL'den ilan içe aktarma** (yıldız özellik). E-imza/TKGM/müşteri portalı bilinçli olarak atlandı. |
| 7 | Cila & Üretim | ⏳ Bekliyor | PWA, performans, test, güvenlik, çoklu dil |

**İlerleme:** 6/8 faz (Faz 0+1+2+3+4+5+6 kod tam — Faz 2/4.3/6 sadece ENV key'leri bekliyor; Faz 7 (cila & üretim) sıradaki).

---

## ✅ FAZ 0 — Altyapı (TAMAMLANDI)

Amaç: Her şeyin temelini oluşturacak servisler ve veri yapıları.

### Tamamlanan işler

- [x] `config/reos.php` → varsayılan model `gpt-4o`, pricing tablosu, kredi yapılandırması
- [x] `app/Providers/AppServiceProvider.php` → OpenAI client singleton binding
- [x] **Migration'lar:**
  - [x] `ai_usage_logs` — her AI çağrısı (model, token, maliyet, latency, status)
  - [x] `ai_credits` — office bazlı aylık kredi takibi
  - [x] `ai_settings` — office bazlı API key override (encrypted)
- [x] **Modeller:** `App\Models\AiUsageLog`, `AiCredit`, `AiSetting`
- [x] **AiCreditService** — `consume`, `hasAvailable`, `grantExtra`, otomatik aylık rollover
- [x] **AIService refactor:** placeholder facade kaldırıldı, raw OpenAI client, usage logging, cost estimation, cache layer, kredi check
- [x] **Async Jobs altyapısı:** `AnalyzeLeadJob`, `GenerateListingDescriptionJob`, `ValuateListingJob`

### Sonraki oturuma kalan ufak işler
- [ ] Queue worker'ı systemd/supervisor ile production-ready hale getir
- [ ] Failed job UI (admin'de görüntüleme)
- [ ] Aylık kredi reset için scheduled task (`schedule:run`)

---

## ✅ FAZ 1 — AI Lead & İlan Zekası (TAMAMLANDI)

Amaç: CRM'in kalbini gerçek AI ile çalıştır.

### Tamamlanan işler

- [x] **Yeni lead → otomatik AI analizi** — Lead modelinde `created` event'ında `AnalyzeLeadJob` dispatch
  - Sonuç: `ai_score`, `ai_analysis`, `ai_suggestions`, `intent_signals` otomatik dolar
- [x] **ContentController** gerçek `ContentService` çağırıyor
  - description, social, ads, headlines, improve — hepsi gerçek OpenAI
  - Çoklu dil desteği: TR, EN, RU, AR, DE, FR
  - Async mode: `?async=true` parametresi ile queue'ya at
- [x] **ValuationController** gerçek `ValuationService` çağırıyor
  - Komparablar, market trend, ROI analizi, AI yorumu
  - Async + sync mod
- [x] **CopilotController** gerçek OpenAI ile
  - `/chat` — kullanıcı sorularına yanıt
  - `/lead-suggestions` — lead için sonraki adım önerileri
  - `/analyze-call` — transcript özetleme
  - `/search` — doğal dil ilan arama
- [x] **AI Settings panel** (`/admin/ai/settings`)
  - API key girişi (encrypted)
  - Model seçimi (gpt-4o / gpt-4o-mini / gpt-4-turbo)
  - Aylık kredi limiti
  - Özellik bazlı açma/kapama
  - Aylık kullanım istatistikleri (token, maliyet, çağrı sayısı)
  - Bağlantı testi butonu
  - Bonus kredi ekleme
- [x] **Floating AI Copilot widget** — her admin sayfasının sağ alt köşesi
  - Hızlı başlatma soruları
  - Geçmiş takibi (son 10 mesaj)
  - Animasyonlu loading
- [x] **Sidebar:** AI Ayarları + Telegram linki eklendi

### Sonraki oturuma kalan ufak işler
- [x] Lead detay sayfasında "AI Analizi" kartı (mevcut `ai_analysis` JSON'u görsel olarak göster) — `modules/CRM/Resources/views/leads/partials/ai-analysis-card.blade.php` + `admin.leads.reanalyze` route
- [x] İlan detay sayfasında "AI Değerleme" kartı + "Açıklama Üret" butonu — `modules/RealEstate/Resources/views/listings/partials/ai-valuation-card.blade.php` (mevcut `admin.ai.valuation.generate` ve `admin.ai.content.description` route'ları kullanılıyor)
- [ ] Copilot widget'ında "function calling" — gerçek lead/ilan verisi çekmek için tool use

---

## ✅ FAZ 2 — Telegram Operasyon Merkezi (KOD TAMAM)

Amaç: Saha danışmanları gerçek zamanlı operasyonda olsun.

### Tamamlanan işler

- [x] **`modules/Telegram/` modülü** — service provider, routes, migrations, observer + command registration
- [x] **`telegram_users` tablosu** — kullanıcı-bot eşleştirme + pairing code akışı
- [x] **TelegramService** — sendMessage, sendPhoto, sendMessageWithButtons, answerCallback, downloadFile, notifyUser, notifyOffice, setWebhook, generatePairingCode, completePairing
- [x] **TelegramController** — `/admin/telegram` sayfası, pairing kod oluştur, bağlantı kaldır
- [x] **WebhookController** — komutları + callback + media handler'lara delege ediyor
- [x] **Bağlama akışı:** UI'dan kod al → Telegram'da bot'a `/start KOD` → bağlandı
- [x] **CommandHandler** — komutlar bir yerde toplandı, yetkilendirme tek noktada
  - `/start KOD`, `/help`
  - `/today` — bugünkü görevler + sıcak lead özeti
  - `/leads` — aktif lead listesi
  - `/hot` — skor ≥ 80 olan lead'ler (kontakt + sinyaller dahil)
  - `/search KRİTER` — `MatchingService::semanticSearch` ile doğal dil ilan arama
  - `/listing REF` — ilan kartı (foto varsa caption ile fotoğraf, yoksa metin)
- [x] **CallbackHandler** — interactive button'lar: `lead.assign`, `lead.task`, `lead.open`
- [x] **MediaIngestService** — agent bot'a foto/ses/video/dosya gönderince son aktif lead'e activity olarak düşer; ses Whisper ile transkript edilir
- [x] **Bildirim observer'ları (event'siz):**
  - `LeadObserver` — yeni lead atama + hot lead alarmı (cold/warm → hot geçişi)
  - `DealObserver` — stage değişimi + deal kazanıldı/kaybedildi (agent + ofis broadcast)
- [x] **Sabah brifingi:** `telegram:morning-briefing` console command — her gün 08:30 (Europe/Istanbul), kişiselleştirilmiş (görevler + sıcak lead + uzun süredir hareketsiz lead)
- [x] **Görev hatırlatması:** `telegram:task-reminders` — her 5 dk, `reminder_at` yaklaşan görevler

### Sonraki oturuma kalan ufak işler

- [ ] **`.env`'e ekle (kullanıcı tarafı):** `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME` (BotFather'dan)
- [ ] **Webhook public URL:** ngrok / cloudflare tunnel ile lokali expose et, `/admin/telegram` → "Webhook'u ayarla" butonuna bas
- [ ] **Ofis kanalı (manager view)** — `notifyOffice` mevcut; cron'a "günlük ofis özeti" ekle (Faz 5'e bırakılabilir)
- [ ] **Lead/Deal event class'ları** — `Modules\CRM\Events\LeadCreated/Updated/Converted` ve `DealCreated/StageChanged/DealClosed` boş stub'lar; `Lead::created`'da `event(new LeadCreated())` çağrısı runtime'da fail eder. Şu an observer akışı bağımsız çalışıyor ama broader CRM tarafı için bunlar bir gün doldurulmalı (kapsam dışı bug)

---

## ✅ FAZ 3 — Sosyal Medya Motoru (ÇEKİRDEK TAMAM)

Amaç: Pazarlama otomasyonu — AI ilanları sosyal medyaya hazır hale getirsin.

### Tamamlanan işler

- [x] **Fal.ai entegrasyonu** — `modules/AI/Services/FalAiService.php`
  - skyReplacement, twilight, declutter, virtualStaging, enhance
  - Per-office usage logging (AiUsageLog feature=`image.{operation}`)
  - Config: `config/services.php` (fal.api_key, base_url) + `config/reos.php` (ai.image.models, timeout)
  - UI: Yeni gönderi modal'ında media URL girilince 5 buton (Gökyüzü / Twilight / Temizle / Staging / Netleştir)
- [x] **Markalı sosyal kart üretici** — `modules/SocialMedia/Services/SocialCardService.php`
  - Intervention/Image v3 (GD) ile
  - 4 şablon: `yeni_ilan`, `satildi`, `acik_ev`, `fiyat_indirimi`
  - 3 boyut: square 1080×1080, story 1080×1920, landscape 1200×630
  - DejaVuSans TTF (dompdf vendor) — Türkçe karakter desteği
  - Listing primary photo + gradient overlay + badge + başlık + lokasyon + fiyat + ofis adı
  - `storage/app/public/social-cards/` altına JPG kaydeder, `/storage/...` URL döner
- [x] **AI caption üretici (multiplatform)** — `ContentService::generateSocialContent` UI'a bağlı (instagram/facebook/twitter/linkedin/story)
- [x] **Reels/TikTok script üretici** — `ContentService::generateReelsScript` aynı modal'da
- [x] **İçerik takvimi** — `/admin/social-media/calendar` aylık grid view (Pzt başlangıç)
  - Geri/ileri ay gezme
  - Günde 3 post + count badge, status renk kodlu
- [x] **Hashtag intelligence** — `/ai/hashtags` endpoint
  - Bölge (city/district) + ilan tipi tabanlı
  - 20 hashtag default, JSON mode (response_format: json_object)
  - Caption'a inline ekleme / kopyalama UI
- [x] **Listing Studio Modal** — header "İlandan Oluştur" butonu, 3 sekme (İçerik / Kart / Hashtag) tek yerden

### Geriye kalan (Faz 4'e devir veya ayrı)

- [ ] **Çoklu platform yayın** — gerçek Meta Graph API / X / LinkedIn entegrasyonu (Faz 4 İletişim ile birlikte)
- [ ] **Postiz entegrasyonu** (alternatif — postiz-app çalışıyorsa)
- [ ] **Drag-drop calendar** — şu an statik aylık grid, drag-drop ile rescheduling
- [ ] **Performans takibi** — like, share, comment, conversion (yayın API'leri canlı olunca)
- [ ] **Unified inbox** — Instagram DM + Messenger + WhatsApp + Telegram (Faz 4)

### Kullanılacak modüller
- `modules/SocialMedia/` — UI + servis + controller doldu
- `modules/AI/` — FalAiService eklendi
- `modules/Advertising/` — Meta Ads entegrasyonu (Faz 4/6'da)

### Kurulum notu
- Fal.ai için `.env`'e `FAL_API_KEY=...` ekle (https://fal.ai/dashboard/keys)
- Sosyal kart için `php artisan storage:link` çalışmış olmalı (otomatik yapıldı)

---

## 🟡 FAZ 4 — İletişim & Yaşam Döngüsü (4.1 TAMAM)

Amaç: Tüm iletişim kanalları tek inbox'ta, müşteri yaşam döngüsü otomatik.

### 4.1 Unified Inbox — TAMAM ✅

**Tamamlanan (backend):**
- [x] `database/migrations/2026_05_20_200000_create_conversations_table.php` — kanal-agnostik thread tablosu (office, contact, lead, channel, channel_thread_id, status, unread_count, last_message_*, meta)
- [x] `database/migrations/2026_05_20_200001_create_messages_table.php` — direction, channel, external_id, body, attachments, sent_by_user_id, AI alanları (summary/sentiment/intent), status, read_at
- [x] `modules/CRM/Models/Conversation.php` — messages/contact/lead/assignee relations + `markAsRead()` + `touchLastMessage(Message)` + `scopeOpen`/`scopeForChannel`
- [x] `modules/CRM/Models/Message.php` — conversation/sentByUser relations + isInbound/isOutbound
- [x] `modules/Integrations/Channels/ChannelInterface.php` — `name()`, `isEnabled()`, `send(Conversation, body, attachments, userId)`
- [x] `modules/Integrations/Channels/ChannelManager.php` — singleton register/get/all (Application container'dan resolve)
- [x] `modules/Integrations/Channels/TelegramChannel.php` — TelegramService'i sarmalar (text + foto, ilk attachment)
- [x] `modules/Integrations/Channels/WhatsAppChannel.php` — WhatsAppConnector sarmalar
- [x] `modules/Integrations/Channels/SmsChannel.php` — SMSConnector sarmalar
- [x] `modules/Integrations/Channels/EmailChannel.php` — Mail::raw ile minimal SMTP
- [x] `IntegrationsServiceProvider::register()` — ChannelManager singleton + 4 kanal register
- [x] `modules/CRM/Http/Controllers/InboxController.php` — index (filtreler: channel, status), show (markAsRead), send, assign, updateStatus, authorizeView (office isolation)
- [x] CRM `Routes/web.php` — `GET /admin/inbox`, `GET /admin/inbox/{conv}`, `POST .../send`, `.../assign`, `.../status`

**Tamamlanan (UI + ingest):**
- [x] **`modules/CRM/Resources/views/inbox/index.blade.php`** — sol panel: durum tab'ları (Açık/Arşiv/Kapalı) + count, kanal chip'leri (tümü/telegram/whatsapp/sms/email/instagram_dm/facebook_messenger), conversation list (avatar + kanal rozeti, kontak adı, son mesaj preview, unread badge, last_message_at diffForHumans); sağ panel: boş state — "Bir sohbet seçin"
- [x] **`modules/CRM/Resources/views/inbox/show.blade.php`** — üst conversation header (kanal rozeti + kontak adı + tel/email + lead linki + assignee dropdown + status dropdown — onchange submit); orta scroll'lu mesaj listesi (in/out balon, gradient out, ai_summary alt notu, attachments — photo/voice/audio/document); alt composer (textarea + gönder, kanal infosu)
- [x] **`modules/Telegram/Services/ConversationIngestService.php`** — `recordIncomingText()` + `recordIncomingMedia()` + `resolveConversation()` (firstOrCreate by chat_id, office/lead/contact TelegramUser'dan)
- [x] **WebhookController refactor** — text mesajları için `CommandHandler` çağrısına ek olarak `ConversationIngestService::recordIncomingText()`; media için MediaIngest aracılığıyla. Activity yazımı korundu (paralel)
- [x] **MediaIngestService update** — `ConversationIngestService` constructor inject, foto/ses/video/doküman Message attachment'ına yazılır (type, path, url, duration), Whisper transcript ai_summary'e
- [x] **Sidebar Gelen Kutusu linki** — CRM grubuna Görevler altında, `Schema::hasTable('conversations')` guard'lı unread count badge
- [x] **InboxController::show** — `$agents` (office filter + is_active) view'a geçilir (assignee dropdown için)

### 4.2 Drip Campaign engine ✅ TAMAM

- [x] `drip_campaigns` + `drip_steps` + `drip_enrollments` tabloları (table prefix `drip_` — Advertising `campaigns` ile collision'ı önlemek için)
- [x] **Modeller:** `Campaign`, `CampaignStep`, `CampaignEnrollment` (CRM namespace, drip_* table'lara bound)
- [x] **DripExecutor servisi** — step type executor: `send_message` (channel param + template render), `wait` (saat/gün/dk), `create_task` (Task tablosuna yaz), `branch` (lead_status_in/score_gte/...), `ai_action` placeholder. ChannelManager üzerinden 4 kanal gönderimi. Conversation auto-resolve (email/sms/whatsapp için contact phone/email'den firstOrCreate; telegram için mevcut conversation bul).
- [x] **Template render** — `{{contact.first_name}}`, `{{lead.score}}`, `{{agent.name}}`, `{{office.name}}` vb. nested path desteği
- [x] **`campaigns:tick` console command** — her 5 dk schedule (CRMServiceProvider'da kayıtlı), `--limit=50` opsiyonu
- [x] **`LeadCampaignObserver`** — `Lead::created`'ta `trigger='lead_created'` aktif kampanyalara enroll (audience_filter: status_in, source_in, score_gte destekli)
- [x] **`OnboardingCampaignSeeder`** — 5 günlük 7-adımlı varsayılan kampanya (e-posta karşılama → 1 gün bekle → görev oluştur → 2 gün bekle → SMS hatırlatma → 2 gün bekle → e-posta kapanış)
- [x] **`CampaignController` + routes** — `/admin/campaigns` (index), `/admin/campaigns/{id}` (show: steps timeline + enrollments list + stats), `toggle`, `enroll`, `enrollments/{id}/cancel`, `tick`
- [x] **Views** — `campaigns/index.blade.php` (3 stat kart + tablo + toggle/incele butonu), `campaigns/show.blade.php` (steps timeline ikonlu + enrollment tablosu + iptal butonu + 4 stat kart)
- [x] **Sidebar "Otomasyonlar" linki** — Gelen Kutusu altında

### 4.2 — Geriye kalan / Faz 5'e devir

- [ ] **Cold lead reactivation** — 60+ gün hareket yok olan lead'leri reactivation campaign'e al (cron)
- [ ] **AI campaign suggestion** — `CopilotService::generateFollowUpPlan` ile lead detayında "Bu lead için kampanya öner" butonu
- [ ] **No-code step builder UI** — şu an campaign + step'ler sadece seeder/manuel SQL ile oluşur, görsel akış editörü Faz 5

### 4.3 + 4.4 Sesli AI Sekreter ✅ TAMAM (ElevenLabs Agents + Netgsm)

**Faz 4.3 başlangıçta sadece "çağrı özetleme" olarak planlandı; Burak'ın iş vizyonu (Netgsm bayisi + bayi-paket teklif) ile gerçek zamanlı AI sekreter olarak genişletildi (2026-05-20).**

- [x] **`voice_agent_configs` tablosu + `VoiceAgentConfig` model** — ofis bazlı yapılandırma (routing_mode, secretary/default_agent phone, ring_timeout, mesai saatleri+timezone, system_prompt, greeting, ElevenLabs agent_id+voice_id, Telegram office channel, language). `isWithinBusinessHours()` helper.
- [x] **4 routing modu** (`VoiceAgentConfig::MODE_*`):
  - `listing_owner_first` (default) — önce ilan danışmanı, açmazsa sekreter, o da açmazsa randevu
  - `secretary_only` — klasik santral
  - `listing_owner_only` — direkt ilan danışmanına
  - `callback_only` — kimseye bağlama, sadece randevu (düşük tier paket)
- [x] **Tool API'leri** (`/api/voice-agent/tools/...`) — ElevenLabs Agent dashboard'dan çağrılır:
  - `search_listing` — yapısal + fallback semantic search, "spoken summary" döner
  - `create_lead` — kontak dedup (telefon+ofis), açık lead'i tekrar kullan, listing ref'ten agent atama
  - `request_transfer` — `TransferRouter` ile mode-aware karar (transfer/callback/voicemail)
  - `pre_call_brief` — `PreCallBriefService` ile çağrı ÖNCESI danışmana Telegram brifing
  - `book_callback` — Task oluştur + ofis kanalına bildirim
- [x] **`VerifyAgentToken` middleware** — `X-Voice-Agent-Token` shared-secret doğrulaması (header veya Bearer)
- [x] **Post-call webhook** (`/api/voice-agent/webhook`) — ElevenLabs konuşma sonu payload'undan Activity oluşturur (transcript flatten, recording_url, summary/sentiment/intent, tool_calls'tan lead_id ayıkla), danışmana/ofis kanalına özet Telegram
- [x] **Admin UI** `/admin/ai/voice-agent` — yayın toggle, 4 routing seçeneği (radio), transfer numaraları, mesai saatleri + hafta sonu, ElevenLabs Agent ID + voice ID, sistem promptu + Türkçe varsayılan template, Telegram kanal ID, tool URL'leri + shared secret göstergesi
- [x] **Sidebar "Sesli AI" linki** — AI & Araçlar grubunda, AI Settings'in üstünde
- [x] **`config/services.php`** — `voice_agent.shared_secret` + `voice_agent.webhook_url`
- [x] **`docs/voice-agent-setup.md`** — ElevenLabs dashboard kurulum rehberi: agent oluşturma, 5 tool tanımlama (JSON spec'leri), post-call webhook, Netgsm SIP routing notları, test akışı, sorun giderme tablosu, maliyet özeti

### 4.3 — Eski "ses dosyası yükleme → özetleme" özelliği

CallTranscriptionService + Lead detayındaki "Çağrı Özetleme (AI)" partial **korundu** — sesli AI sekretere PARALEL olarak çalışıyor. Geçmiş telefon kayıtlarını sonradan yüklemek için hâlâ değerli.

- [x] **`ElevenLabsService`** — STT (`scribe_v1`, Türkçe odaklı `language_code=tr`) + TTS (`eleven_multilingual_v2`); multipart upload, configurable timeout, fail → null + log
- [x] **`CallTranscriptionService`** — pipeline kalbi: `fromFile()` / `fromUrl()` → STT (provider switch, ElevenLabs default + Whisper fallback) → GPT JSON (özet + sentiment + intent + next_actions + buying_signals) → Activity yaz/güncelle
- [x] **CallConnector → Netgsm voice** — `callViaNetgsm()` (sesli arama, audio URL ile), `getNetgsmRecording()` (job report → recording URL), `transcribe()` artık CallTranscriptionService'e delege ediyor (Whisper hard-code'u kaldırıldı)
- [x] **`CallController` + route'lar** — `POST /admin/calls/transcribe` (lead_id + audio veya recording_url), `POST /admin/calls/activities/{activity}/transcribe` (var olan webhook activity'yi sonradan özetle)
- [x] **Lead detay partial** — `leads.partials.call-transcribe-card.blade.php` (audio upload + URL field + STT provider badge); `leads/show.blade.php` ai-analysis-card'ın altına include
- [x] **Config** — `config/services.php`'a `elevenlabs` (api_key, base_url, stt_model, language, tts_model, voice_id, timeout) + `netgsm` (usercode, password, sender_id, default_audio_url, default_number). `config/reos.php`'a `ai.transcription_provider` (eleven/whisper switch) + `voice` (provider) bölümleri
- [x] **`MediaIngestService::transcribeVoice` provider switch** — Telegram sesli notlar da ElevenLabs default'a düştü, fail → Whisper fallback

### 4.3 — Kullanıcı tarafı kurulum

`.env`'e eklenmesi gerekenler:
```
ELEVENLABS_API_KEY=sk_...
ELEVENLABS_DEFAULT_VOICE_ID=...   # TTS için (opsiyonel — şu an sadece STT kullanılıyor)
NETGSM_USERCODE=...
NETGSM_PASSWORD=...
NETGSM_SENDER_ID=...               # Netgsm onaylı caller ID (SMS başlığı ile aynı olabilir)
NETGSM_DEFAULT_AUDIO_URL=https://... # Netgsm voice çağrılarında çalınacak varsayılan ses dosyası
AI_TRANSCRIPTION_PROVIDER=elevenlabs # opsiyonel; default elevenlabs
VOICE_PROVIDER=netgsm
```

Test akışı:
1. `.env`'ye key'leri ekle → `php artisan config:clear`
2. `/admin/leads/{id}` aç → "Çağrı Özetleme (AI)" kartına git
3. Mp3/wav dosya yükle veya recording URL ver → "Özetle"
4. Activity oluşur — transcript + summary + sentiment + intent + next_actions + buying_signals dolu

### Faz 4 dışı (gelecekte)

- [ ] **WhatsApp Business API gerçek bağlantı** — `WhatsAppConnector` kodu yazılı, sadece access token + phone_number_id gerek
- [ ] **SMS provider gerçek bağlantı** — Netgsm key, `SMSConnector` mevcut
- [ ] **Twilio çağrı initiate + recording** — `CallConnector` mevcut
- [ ] **E-posta entegrasyonu** — IMAP fetch + SMTP threading (`EmailChannel` şu an sadece SMTP out)
- [ ] **Çoklu sosyal medya yayın** (Faz 3'ten devir): Meta Graph / X / LinkedIn — unified inbox ile birlikte mantıklı
- [ ] **Instagram DM + Facebook Messenger** kanal — Meta Graph API ile

---

## ✅ FAZ 5 — Süreç Otomasyon (TAMAM)

Amaç: Operasyonel mükemmellik — manuel takip minimuma insin.

### Tamamlanan işler

- [x] **`stage_entered_at` kolonu** — `deals` tablosuna eklendi (migration + backfill), Deal model'de `creating`/`updating` boot hook ile otomatik damgalama. Takılan deal tespiti için temel.
- [x] **`PipelineAutoActionExecutor`** — `PipelineStage::auto_actions` JSON'unu okuyup deal o stage'e girdiğinde çalıştırır. 6 aksiyon tipi: `create_task`, `notify_agent`, `notify_office`, `set_field` (whitelist'li kolonlar), `enroll_campaign` (slug ile), `update_probability`. Template render `{{contact.first_name}}`, `{{deal.title}}` vb.
- [x] **`DealStageObserver`** (CRM tarafı, Telegram observer'dan ayrı) — `created` ve `updated` (stage_id değiştiyse) eventinde executor'ı tetikler. CRMServiceProvider'da bind.
- [x] **`deals:stalled` console command** — `--days=14 --limit=200 --dry` opsiyonları, won/lost stage'leri hariç, agent başına liste + ofis özeti Telegram'a düşer. Günlük 09:00 schedule.
- [x] **Auto-actions builder UI** — `/admin/pipelines/{p}/stages/{s}/auto-actions` Alpine.js editör: 6 aksiyon tipi için tipo-spesifik formlar, yukarı/aşağı sıralama, sil. Show sayfasında her stage'in yanında "⚡ Aksiyonlar" butonu + aksiyon sayısı badge'i.
- [x] **PipelineController stage save bug fix** — form `stages[*]` array gönderiyordu ama hiç process edilmiyordu, controller update'i baştan yazıldı (kept_ids ile mevcut update, yeni eklenenleri create, çıkanları sadece deal'sızsa sil).
- [x] **`DailyPlannerService`** — kullanıcının açık iş yükünü (bugünkü görevler, hot leads, follow-up leads, açık deals) toplar, GPT'ye JSON mode'da öncelikli 5 aksiyon yazdırır. 1 saatlik cache. AI fallback olduğunda kural tabanlı sıralama. `/admin/ai/copilot/daily-plan` sayfası + sidebar "Bugünkü Plan" linki.
- [x] **Sabah brifingine AI önerileri** — `SendMorningBriefing` artık `DailyPlannerService::generateForAgent` çağırıp top-3 aksiyonu Telegram brifingine ekliyor. AI patlasa sessizce geçer.
- [x] **Performans dashboard (BI modül revize)** — `AnalyticsService` zaten implemented'dı ama controller/view boştu; tüm view'ler yazıldı: `dashboard` (KPI + revenue trend chart + funnel + top agents + lead sources), `agent-performance` (tarih filtreli leaderboard tablosu), `conversion-funnel` (huni + step rates), `lead-sources`, `portal-performance`, `listing-performance`, `revenue` (Chart.js bar + tablo). Route'lar düzeltildi (önceki `admin/reports/reports/...` double-nested junk temizlendi). BI provider `Resources` → `resources` case fix.
- [x] **`RealEstateExpertService`** — guzllik ai-reklam-uzmani pattern: 7 yönetimsel prensip listesi (60+ gün satılmayan ilan, 30+ gün stuck deal, %5 altı conv. rate kaynak, 60dk+ yanıt süresi, skor 80+ ama 7+ gün hareketsiz lead, kalite skoru 40 altı ilan, agent yanıt süresi 2x ortalama). `buildSnapshot` veriyi toplar (Listing + Lead + Deal + AnalyticsService), GPT'ye JSON modunda { insights[], summary } yazdırır. 2 saatlik cache. AI fallback'te kural tabanlı insight üretir. `/admin/ai/copilot/expert` sayfası — kategori ikonları, severity renkleri, prensip listesi accordion.

### Notlar

- **Kaynak ROI** maliyet datası yok (ad spend portal'larından çekilmiyor) — `getPortalPerformance` `cost_per_lead` ve `roi`'yi 0 dönüyor. Faz 6'da Meta Ads / Google Ads connector bağlanınca gerçek ROI hesaplanabilir.
- **Stage saving düzeltmesi** PipelineController::update'te yapıldı — eski form-only davranış silinerek yeni mantık eklendi. Mevcut deal'ı olan stage'ler silinmiyor.
- **Schedule list** Telegram (08:30 brifing, /5dk task reminders) + CRM (`campaigns:tick` /5dk, `deals:stalled` günlük 09:00). Cron tek satır: `* * * * * php artisan schedule:run`.

---

## ✅ FAZ 6 — İleri Özellikler (ÇEKİRDEK TAMAM)

Amaç: Türkiye pazarına özel rekabet üstünlüğü.

### Tamamlanan işler

- [x] **AI Broşür PDF üretici** — `BrochureService` + 2 sayfalı şık template:
  - Kapak (ofis logosu/markası + ilan başlığı + fiyat + ana foto)
  - Detay (temel bilgiler, AI iyileştirilmiş açıklama)
  - Galeri (5 ek foto), özellikler, harita (Google Static veya OpenStreetMap fallback), danışman kartı
  - Endpoint: `/admin/listings/{id}/brochure` (?mode=preview ile tarayıcıda açılır, default indirir)
  - Listing show'a "Broşür İndir" + "Önizle" butonları
- [x] **Portal Sync altyapısı** — Sahibinden + Hepsiemlak + EmlakJet:
  - `PortalConnectorInterface` + `AbstractPortalConnector` + 3 concrete connector
  - `PortalManager` singleton — registry pattern
  - `portal_sync_logs` tablosu, her sync (başarı/başarısızlık) kayıt altında
  - `/admin/portal-sync` sayfası: 3 portal durumu, ilan başına tek tek/"Tümü" butonu, son işlem paneli
  - `.env`'de credential yoksa "Kurulum gerek" rozeti + gerçek HTTP çağrısı atılmaz
  - Sidebar "Portal Senkron" linki eklendi
  - Sahibinden için kurumsal API başvuru gerekiyor — kullanıcı yetkisini alınca aktif (per-office credential ile de büyütülebilir altyapı hazır)
- [x] **KVKK uyumlu Doküman Yönetimi**:
  - Polimorfik `documents` tablosu (Lead/Deal/Contact/Listing'e bağlanır)
  - `Document` model — LogsActivity + SoftDelete + is_confidential default true
  - `DocumentController` — index/store/download/destroy, office isolation
  - Reusable `documents-card.blade.php` partial — Lead/Deal/Contact show'lara include
  - Alpine.js drag-drop yok, ama upload + kategori seçimi (sözleşme/kimlik/tapu/ekspertiz/foto/diğer) + gizli işareti
  - Her indirme `activity_log`'a "downloaded" event'i (kim, hangi IP'den) — KVKK audit
  - Dosyalar `local` (private) disk'te, sadece auth route üzerinden indirilir
- [x] **AI ile URL'den İlan İçe Aktarma** ⭐ (yıldız özellik):
  - `ListingImportService` — Sahibinden/Hepsiemlak/EmlakJet host detection + HTML scrape + AI JSON parse
  - HTML temizleme (script/style/svg at), 30K char limit, GPT JSON mode
  - Foto URL'leri AI'dan + meta tag/img src fallback
  - `DownloadListingPhotosJob` — async foto indirme, MediaLibrary'ye photos collection'ına yazar
  - Listings index'te "AI ile İçe Aktar" yeşil buton + Alpine.js 3-step modal (URL → önizleme → kaydet)
  - Onboarding sürtünmesini büyük ölçüde kaldırıyor — danışman Sahibinden ilanını tek linkle bizim CRM'e taşıyor

### Bilinçli olarak atlanan (Burak'la birlikte karar verildi 2026-05-20)

- [ ] ~~E-imza entegrasyonu~~ — Türkiye'de gayrimenkul satışı için tapuda noter şartı var, e-imza yerine geçmiyor. Kullanım talebi yok.
- [ ] ~~TKGM Tapu API~~ — Web Tapu emlakçı portalı sadece web arayüzü, REST/SOAP API yok. TAKPAS sadece avukatlar + resmi kurumlar için. SaaS olarak otomatik sorgu mümkün değil.
- [ ] ~~Alıcı/Satıcı portal~~ — Türkiye emlak pazarında müşteri portal'a alışkın değil, WhatsApp tercih ediyor. Sahibinden zaten ilan görüntüleme arayüzünü sağlıyor. Tuzak olarak değerlendirilip atlandı.

### Gelecekte yapılabilecekler (Faz 6 dışı)

- [ ] **Property tour scheduling** — Google/Outlook calendar sync
- [ ] **Satış sonrası** — bakım talepleri, yıldönümü takipleri, referans isteği
- [ ] **Per-office portal credentials** — Sahibinden gibi resmi portallarda her ofisin kendi API anahtarı (UI + encrypted storage)

---

## ⏳ FAZ 7 — Cila & Üretim (BEKLEYEN)

Amaç: Yayın hazırlığı.

### Planlanan işler

- [ ] **Mobile PWA optimizasyon** — install prompt, offline support
- [ ] **Performans audit** — N+1 query, eager loading, cache stratejisi, index'ler
- [ ] **Test suite** — Pest ile feature + unit testler
- [ ] **Security review** — XSS, SQLi, CSRF, IDOR
- [ ] **Onboarding wizard** — yeni office için 5 adımlı kurulum
- [ ] **Çoklu dil UI** — TR/EN minimum, daha sonra RU/AR
- [ ] **Production deployment** — env config, queue worker (supervisor), cache warmup, monitoring (Sentry?)
- [ ] **Backup & disaster recovery** stratejisi

---

## 🗂 Önemli Dosyalar Haritası

```
realestate/
├── PROGRESS.md                              ← BU DOSYA (yol haritası)
├── config/
│   ├── reos.php                             ← AI config (model, pricing, kredi)
│   └── services.php                         ← Telegram, OpenAI, Twilio anahtarları
├── app/
│   ├── Models/
│   │   ├── AiUsageLog.php                   ← Her AI çağrı kaydı
│   │   ├── AiCredit.php                     ← Office aylık kredi
│   │   └── AiSetting.php                    ← Per-office API key (encrypted)
│   ├── Services/AI/
│   │   └── AiCreditService.php              ← Kredi consume/check
│   └── Jobs/AI/
│       ├── AnalyzeLeadJob.php               ← Yeni lead için async analiz
│       ├── GenerateListingDescriptionJob.php
│       └── ValuateListingJob.php
├── modules/
│   ├── AI/
│   │   ├── Services/AIService.php           ← Tüm AI çağrılarının kalbi
│   │   ├── Services/{Content,Valuation,Copilot,Matching}Service.php
│   │   ├── Http/Controllers/
│   │   │   ├── ContentController.php        ← Gerçek OpenAI
│   │   │   ├── ValuationController.php
│   │   │   ├── CopilotController.php
│   │   │   └── SettingsController.php       ← /admin/ai/settings
│   │   └── Resources/views/
│   │       ├── copilot/index.blade.php      ← AI Copilot sayfası
│   │       └── settings/index.blade.php     ← API key + kullanım istatistikleri
│   ├── CRM/Models/Lead.php                  ← created event → AnalyzeLeadJob
│   └── Telegram/                            ← Yeni modül
│       ├── Services/TelegramService.php
│       ├── Http/Controllers/{Telegram,Webhook}Controller.php
│       ├── Models/TelegramUser.php
│       └── Resources/views/index.blade.php
└── resources/views/
    ├── layouts/admin.blade.php              ← Copilot widget include
    ├── layouts/partials/sidebar.blade.php   ← AI Ayarları + Telegram link
    └── components/ai-copilot-widget.blade.php ← Floating AI butonu
```

---

## 🔧 Geliştirici Kurulum Kontrol Listesi

### İlk kurulum (yeni makineler)
- [ ] `.env` dosyasına OpenAI key ekle (`OPENAI_API_KEY=sk-...`)
- [ ] Telegram için `.env`'e ekle (Faz 2 canlı olduğunda): `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME`
- [ ] `php artisan migrate` koş
- [ ] `php artisan queue:work --tries=2 --timeout=120` worker'ı başlat
- [ ] `php artisan serve` + `npm run dev`
- [ ] `/admin/ai/settings` üzerinden bağlantı testi

### Her oturum başı
- [ ] Dev server kontrolü: `lsof -ti:8000` ve `:5173`
- [ ] `git status` ile son durum
- [ ] Bu dosya (PROGRESS.md) okunup hangi fazda olduğumuza bakılır
- [ ] Hedef faz seçilip kalan checklist'lerden devam edilir

---

## 📝 Karar Defteri (geriye dönük not)

- **2026-05-20**: Master roadmap onaylandı. Faz 0 → 1 → 2 sırası seçildi. AI provider olarak yalnız OpenAI. Telegram bot bağlama sonraki oturuma ertelendi. `gpt-4o` varsayılan model (GPT-5.5 henüz yok, çıkınca config'den değiştir).
- **2026-05-20**: AIService raw OpenAI client'a refactor edildi (`openai-php/laravel` paketi yüklü değildi, paket eklemek yerine raw client tercih edildi).
- **2026-05-20**: Faz 1 cila — Lead detayda AI Analizi kartı + manuel "Yeniden Analiz Et" akışı; İlan detayda AI Değerleme kartı (komparable + trend + AI yorumu + fiyatlama senaryoları) + "AI ile Üret" açıklama butonu canlı. Tailwind dynamic class trap'i yüzünden tüm renk class'ları literal/match ile yazıldı (JIT safelist yok).
- **2026-05-20**: Faz 2 kod tamamı — WebhookController küçük router'a indirildi, iş `CommandHandler` + `CallbackHandler` + `MediaIngestService`'e dağıtıldı. Bildirimler için **observer pattern** seçildi (event pattern değil), çünkü `Modules\CRM\Events\Lead*` ve `Deal*` class'ları stub değil — yani `event(new LeadCreated())` runtime'da `class not found` ile patlardı. Observer'lar `TelegramServiceProvider::boot()`'ta `Lead::observe()` / `Deal::observe()` ile bind. Schedule da aynı provider'dan `Schedule` resolve edip kuruldu (Kernel'a dokunulmadı). Whisper transkripsiyon `audio()->transcribe()` API'si — fail olursa activity yine yazılır, summary null kalır. Faz 2'nin geriye kalanı dışarıdan iş (bot token + public URL).
- **2026-05-20**: CRM Event stub'ları yazıldı — `LeadCreated`, `LeadUpdated`, `LeadConverted`, `DealCreated`, `DealStageChanged`, `DealClosed`. `Lead.php`'deki `event(new LeadCreated())` çağrısı artık runtime'da `class not found` ile patlamıyor.
- **2026-05-20**: Faz 3 çekirdek — Fal.ai foto iyileştirme (5 op), markalı sosyal kart üretici (4 şablon × 3 boyut, Intervention v3 GD), `ContentService::generateSocialContent`/`generateReelsScript` UI'a bağlandı, aylık takvim view, hashtag intelligence (JSON mode). Tek "İlandan Oluştur" modal'ında 3 sekme. Card için **Intervention v3** seçildi (browser-shot/headless Chrome alternatifi yerine — sunucu kurulumu basit, GD zaten var, dompdf vendor'undan DejaVuSans TTF ile Türkçe destekli). Fal.ai için sync `fal.run/{model}` endpoint'i (queue API kullanmadık — UX için bloklayıcı ama timeout 90s yeterli). Yayın API'leri (Meta Graph / X / LinkedIn) bilinçli olarak ertelendi — gerçek inbox ve OAuth Faz 4 ile birlikte gelir.
- **2026-05-20**: Faz 4.1 Unified Inbox **backend tamam, UI yarım**. Kullanıcı oturumu duraklatmaya karar verdi — view'ler yazılmadan commit atıldı (kasıtlı checkpoint). Tasarım kararları: (1) **Conversations + Messages** yeni tablolar — Activity'den ayrı çünkü Activity geniş bir CRM event modeli, conversation thread'i daha dar bir kanal-bazlı mesajlaşma soyutlaması. İki sistem paralel çalışacak (Activity = "ne oldu" defter, Conversations = "kim ne yazdı" sohbet); ingest noktaları her ikisine yazacak. (2) **ChannelInterface + ChannelManager** singleton pattern — Telegram/WhatsApp/SMS/Email tek arayüz arkasında, mevcut connector'lar `*Channel` sınıfında sarmalandı, controller kanal adına göre `$manager->get('telegram')` ile çözüyor. Yeni kanal eklemek = yeni class + provider'a register. (3) Inbox CRM modülü altına konuldu (`admin/inbox`), ayrı modül açmadık — conversations CRM concept ve sidebar'da CRM grubuna doğal düşüyor. (4) Office isolation `InboxController::authorizeView()` ile tek noktada, controller seviyesinde.
- **2026-05-20**: **Sesli AI Sekreter** canlı — Faz 4.3 başlangıçta "çağrı özetleme" idi, Burak'ın iş vizyonu (Netgsm bayisi → paket satış) ile **gerçek zamanlı AI sekreter**'e büyütüldü. Tasarım kararları: (1) **Yeni modül `modules/VoiceAgent/`** — CRM altında değil, ayrı bir bounded context (çünkü tool API + webhook + agent config + admin UI hepsi bir araya gelir, RealEstate ve Telegram modüllerinin servislerini consume ediyor). (2) **ElevenLabs Conversational AI Agents** (sadece STT değil) — bizim Laravel sadece tool API + webhook yazıyor, agent'in beyin/ses/SIP tarafı ElevenLabs dashboard'da yapılandırılıyor. Bizim kod ElevenLabs spesifikasyonuna bağımlı değil — webhook payload normalize ediliyor. (3) **4-modlu routing** ofis bazlı seçilebilir — `TransferRouter` mode + mesai saati + lead/listing context'i ile karar verir. `callback_only` modu özellikle bayi paketinin **düşük tier** seçeneği (ofis hiç telefon açmıyor). (4) **`pre_call_brief` ayrı tool** — agent transfer'den ÖNCE çağırmalı: danışmana "X arıyor, Y ilanı, Z bütçe, 5sn'de bağlanıyor" Telegram'dan düşer. Bu fark yaratıyor — danışman ne konuşacağını bilerek açar. (5) **`VerifyAgentToken` middleware** shared secret + `hash_equals` (timing-safe) — `.env`'de boşsa dev mode (auth off). (6) **Eski `CallTranscriptionService` korundu** — paralel akış, geçmiş ses dosyası yüklemeleri için hâlâ değerli. (7) **`docs/voice-agent-setup.md`** — 6 adımlı operatör kurulum rehberi, dashboard adımları, 5 tool JSON spec, Netgsm SIP notları, test akışı, maliyet özeti. Buradaki en kritik nokta: Netgsm SIP → ElevenLabs SIP eşleşmesi olgun değil, Twilio middleware veya outbound callback alternatifi gerekebilir — bunu kullanıcı kendi telekom kurulumunda halledecek.
- **2026-05-20**: Faz 4.3 AI çağrı özetleme **canlı** — ElevenLabs (STT/TTS) + Netgsm (voice) entegrasyonu. Tasarım kararları: (1) **Provider switch katmanı** — `CallTranscriptionService::transcribe()` `reos.ai.transcription_provider` config'ine bakarak ElevenLabs'a gidiyor, başarısız olursa Whisper fallback. Aynı switch `MediaIngestService::transcribeVoice`'da da var (Telegram sesli notlar için). Whisper hiç kaldırılmadı — fallback olarak duruyor. (2) **`CallTranscriptionService` üst seviyede tek pipeline** — STT + GPT analiz + Activity yazımı tek class'ta. `CallConnector::transcribe()` ona delege ediyor (eski Whisper kodu kaldırıldı). (3) **Netgsm voice basit ilk versiyon** — pre-recorded `audio_url` ile dial, gerçek IVR (TTS-driven, interactive call) sonraki dilim. `callViaNetgsm()` Netgsm SMS connector pattern'ini takip ediyor (response code 00/01/02 başarı, format `00 jobid`). (4) **GPT analiz JSON-mode** — system prompt strict JSON istiyor (`summary`, `sentiment`, `intent`, `next_actions[]`, `buying_signals[]`), `AIService::chatJson` ile parse. (5) **Activity'ye yazma** mevcut `call_transcript`, `ai_summary`, `ai_sentiment`, `ai_intent`, `ai_next_actions`, `call_sentiment` alanlarını dolduruyor — şema değişikliği yok, mevcut Activity model'i yeterince zengin. (6) **Lead show'da yeni partial** — `call-transcribe-card`, ai-analysis-card'ın altına include edildi. STT provider rozeti gösteriyor (kullanıcı hangi sağlayıcının aktif olduğunu görsün).
- **2026-05-20**: Faz 4.2 Drip Campaign engine **canlı**. Tasarım kararları: (1) **Tablo prefix `drip_`** — Advertising modülünde zaten bir `campaigns` tablosu var (ad campaigns — `hedef`, `durum`, `budget`, `health_score`), collision'ı `drip_campaigns`/`drip_steps`/`drip_enrollments` ile çözdük. Model namespace farklı (`Modules\CRM\Models\Campaign` vs `Modules\Advertising\Models\Campaign`) ama tablo adları collide. Model'lere `protected $table = 'drip_*'` eklendi. (2) **Executor processing loop** — bir tick'te bir enrollment'ı `wait` step'ine ya da tamamlanmaya kadar zincirleme step çalıştırır (`maxStepsPerTick=10` safety cap). Böylece `send_message → create_task → wait` üçlüsü tek tick'te ilk iki step'i koşar, wait'e kadar gelir. (3) **Conversation auto-resolve** — `send_message` step'i Conversation'ı firstOrCreate ediyor (phone/email'den). Telegram için bu mümkün değil (chat_id pairing gerekir) — sadece mevcut conversation aranır. (4) **Template render** basit regex `{{path.to.value}}` ile nested context (`contact`, `lead`, `agent`, `office`). (5) **Observer pattern** kullanıldı (yine event yerine) — `LeadCampaignObserver` direkt `Lead::observe()` ile bind edildi (mevcut `Telegram\LeadObserver` ile aynı pattern). (6) **OnboardingCampaign seeder** idempotent — `firstOrCreate` ile slug bazlı, step'ler her seed'de yeniden kuruluyor (silinip yazılıyor) çünkü step config'i kod tarafında evolve edebilir.
- **2026-05-20**: **Faz 5 Süreç Otomasyon tamam** — 7 görev, hepsi canlı. Tasarım kararları: (1) **`stage_entered_at`** yeni kolon, Deal boot hook'larında `creating`/`updating` ile damgalanıyor. `updated_at` kullanmadık çünkü o her field edit'inde değişir (yanlış stalled tespiti olur). (2) **`PipelineAutoActionExecutor` ayrı servis** + **`DealStageObserver`** (CRM tarafı) Telegram'ın `DealObserver`'ından ayrı tutuldu — sorumluluk ayrımı, sıra önemli değil. (3) **6 aksiyon tipi**: create_task, notify_agent, notify_office, set_field (whitelist'li kolonlar — güvenlik), enroll_campaign (slug ile — campaign_id değişebilir), update_probability. (4) **Stage save bug fix** — PipelineController::update() form `stages[]` array'i gönderirken hiç process etmiyordu (pre-existing bug). Auto-actions feature'ı çalışsın diye minimal düzeltildi: keep_ids ile mevcut update, yeni eklenen create, çıkanlar sadece deal'sızsa sil. (5) **`deals:stalled` günlük 09:00** — won/lost stage'leri hariç tutuyor (`is_won_stage`/`is_lost_stage` flag'leri). Idempotent değil — her gün hatırlatma; çok rahatsız olursa eşik yükseltilir. (6) **`DailyPlannerService` + sabah brifing entegrasyonu** — kullanıcının açık iş yükünü AI'ya yazıp top-5 aksiyon istiyor. JSON mode. 1 saat cache. AI patlarsa kural tabanlı fallback. Brifinge ekstra section eklendi ama try/catch ile — AI yoksa sessizce geçer (brifing kırılmasın). (7) **BI dashboard revize** — AnalyticsService zaten implemented'dı ama controller/view'ler boştu. Tüm view'ler yazıldı. Önceki double-nested route'lar (`admin/reports/reports/...`) tek seviyeye düşürüldü. BI provider'da `Resources` → `resources` case fix (Linux production için). (8) **`RealEstateExpertService`** — guzllik ai-reklam-uzmani pattern uygulandı: PRENSİPLER sabit listesi GPT system prompt'una konuldu, snapshot ayrı toplandı, AI prensiplere göre veriyi yorumlasın istendi. 2 saat cache. Kategori ikonları + severity renkleri ile insight kartları. Prensip listesi accordion'da gizli (kullanıcı isterse görür).
- **2026-05-20**: Faz 4.1 Unified Inbox **finalize edildi** — UI + Telegram ingest + sidebar bağlandı. Tasarım kararları: (1) **`ConversationIngestService`** ayrı bir servis olarak yazıldı — hem WebhookController hem MediaIngestService bunu çağırıyor, böylece text/media ingest tek noktada (DRY). Resolve mantığı: `Conversation::firstOrCreate(['channel'=>'telegram','channel_thread_id'=>$chatId])`, office/lead/contact TelegramUser'ın user_id'sinin son aktif lead'inden çıkarılır. (2) **Activity + Conversation paralel** — MediaIngest hem `Activity::create` hem `recordIncomingMedia` çağırır, biri patlasa diğeri geçer (try/catch). Geriye uyumlu — eski activity timeline ekranı bozulmaz. (3) **Inbox layout 2-kolonlu sayfalar** olarak yazıldı, SPA değil — show.blade.php ayrı route'ta, sol panel yine görünür. Bunu seçtim çünkü mevcut admin layout (sidebar + content area) zaten "ayrı sayfa" pattern'iyle uyumlu, Alpine.js/Livewire reactivity eklemeden çalışıyor. SPA hisse uzun versiyonu Livewire ile ileride kolayca eklenebilir. (4) **Attachment data shape**: `[{type:'photo'|'voice'|'audio'|'video'|'document', path:..., url:..., duration?}]`. URL sadece `storage/app/public/` altındaki path'lerden türetiliyor; `telegram-ingest/` özel klasörü public değil, sadece path tutuluyor (gelecekte signed URL ekleyebiliriz). (5) Sidebar unread badge `Schema::hasTable('conversations')` guard'lı çünkü ilk migrate çalıştırılmadan sidebar render edilirse hata vermesin.

---

## 🎯 Şu An Yapılacaklar (En Yakın Hedef)

### Telegram'ı canlıya alma (Faz 2 kullanıcı tarafı)
1. **BotFather'dan token al:** Telegram'da `@BotFather` → `/newbot` → ad ve username ver → token kopyala
2. **`.env`'e ekle:**
   ```
   TELEGRAM_BOT_TOKEN=123456:ABC-DEF...
   TELEGRAM_BOT_USERNAME=re_os_bot
   ```
3. **`php artisan config:clear`** ile config'i yenile
4. **Public URL aç (lokal için):** `ngrok http 8000` veya `cloudflared tunnel`
5. **Webhook'u ayarla:** `/admin/telegram` → "Webhook URL ayarla" butonunu kullan (URL: `https://YOUR-NGROK/api/telegram/webhook`)
6. **Eşleştir:** `/admin/telegram` → "Eşleştirme kodu al" → Telegram'da bot'a `/start KOD`
7. **Komutları test et:** `/today`, `/leads`, `/hot`, `/search Beşiktaş 2+1 kiralık`, `/listing REF`
8. **Sabah brifingi:** sistem cron'una `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1` eklendiğinden emin ol — 08:30'da brifing düşmeli

### Faz 3 — kullanıcı tarafı kurulum

1. **Fal.ai key (opsiyonel):** `.env`'e `FAL_API_KEY=...` (https://fal.ai/dashboard/keys). Yoksa kart üretici + ContentService çalışmaya devam eder, sadece foto iyileştirme butonları "Fal.ai yapılandırılmamış" döner.
2. **Modelleri test et:** `/admin/social-media` → "İlandan Oluştur" → bir ilan seç, "Sosyal Kart Üret" sekmesinde şablonu/boyutu seç, "Kartı Üret"e bas → PNG döner, "Gönderi Olarak Kullan" ile yeni gönderiye aktarılır.
3. **Takvim:** `/admin/social-media/calendar` — aylık görünüm, ileri/geri.

### Sonraki oturum başlangıcı — Faz 7 (Cila & Üretim)

**Faz 6 tamam ✅** — Broşür + Portal Sync + Doküman + AI URL İçe Aktarma canlı.

**Faz 6 hemen denemek için:**
1. `php artisan migrate` (yeni `portal_sync_logs` ve `documents` tabloları)
2. `php artisan queue:work --timeout=300` (foto indirme job'u için)
3. `/admin/listings` → "AI ile İçe Aktar" yeşil butonu → bir Sahibinden ilan linki yapıştır → AI parse → kaydet
4. Listing show → "Broşür İndir" → 2 sayfalı PDF
5. Lead/Deal/Contact show'da "Dokümanlar" kartı → dosya yükle
6. `/admin/portal-sync` → portal durumlarını gör (.env'e API key girince aktif olur)

**Sonra (Faz 7 — Cila & Üretim):**
- Mobile PWA optimizasyonu — install prompt, offline support
- Performans audit — N+1 query temizliği, eager loading, cache stratejisi, DB index'leri
- Pest test suite — feature + unit testler
- Security review — XSS, SQLi, CSRF, IDOR taraması
- Production deployment — env config rehberi, queue worker (supervisor), cache warmup, monitoring (Sentry?)
- Yeni ofis için onboarding wizard (5 adımlı kurulum)
- Çoklu dil UI — TR/EN
- Backup & disaster recovery

**Faz 4'ün opsiyonel geri kalanları:**
- Cold lead reactivation cron (60+ gün inactive → reactivation campaign'e al)
- AI campaign suggestion butonu — lead detayında "Bu lead için kampanya öner"
- Görsel campaign step builder UI

**4.1'in opsiyonel iyileştirmeleri (Faz 7 / cila):**
- Inbox SPA hissi — Livewire/Alpine ile sayfa yenilenmeden mesaj akışı
- Composer'a foto/dosya yükleme — şu an boş `attachments` gönderiyor
- "Yeni Conversation" butonu — manuel kontakt + kanal seç + ilk mesaj
- Mesaj real-time refresh — broadcasting / polling
- Inbox arama — kontak/içerik/lead bazlı

