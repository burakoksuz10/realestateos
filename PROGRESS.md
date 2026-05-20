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
| 2 | Telegram Operasyon Merkezi | 🟡 İskelet hazır | Komutlar + bot token sonraki oturum |
| 3 | Sosyal Medya Motoru | ⏳ Bekliyor | Fal.ai entegrasyonu, kart üretici, takvim |
| 4 | İletişim & Yaşam Döngüsü | ⏳ Bekliyor | WhatsApp/SMS/Çağrı/E-posta + drip campaigns |
| 5 | Süreç Otomasyon | ⏳ Bekliyor | Workflow builder, takılan deal uyarıları |
| 6 | İleri Özellikler | ⏳ Bekliyor | Portal sync, brochure, e-imza, TKGM, alıcı/satıcı portal |
| 7 | Cila & Üretim | ⏳ Bekliyor | PWA, performans, test, güvenlik, çoklu dil |

**İlerleme:** 2/8 faz tamamlandı, 1 faz iskelet seviyesinde.

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

## 🟡 FAZ 2 — Telegram Operasyon Merkezi (İSKELET HAZIR)

Amaç: Saha danışmanları gerçek zamanlı operasyonda olsun.

### Tamamlanan işler (iskelet)

- [x] **`modules/Telegram/` yeni modül oluşturuldu**
- [x] **`telegram_users` tablosu** — kullanıcı-bot eşleştirme + pairing code akışı
- [x] **TelegramService** — sendMessage, notifyUser, notifyOffice, setWebhook, generatePairingCode, completePairing
- [x] **TelegramController** — /admin/telegram sayfası, pairing kod oluştur, bağlantı kaldır
- [x] **WebhookController** — `/start KOD`, `/help` komutları
- [x] **Bağlama akışı tam çalışıyor:** UI'dan kod al → Telegram'da bot'a `/start KOD` → bağlandı

### Sonraki oturuma kalan işler

- [ ] **.env'e ekle:** `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME` (BotFather'dan alın)
- [ ] **Webhook public URL:** ngrok / cloudflare tunnel ile lokali expose et
- [ ] **/leads komutu** — aktif lead'leri listele
- [ ] **/today komutu** — bugün arayacaklar + görevler
- [ ] **/hot komutu** — sıcak lead'ler (score >80)
- [ ] **/search KRİTER** — doğal dil ilan arama (CopilotService::semanticSearch reuse)
- [ ] **/listing REF** — ilan kartı (foto + bilgi)
- [ ] **Bildirim event'leri:**
  - Yeni lead atandığında otomatik mesaj
  - Hot lead alarmı (score >80 olunca)
  - Görev hatırlatması
  - Deal kapandı/kapanmaya yakın
- [ ] **Sabah brifingi (08:30)** — her agent için kişiselleştirilmiş özet (cron)
- [ ] **Ofis kanalı** — manager view: günlük ofis özeti
- [ ] **Foto/ses → CRM** — agent bot'a forward ederse lead aktivitesine düşsün
- [ ] **Interactive buttons** — "Bu lead'i bana ata", "Görev olarak ekle"

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

---

## 🎯 Şu An Yapılacaklar (En Yakın Hedef)

1. `.env` dosyasına `OPENAI_API_KEY` ekle
2. `php artisan queue:work` başlat
3. Tarayıcıdan `/admin/ai/settings` → "Bağlantıyı Test Et"
4. Yeni bir lead oluştur → AI analizinin geldiğini gör
5. Floating Copilot widget'a soru sor
6. Hazır olunca: Faz 2'yi bitirmek için Telegram bot token al (@BotFather)

