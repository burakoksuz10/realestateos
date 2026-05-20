<?php

namespace Modules\SocialMedia\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use Modules\RealEstate\Models\Listing;

/**
 * Markalı sosyal kart üretici.
 *
 * Şablonlar: yeni_ilan, satildi, acik_ev, fiyat_indirimi
 * Boyutlar: square (1080x1080), story (1080x1920), landscape (1200x630)
 *
 * Çıktı: storage/app/public/social-cards/{ts}_{id}_{template}_{size}.jpg
 * Erişim: /storage/social-cards/...
 */
class SocialCardService
{
    /** Şablon → [badge yazısı, accent rengi] */
    protected const TEMPLATES = [
        'yeni_ilan' => ['YENİ İLAN', '#10b981'],
        'satildi' => ['AZ ÖNCE SATILDI', '#ef4444'],
        'acik_ev' => ['AÇIK EV', '#8b5cf6'],
        'fiyat_indirimi' => ['FİYAT İNDİRİMİ', '#f59e0b'],
    ];

    /** Boyut → [width, height] */
    protected const SIZES = [
        'square' => [1080, 1080],
        'story' => [1080, 1920],
        'landscape' => [1200, 630],
    ];

    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function generate(Listing $listing, string $template = 'yeni_ilan', string $size = 'square'): array
    {
        if (!isset(self::TEMPLATES[$template])) {
            return ['success' => false, 'error' => "Bilinmeyen şablon: {$template}"];
        }
        if (!isset(self::SIZES[$size])) {
            return ['success' => false, 'error' => "Bilinmeyen boyut: {$size}"];
        }

        [$badge, $accent] = self::TEMPLATES[$template];
        [$w, $h] = self::SIZES[$size];

        $photoPath = $this->resolveListingPhoto($listing);
        $img = $photoPath
            ? $this->manager->read($photoPath)
            : $this->manager->create($w, $h)->fill('#0f172a');

        $img->cover($w, $h);

        $this->drawGradientOverlay($img, $w, $h);
        $this->drawBadge($img, $badge, $accent, $size);
        $this->drawListingInfo($img, $listing, $size, $template);
        $this->drawBrandFooter($img, $listing, $size);

        $filename = sprintf(
            'social-cards/%s_%d_%s_%s.jpg',
            now()->format('Ymd_His'),
            $listing->id,
            $template,
            $size,
        );

        Storage::disk('public')->put($filename, (string) $img->toJpeg(85));

        return [
            'success' => true,
            'image_url' => Storage::disk('public')->url($filename),
            'path' => $filename,
            'template' => $template,
            'size' => $size,
        ];
    }

    protected function resolveListingPhoto(Listing $listing): ?string
    {
        try {
            $media = $listing->getFirstMedia('photos');
            if ($media && file_exists($media->getPath())) {
                return $media->getPath();
            }
        } catch (\Throwable $e) {
            // sessizce fallback
        }
        return null;
    }

    protected function drawGradientOverlay(ImageInterface $img, int $w, int $h): void
    {
        // GD'de hazır gradient yok — alt yarıyı koyulaştırmak için
        // 4 yatay band çiz, gitgide daha az saydam.
        $bandHeight = (int) ceil($h / 4);
        $opacities = [10, 35, 60, 85];
        for ($i = 0; $i < 4; $i++) {
            $img->drawRectangle(0, $h - ($bandHeight * (4 - $i)), function ($r) use ($bandHeight, $w, $opacities, $i) {
                $r->size($w, $bandHeight);
                // intervention v3 — fillColor desteklenir; alpha hex ile.
                $alphaHex = str_pad(dechex((int) round($opacities[$i] * 2.55)), 2, '0', STR_PAD_LEFT);
                $r->background('#000000' . $alphaHex);
            });
        }
    }

    protected function drawBadge(ImageInterface $img, string $label, string $accent, string $size): void
    {
        [$w, $h] = self::SIZES[$size];
        $padX = 28;
        $padY = 14;
        $fontSize = $size === 'story' ? 40 : 32;
        $x = $size === 'landscape' ? 32 : 48;
        $y = $size === 'landscape' ? 32 : 48;

        // Yaklaşık genişlik için 0.55 * fontSize * karakter sayısı
        $textWidth = (int) (mb_strlen($label) * $fontSize * 0.55);
        $boxWidth = $textWidth + ($padX * 2);
        $boxHeight = $fontSize + ($padY * 2);

        $img->drawRectangle($x, $y, function ($r) use ($boxWidth, $boxHeight, $accent) {
            $r->size($boxWidth, $boxHeight);
            $r->background($accent);
        });

        $img->text($label, $x + $padX, $y + $padY + $fontSize - 4, function (FontFactory $f) use ($fontSize) {
            $f->filename($this->boldFontPath());
            $f->size($fontSize);
            $f->color('#ffffff');
        });
    }

    protected function drawListingInfo(ImageInterface $img, Listing $listing, string $size, string $template): void
    {
        [$w, $h] = self::SIZES[$size];
        $marginX = $size === 'landscape' ? 40 : 56;
        $titleSize = $size === 'story' ? 64 : ($size === 'landscape' ? 40 : 52);
        $metaSize = $size === 'story' ? 36 : 28;
        $priceSize = $size === 'story' ? 88 : ($size === 'landscape' ? 56 : 72);

        $title = $this->truncate((string) ($listing->title ?? 'İlan'), $size === 'story' ? 40 : 32);
        $location = trim(($listing->district ?? '') . ', ' . ($listing->city ?? ''), ', ');

        // Başlık - alttan yukarı
        $titleY = $h - ($size === 'story' ? 380 : 240);
        $img->text($title, $marginX, $titleY, function (FontFactory $f) use ($titleSize) {
            $f->filename($this->boldFontPath());
            $f->size($titleSize);
            $f->color('#ffffff');
        });

        // Lokasyon
        if ($location) {
            $img->text('📍 ' . $location, $marginX, $titleY + $titleSize + 16, function (FontFactory $f) use ($metaSize) {
                $f->filename($this->regularFontPath());
                $f->size($metaSize);
                $f->color('#e2e8f0');
            });
        }

        // Oda + m²
        $meta = [];
        if ($listing->room_count) $meta[] = $listing->room_count . ' oda';
        if ($listing->gross_sqm) $meta[] = $listing->gross_sqm . ' m²';
        if (count($meta)) {
            $img->text(implode('  •  ', $meta), $marginX, $titleY + $titleSize + 16 + $metaSize + 14, function (FontFactory $f) use ($metaSize) {
                $f->filename($this->regularFontPath());
                $f->size((int) round($metaSize * 0.85));
                $f->color('#cbd5e1');
            });
        }

        // Fiyat — alt sağ
        $price = $this->formatPrice($listing, $template);
        if ($price) {
            $img->text($price, $w - $marginX, $h - ($size === 'story' ? 130 : 90), function (FontFactory $f) use ($priceSize, $template) {
                $f->filename($this->boldFontPath());
                $f->size($priceSize);
                $f->color($template === 'fiyat_indirimi' ? '#fbbf24' : '#ffffff');
                $f->align('right');
            });
        }
    }

    protected function drawBrandFooter(ImageInterface $img, Listing $listing, string $size): void
    {
        [$w, $h] = self::SIZES[$size];
        $marginX = $size === 'landscape' ? 40 : 56;
        $fontSize = $size === 'story' ? 28 : 22;

        $officeName = optional($listing->office)->name ?? 'RE-OS Emlak';
        $ref = $listing->reference_no ? 'Ref: ' . $listing->reference_no : '';

        $footerText = $officeName . ($ref ? '  •  ' . $ref : '');
        $img->text($footerText, $marginX, $h - ($size === 'story' ? 50 : 40), function (FontFactory $f) use ($fontSize) {
            $f->filename($this->regularFontPath());
            $f->size($fontSize);
            $f->color('#94a3b8');
        });
    }

    protected function formatPrice(Listing $listing, string $template): ?string
    {
        if (!$listing->price) return null;

        $currency = match ($listing->price_currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => '₺',
        };

        $formatted = number_format((float) $listing->price, 0, ',', '.');

        if ($template === 'fiyat_indirimi' && $listing->original_price && $listing->original_price > $listing->price) {
            $old = number_format((float) $listing->original_price, 0, ',', '.');
            return $currency . $formatted . "\n" . 'Eski: ' . $currency . $old;
        }

        return $currency . $formatted;
    }

    protected function truncate(string $text, int $limit): string
    {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 1) . '…' : $text;
    }

    protected function boldFontPath(): string
    {
        return base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
    }

    protected function regularFontPath(): string
    {
        return base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
    }

    public static function availableTemplates(): array
    {
        return array_keys(self::TEMPLATES);
    }

    public static function availableSizes(): array
    {
        return array_keys(self::SIZES);
    }
}
