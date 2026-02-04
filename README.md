# RE-OS - Real Estate Operating System

Ultra-profesyonel, AI destekli gayrimenkul yönetim sistemi. Laravel 12 ile geliştirilmiş, modüler mimari.

## 🚀 Özellikler

### 🏠 Portföy & İlan Yönetimi
- Konut, ticari, arsa ve proje ilanları
- Dinamik alan yönetimi
- Medya yönetimi (foto/video/plan/pdf)
- EXIF temizleme ve watermark
- Sürüm geçmişi ve geri alma
- Otomatik broşür üretimi (PDF)

### 👥 CRM + Lead Yönetimi
- Çoklu kaynak entegrasyonu (Meta, Google, portal, WhatsApp)
- Lead scoring ve sıcaklık analizi
- Kanban pipeline görünümü
- Otomatik takip serileri
- Görev ve hatırlatma sistemi

### 🤖 AI Copilot
- Lead-to-Deal asistan
- Arama/WhatsApp/email taslakları
- Uygun portföy shortlist
- Randevu önerisi
- Takip planı oluşturma
- Konuşma zekası (Call AI)
- Müşteri niyet ve bütçe doğrulama

### 📊 Değerleme & Piyasa Zekası
- Bölge bazlı fiyat endeksi
- Emsal analizi
- ROI/kira getirisi hesaplama
- Satılma olasılığı tahmini
- Fiyat düşürme önerileri
- Portföy kalite skoru

### 📢 Pazarlama Otomasyonu
- Kampanya oluşturucu
- AI kreatif üretim
- Meta reklam entegrasyonu
- Reels senaryosu üretimi
- UTM ve dönüşüm izleme

### 🌐 Web Sitesi Builder
- White-label site builder
- Çok dilli destek (AI çeviri)
- Davranış izleme ve heatmap
- SEO optimizasyonu

### 🔗 Entegrasyonlar
- Portal senkronizasyonu (Sahibinden, Hepsiemlak, Emlakjet)
- WhatsApp Business API
- SMS (Netgsm, İleti Merkezi)
- VoIP (Bulutfon, Twilio)
- Ödeme (PayTR, iyzico)
- E-imza
- 360° sanal tur

### 📈 Raporlama & BI
- Dönüşüm hunisi analizi
- Danışman performans raporları
- Portal bazlı performans
- Gelir trendleri

## 🛠 Kurulum

### Gereksinimler
- PHP 8.2+
- MySQL 8.0+ veya PostgreSQL 14+
- Redis (önerilen)
- Node.js 18+
- Composer 2.x

### Adımlar

```bash
# Projeyi klonlayın
git clone https://github.com/your-org/reos.git
cd reos

# Bağımlılıkları yükleyin
composer install
npm install

# Ortam dosyasını oluşturun
cp .env.example .env

# Uygulama anahtarı oluşturun
php artisan key:generate

# Veritabanını yapılandırın (.env dosyasını düzenleyin)
# DB_DATABASE=reos
# DB_USERNAME=root
# DB_PASSWORD=

# Migrasyonları çalıştırın
php artisan migrate

# Seed verilerini yükleyin
php artisan db:seed

# Storage linkini oluşturun
php artisan storage:link

# Frontend varlıklarını derleyin
npm run build

# Geliştirme sunucusunu başlatın
php artisan serve
```

## 🔐 Varsayılan Kullanıcılar

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Super Admin | admin@reos.com | password |
| Agent | agent@reos.com | password |

## 📁 Proje Yapısı

```
reos/
├── app/                    # Laravel uygulama çekirdeği
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
├── modules/                # Modüler yapı
│   ├── Core/              # Tenancy, Auth, Audit
│   ├── RealEstate/        # İlan ve proje yönetimi
│   ├── CRM/               # Müşteri ilişkileri
│   ├── AI/                # Yapay zeka servisleri
│   ├── Integrations/      # Dış entegrasyonlar
│   ├── Websites/          # Site builder
│   └── BI/                # Raporlama
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       └── auth/
└── routes/
```

## 🔧 Konfigürasyon

### AI Servisleri
```env
OPENAI_API_KEY=your-api-key
AI_MODEL=gpt-4-turbo-preview
```

### Portal Entegrasyonları
```env
SAHIBINDEN_API_KEY=
HEPSIEMLAK_API_KEY=
EMLAKJET_API_KEY=
```

### İletişim
```env
WHATSAPP_ACCESS_TOKEN=
SMS_PROVIDER=netgsm
BULUTFON_API_KEY=
```

## 📚 API Dokümantasyonu

API endpoint'leri `/api/v1` prefix'i altında sunulmaktadır.

### Kimlik Doğrulama
```bash
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/user
```

### İlanlar
```bash
GET    /api/realestate/listings
POST   /api/realestate/listings
GET    /api/realestate/listings/{id}
PUT    /api/realestate/listings/{id}
DELETE /api/realestate/listings/{id}
```

### Leads
```bash
GET    /api/crm/leads
POST   /api/crm/leads
GET    /api/crm/leads/{id}
PUT    /api/crm/leads/{id}
POST   /api/crm/leads/{id}/convert
```

## 🧪 Test

```bash
# Tüm testleri çalıştır
php artisan test

# Belirli bir modülü test et
php artisan test --filter=CRM
```

## 🚀 Production Deployment

```bash
# Optimizasyonları çalıştır
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Queue worker başlat
php artisan queue:work --daemon

# Scheduler'ı cron'a ekle
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📄 Lisans

Bu proje özel lisans altındadır. Ticari kullanım için iletişime geçin.

## 🤝 Destek

- 📧 Email: support@reos.com
- 📚 Dokümantasyon: https://docs.reos.com
- 💬 Discord: https://discord.gg/reos

---

**RE-OS** - Gayrimenkul sektörünün geleceği 🏠✨
