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
| 4 | İletişim & Yaşam Döngüsü | 🟡 4.1 yarım | Unified Inbox backend tamam (conv+msg tabloları, 4 kanal abstraksiyonu, controller, route'lar). View + Telegram ingest refactor + sidebar bekliyor. Drip + çağrı özet sonraki dilim |
| 5 | Süreç Otomasyon | ⏳ Bekliyor | Workflow builder, takılan deal uyarıları |
| 6 | İleri Özellikler | ⏳ Bekliyor | Portal sync, brochure, e-imza, TKGM, alıcı/satıcı portal |
| 7 | Cila & Üretim | ⏳ Bekliyor | PWA, performans, test, güvenlik, çoklu dil |

**İlerleme:** 3.5/8 faz (Faz 2 kodu hazır — bot token + webhook URL bekliyor; Faz 3 çekirdek özellikler canlı; Faz 4.1 Unified Inbox backend tamam, UI yarım kaldı).

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

## 🟡 FAZ 4 — İletişim & Yaşam Döngüsü (4.1 YARIM)

Amaç: Tüm iletişim kanalları tek inbox'ta, müşteri yaşam döngüsü otomatik.

### 4.1 Unified Inbox — backend TAMAM, UI YARIM

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

**Yarım kalan — sonraki oturum bunlardan devam:**
- [ ] **Inbox view'ları:** `modules/CRM/Resources/views/inbox/index.blade.php` (sol conversation list — kanal ikonu, kontak adı, son mesaj preview, unread badge, durum filtresi; sağ panel boş state) + `inbox/show.blade.php` (üst conversation header — kanal/kontak/lead/assignee/durum; orta scroll'lu mesaj listesi — in/out balon; alt mesaj yaz + gönder formu)
- [ ] **Telegram ingest refactor:** `WebhookController` text mesajları artık MediaIngest'e değil, **Conversation+Message**'a da yazsın. Mevcut Activity yazımı korunmalı (geriye uyumlu). `TelegramUser`'dan office_id + linked_contact_id çekilip Conversation `channel='telegram'`, `channel_thread_id=chat_id` ile eşleştirilir; varsa `firstOrCreate`
- [ ] **MediaIngestService update:** foto/ses geldiğinde Conversation+Message'a attachment olarak yazılsın (mevcut activity akışı korunur)
- [ ] **Sidebar'a "Gelen Kutusu" linki** — `resources/views/layouts/partials/sidebar.blade.php`, unread count badge ile
- [ ] **Conversations index empty-state aksiyonu:** "Yeni Conversation" butonu (manuel kontakt + kanal seç + ilk mesaj) — opsiyonel ama UX için iyi

### 4.2 Drip Campaign engine (BEKLİYOR)

- [ ] `campaigns` + `campaign_steps` + `campaign_enrollments` tabloları
- [ ] Step executor: `send_message` (channel param), `wait` (saat/gün), `branch` (condition), `create_task`
- [ ] `Lead::created` → `OnboardingCampaign` enrollment (5 günlük seri)
- [ ] Cold lead reactivation: cron — 60+ gün hareket yok olan lead'leri reactivation campaign'e al
- [ ] `CopilotService::generateFollowUpPlan` ile AI öneri (lead detayda "Bu lead için kampanya öner" butonu)

### 4.3 AI çağrı özetleme (BEKLİYOR)

- [ ] POST `/admin/calls/transcribe` — audio dosya yükle veya call_recording_url
- [ ] Whisper transkript → `Activity::call_transcript`
- [ ] GPT özet → `ai_summary` + `ai_sentiment` + `ai_intent` + buying signals (mevcut Activity alanlarını doldur)
- [ ] CallConnector mevcut, transcribe pipeline'ı bağla

### Faz 4 dışı (gelecekte)

- [ ] **WhatsApp Business API gerçek bağlantı** — `WhatsAppConnector` kodu yazılı, sadece access token + phone_number_id gerek
- [ ] **SMS provider gerçek bağlantı** — Netgsm key, `SMSConnector` mevcut
- [ ] **Twilio çağrı initiate + recording** — `CallConnector` mevcut
- [ ] **E-posta entegrasyonu** — IMAP fetch + SMTP threading (`EmailChannel` şu an sadece SMTP out)
- [ ] **Çoklu sosyal medya yayın** (Faz 3'ten devir): Meta Graph / X / LinkedIn — unified inbox ile birlikte mantıklı
- [ ] **Instagram DM + Facebook Messenger** kanal — Meta Graph API ile

---

## ⏳ FAZ 5 — Süreç Otomasyon (BEKLEYEN)

Amaç: Operasyonel mükemmellik — manuel takip minimuma insin.

### Planlanan işler

- [ ] **Pipeline auto-actions** — stage değişince fire job (mevcut `auto_actions` JSON kolonu var)
- [ ] **No-code workflow builder UI** — "Eğer X olursa Y yap"
- [ ] **Takılan deal uyarıları** — 14 gündür stage'i değişmemiş deal'lar için bildirim
- [ ] **Smart task suggestions** — AI günlük "şunu yap" listesi
- [ ] **Performans dashboard** — agent leaderboard, lead source ROI, time-to-close, conversion funnel
- [ ] **AI Emlak Uzmanı** — guzllik'in `ai-reklam-uzmani.md` pattern'i, prensip tabanlı karar destek
  - "Bu portfoyde 5 ilan 60+ gündür satılmıyor, fiyat indirimi öner"
  - "Bu agent yanıt süresi yavaşladı"
  - "Şu kaynak ROI olarak zayıf"

---

## ⏳ FAZ 6 — İleri Özellikler (BEKLEYEN)

Amaç: Türkiye pazarına özel rekabet üstünlüğü.

### Planlanan işler

- [ ] **Portal sync** — Sahibinden, Hepsiemlak, Emlakjet
  - Gerçek API entegrasyonu (mevcut routes var, içleri boş)
  - Otomatik yayınla / güncelle / kaldır
  - Stat çekme (görüntülenme, favori)
- [ ] **Brochure PDF generator** — AI açıklama + foto + harita + agent kartı
- [ ] **Doküman yönetimi** — KVKK uyumlu, soft delete, audit trail
- [ ] **E-imza entegrasyonu** — onaylarim / DocuSign
- [ ] **TKGM Tapu API** — tapu doğrulama, kadastro bilgi
- [ ] **Alıcı/Satıcı portal** — ayrı frontend, müşteri kendi yolculuğunu görsün
- [ ] **Property tour scheduling** — Google/Outlook calendar sync
- [ ] **Satış sonrası** — bakım talepleri, yıldönümü takipleri, referans isteği

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

### Sonraki oturum başlangıcı — buradan devam (Faz 4.1 finalize)

**Hemen yapılacak (Inbox UI):**

1. **`modules/CRM/Resources/views/inbox/index.blade.php`** — `crm::inbox.index`. Layout: `@extends('layouts.admin')`. Sol panel (~360px): durum tab'ları (Açık/Arşiv/Kapalı + Okunmamış filtresi), kanal filtresi (chip'ler: tümü/telegram/whatsapp/sms/email), conversation list (`@foreach($conversations)` — kanal ikonu, kontak adı veya `channel_thread_id`, last_message_preview, unread badge, `last_message_at` diff_for_humans). Sağ panel: boş state — "Bir sohbet seçin" mesajı.
2. **`modules/CRM/Resources/views/inbox/show.blade.php`** — `crm::inbox.show`. Üst: conversation header (kanal ikonu + kontak + lead linki + assignee dropdown + status dropdown — `admin.inbox.assign`/`status` POST). Orta: scroll'lu mesaj listesi, `$messages` üzerinde foreach — `direction=='in'` sol balon (gri), `direction=='out'` sağ balon (mavi), zaman + status badge. Alt: form `action="admin.inbox.send"`, `<textarea name="body">` + ek dosya alanı (opsiyonel, ilk versiyonda `attachments` boş gönder), "Gönder" butonu.
3. **Telegram ingest refactor:** `modules/Telegram/Http/Controllers/WebhookController.php`'da text mesaj handler — `CommandHandler` çağırdıktan sonra (veya komut değilse), `Conversation::firstOrCreate(['channel'=>'telegram','channel_thread_id'=>$chatId], [...defaults: office_id from TelegramUser, contact_id null])` ve `Message::create(['direction'=>'in','channel'=>'telegram','external_id'=>message_id,'body'=>$text,'status'=>'received'])` + `$conversation->touchLastMessage($message)`. Activity yazımı korunur (paralel).
4. **MediaIngestService update:** foto/ses geldiğinde Message'a attachment olarak da yaz — `attachments` JSON kolonu `[{type:'photo'|'voice','url'|'path','duration'?}]`. Whisper transcript varsa `ai_summary`'e koy.
5. **Sidebar:** `resources/views/layouts/partials/sidebar.blade.php` — CRM grubuna "Gelen Kutusu" linki (`route('admin.inbox.index')`), `Conversation::open()->where('unread_count','>',0)->count()` badge'ı.

**Sonra (4.2 + 4.3):**
- 4.2 Drip Campaign engine — campaigns/steps/enrollments + executor
- 4.3 AI çağrı özetleme — Whisper → GPT pipeline, mevcut Activity alanlarına yaz

