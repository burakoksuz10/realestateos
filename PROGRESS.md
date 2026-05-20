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
| 3 | Sosyal Medya Motoru | ⏳ Bekliyor | Fal.ai entegrasyonu, kart üretici, takvim |
| 4 | İletişim & Yaşam Döngüsü | ⏳ Bekliyor | WhatsApp/SMS/Çağrı/E-posta + drip campaigns |
| 5 | Süreç Otomasyon | ⏳ Bekliyor | Workflow builder, takılan deal uyarıları |
| 6 | İleri Özellikler | ⏳ Bekliyor | Portal sync, brochure, e-imza, TKGM, alıcı/satıcı portal |
| 7 | Cila & Üretim | ⏳ Bekliyor | PWA, performans, test, güvenlik, çoklu dil |

**İlerleme:** 3/8 faz tamamlandı (Faz 2'nin kodu hazır, sadece `.env` bot token + public webhook URL kullanıcı tarafında).

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

## ⏳ FAZ 3 — Sosyal Medya Motoru (BEKLEYEN)

Amaç: Pazarlama otomasyonu — AI ilanları sosyal medyaya hazır hale getirsin.

### Planlanan işler

- [ ] **Fal.ai entegrasyonu** — gökyüzü değiştirme, twilight efekti, declutter, virtual staging
- [ ] **Markalı sosyal kart üretici** (PHP imagick veya browser-shot)
  - Listing card şablonları (4-5 stil): yeni ilan, az önce satıldı, açık ev, fiyat indirimi
  - Otomatik logo/agent foto/iletişim bilgileri
  - Instagram square / story / facebook landscape / X / linkedin boyutları
- [ ] **AI caption üretici** — platform-spesifik (mevcut `ContentService::generateSocialContent` zaten var, UI'a bağla)
- [ ] **Reels/TikTok script üretici** — `ContentService::generateReelsScript` zaten var
- [ ] **İçerik takvimi** — drag-drop scheduler
- [ ] **Çoklu platform yayın** — Instagram + Facebook (Graph API), X, LinkedIn
- [ ] **Postiz entegrasyonu** (alternatif — siz postiz-app çalışıyorsunuz)
- [ ] **Hashtag intelligence** — bölge bazlı, ilan tipi bazlı öneriler
- [ ] **Performans takibi** — like, share, comment, conversion
- [ ] **Unified inbox** — Instagram DM + Facebook Messenger + WhatsApp + Telegram tek yerden

### Kullanılacak modüller
- `modules/SocialMedia/` (mevcut iskelet) — bu fazda etini doldur
- `modules/Advertising/` — Meta Ads entegrasyonu

---

## ⏳ FAZ 4 — İletişim & Yaşam Döngüsü (BEKLEYEN)

Amaç: Tüm iletişim kanalları tek inbox'ta, müşteri yaşam döngüsü otomatik.

### Planlanan işler

- [ ] **WhatsApp Business API** — gerçek mesaj gönderme/alma (mevcut routes var, içleri boş)
- [ ] **SMS provider** — Netgsm / İletimerkezi (Twilio yedek)
- [ ] **Twilio çağrı** — initiate + recording + auto-transcription (Whisper API)
- [ ] **AI çağrı özetleme** — transcript → activity log + AI özet + intent + buying signals
- [ ] **E-posta entegrasyonu** — IMAP fetch + SMTP send + activity threading
- [ ] **Drip campaign engine** — nurture sequence (Mevcut `CopilotService::generateFollowUpPlan` baz alınabilir)
  - Cold lead reactivation (60+ gün hareket yok)
  - Yeni lead onboarding (5 günlük seri)
  - Açık ev davetiyesi
- [ ] **Unified inbox UI** — guzllik'in Sohbetler.tsx pattern'i

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

### Sonra: Faz 3 (Sosyal Medya Motoru)
- Fal.ai entegrasyonu, markalı kart üretici, içerik takvimi
- Veya: kapsam dışı bug — `Modules\CRM\Events\Lead*` ve `Deal*` event stub'larını oluştur (broader CRM tarafı şu an `event(new LeadCreated())`'ta runtime hatası verebilir)

