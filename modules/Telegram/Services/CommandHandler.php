<?php

namespace Modules\Telegram\Services;

use Illuminate\Support\Facades\Log;
use Modules\AI\Services\MatchingService;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Task;
use Modules\RealEstate\Models\Listing;
use Modules\Telegram\Models\TelegramUser;

class CommandHandler
{
    public function __construct(
        protected TelegramService $telegram,
        protected MatchingService $matching,
    ) {}

    /**
     * Route an incoming text message to the right command handler.
     * Returns true if the message was handled.
     */
    public function handle(string $chatId, string $text, ?string $username, ?TelegramUser $linkedUser): bool
    {
        $text = trim($text);
        if ($text === '') return false;

        // /start KOD — handled here for centralization
        if (str_starts_with($text, '/start')) {
            return $this->handleStart($chatId, $text, $username);
        }

        if ($text === '/help') {
            return $this->handleHelp($chatId, $linkedUser !== null);
        }

        // All other commands require a linked account.
        if (!$linkedUser) {
            $this->telegram->sendMessage($chatId, "🔒 Önce hesabınızı bağlayın.\nRE-OS'da <b>/admin/telegram</b> sayfasından eşleştirme kodu alın, sonra <code>/start KOD</code> komutunu gönderin.");
            return true;
        }

        return match (true) {
            $text === '/today'                    => $this->handleToday($chatId, $linkedUser),
            $text === '/leads'                    => $this->handleLeads($chatId, $linkedUser),
            $text === '/hot'                      => $this->handleHot($chatId, $linkedUser),
            str_starts_with($text, '/search')     => $this->handleSearch($chatId, $text, $linkedUser),
            str_starts_with($text, '/listing')    => $this->handleListing($chatId, $text, $linkedUser),
            default                               => $this->handleUnknown($chatId),
        };
    }

    protected function handleStart(string $chatId, string $text, ?string $username): bool
    {
        $parts = preg_split('/\s+/', $text, 2);
        $code  = $parts[1] ?? null;

        if (!$code) {
            $this->telegram->sendMessage($chatId, "👋 Hoş geldiniz! RE-OS hesabınızı bağlamak için RE-OS sisteminden eşleştirme kodu alıp <code>/start KOD</code> şeklinde gönderin.");
            return true;
        }

        $row = $this->telegram->completePairing(strtoupper($code), $chatId, $username);
        if (!$row) {
            $this->telegram->sendMessage($chatId, "❌ Eşleştirme kodu geçersiz veya süresi dolmuş.\nRE-OS'da <b>/admin/telegram</b> sayfasından yeni bir kod alın.");
            return true;
        }

        $name = $row->user?->name ?? 'kullanıcı';
        $this->telegram->sendMessage($chatId, "✅ Hesabınız bağlandı, {$name}!\n\n<b>Kullanılabilir komutlar</b>\n/today — Bugünkü görevlerim\n/leads — Aktif lead'lerim\n/hot — Sıcak lead'ler (skor > 80)\n/search KRİTER — Doğal dil ilan ara\n/listing REF — İlan kartını getir\n/help — Yardım");
        return true;
    }

    protected function handleHelp(string $chatId, bool $linked): bool
    {
        $body = "<b>RE-OS Telegram komutları</b>\n\n";
        if (!$linked) {
            $body .= "🔒 Hesabınız henüz bağlı değil.\nÖnce RE-OS'da <b>/admin/telegram</b> sayfasından kod alın, sonra <code>/start KOD</code> gönderin.\n\n";
        }
        $body .= "/today — Bugünkü görevlerim ve aramalarım\n";
        $body .= "/leads — Aktif lead'lerim\n";
        $body .= "/hot — Sıcak lead'ler (skor > 80)\n";
        $body .= "/search KRİTER — Doğal dil ilan arama\n";
        $body .= "  örn: <code>/search Beşiktaş'ta 2+1 deniz manzaralı kiralık</code>\n";
        $body .= "/listing REF — İlan kartı (referans no ile)\n";
        $body .= "/help — Bu mesaj";
        $this->telegram->sendMessage($chatId, $body);
        return true;
    }

    protected function handleToday(string $chatId, TelegramUser $tu): bool
    {
        $userId = $tu->user_id;

        $tasks = Task::where('assigned_to', $userId)
            ->whereDate('due_date', today())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
            ->limit(10)
            ->get();

        $hotLeads = Lead::where('assigned_to', $userId)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where('score', '>=', 70)->orWhere('ai_score', '>=', 70);
            })
            ->orderByDesc('ai_score')
            ->limit(5)
            ->get();

        $body = "📅 <b>Bugün — " . today()->translatedFormat('d F Y, l') . "</b>\n\n";

        if ($tasks->isEmpty() && $hotLeads->isEmpty()) {
            $body .= "🎉 Bugüne özel görev veya sıcak lead yok. İyi bir gün geçirin!";
        } else {
            if ($tasks->isNotEmpty()) {
                $body .= "<b>📋 Görevler (" . $tasks->count() . ")</b>\n";
                foreach ($tasks as $task) {
                    $emoji = match ($task->priority) {
                        'urgent' => '🔴',
                        'high'   => '🟠',
                        'medium' => '🟡',
                        default  => '⚪',
                    };
                    $time = $task->due_time?->format('H:i') ?? '';
                    $body .= "{$emoji} " . e($task->title) . ($time ? " — {$time}" : '') . "\n";
                }
                $body .= "\n";
            }

            if ($hotLeads->isNotEmpty()) {
                $body .= "<b>🔥 Takip edilecek sıcak lead'ler</b>\n";
                foreach ($hotLeads as $lead) {
                    $score = $lead->ai_score ?: $lead->score ?: 0;
                    $body .= "• #{$lead->id} " . e($lead->title ?: 'Lead') . " — skor <b>{$score}</b>\n";
                }
            }
        }

        $this->telegram->sendMessage($chatId, $body);
        return true;
    }

    protected function handleLeads(string $chatId, TelegramUser $tu): bool
    {
        $leads = Lead::where('assigned_to', $tu->user_id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get();

        if ($leads->isEmpty()) {
            $this->telegram->sendMessage($chatId, "📭 Aktif lead'iniz yok.");
            return true;
        }

        $body = "📋 <b>Aktif lead'leriniz (" . $leads->count() . ")</b>\n\n";
        foreach ($leads as $lead) {
            $score = $lead->ai_score ?: $lead->score ?: 0;
            $temp  = $this->tempEmoji($score);
            $body .= "{$temp} #{$lead->id} " . e($lead->title ?: 'Lead') . "\n";
            $body .= "    Skor: <b>{$score}</b> • Durum: " . e($lead->status) . "\n";
            if ($lead->last_activity_at) {
                $body .= "    Son hareket: " . $lead->last_activity_at->diffForHumans() . "\n";
            }
            $body .= "\n";
        }

        $this->telegram->sendMessage($chatId, $body);
        return true;
    }

    protected function handleHot(string $chatId, TelegramUser $tu): bool
    {
        $leads = Lead::where('assigned_to', $tu->user_id)
            ->whereNotIn('status', ['converted', 'lost'])
            ->where(function ($q) {
                $q->where('ai_score', '>=', 80)->orWhere('score', '>=', 80);
            })
            ->orderByDesc('ai_score')
            ->limit(10)
            ->get();

        if ($leads->isEmpty()) {
            $this->telegram->sendMessage($chatId, "🧊 Şu an skoru 80+ olan sıcak lead yok.");
            return true;
        }

        $body = "🔥 <b>Sıcak lead'ler (skor ≥ 80)</b>\n\n";
        foreach ($leads as $lead) {
            $score = $lead->ai_score ?: $lead->score ?: 0;
            $body .= "🔥 #{$lead->id} " . e($lead->title ?: 'Lead') . " — <b>{$score}</b>\n";
            $contact = $lead->contact;
            if ($contact) {
                $body .= "    👤 " . e(trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''))) . "\n";
                if ($contact->phone) $body .= "    📞 " . e($contact->phone) . "\n";
            }
            $signals = $lead->intent_signals ?? [];
            if (!empty($signals) && is_array($signals)) {
                $top = array_slice($signals, 0, 3);
                $body .= "    💡 " . e(implode(' • ', array_map(fn($s) => is_array($s) ? ($s['label'] ?? json_encode($s)) : $s, $top))) . "\n";
            }
            $body .= "\n";
        }

        $this->telegram->sendMessage($chatId, $body);
        return true;
    }

    protected function handleSearch(string $chatId, string $text, TelegramUser $tu): bool
    {
        $query = trim(substr($text, strlen('/search')));
        if ($query === '') {
            $this->telegram->sendMessage($chatId, "Kullanım: <code>/search KRİTER</code>\nÖrnek: <code>/search Beşiktaş'ta 2+1 deniz manzaralı kiralık</code>");
            return true;
        }

        try {
            $listings = $this->matching->semanticSearch($query, 5);
        } catch (\Throwable $e) {
            Log::error('Telegram /search failed', ['error' => $e->getMessage(), 'query' => $query]);
            $this->telegram->sendMessage($chatId, "❌ Arama sırasında bir hata oluştu. Birazdan tekrar deneyin.");
            return true;
        }

        if ($listings->isEmpty()) {
            $this->telegram->sendMessage($chatId, "🔍 <i>" . e($query) . "</i> için sonuç bulunamadı.");
            return true;
        }

        $body = "🔍 <b>" . e($query) . "</b> — " . $listings->count() . " sonuç\n\n";
        foreach ($listings as $listing) {
            $title = is_array($listing->title) ? ($listing->title['tr'] ?? reset($listing->title)) : $listing->title;
            $price = $listing->price ? number_format((float)$listing->price, 0, ',', '.') . ' ' . ($listing->price_currency ?: '₺') : '—';
            $loc   = trim(($listing->district ?: '') . ' / ' . ($listing->city ?: ''), ' /');
            $body .= "🏠 <b>" . e($listing->reference_no ?: '#' . $listing->id) . "</b> — " . e((string)$title) . "\n";
            $body .= "    💰 {$price}";
            if ($listing->room_count) $body .= " • 🛏 " . e((string)$listing->room_count);
            if ($listing->gross_sqm)  $body .= " • 📐 " . e((string)$listing->gross_sqm) . " m²";
            $body .= "\n";
            if ($loc) $body .= "    📍 " . e($loc) . "\n";
            $body .= "\n";
        }
        $body .= "Detay için: <code>/listing " . e($listings->first()->reference_no ?: (string)$listings->first()->id) . "</code>";

        $this->telegram->sendMessage($chatId, $body);
        return true;
    }

    protected function handleListing(string $chatId, string $text, TelegramUser $tu): bool
    {
        $ref = trim(substr($text, strlen('/listing')));
        if ($ref === '') {
            $this->telegram->sendMessage($chatId, "Kullanım: <code>/listing REF_NO</code>");
            return true;
        }

        $listing = Listing::where('reference_no', $ref)
            ->orWhere('id', is_numeric($ref) ? (int)$ref : 0)
            ->first();

        if (!$listing) {
            $this->telegram->sendMessage($chatId, "❌ <code>" . e($ref) . "</code> referansıyla ilan bulunamadı.");
            return true;
        }

        $title = is_array($listing->title) ? ($listing->title['tr'] ?? reset($listing->title)) : $listing->title;
        $desc  = is_array($listing->description) ? ($listing->description['tr'] ?? reset($listing->description)) : $listing->description;
        $desc  = $desc ? mb_substr(strip_tags((string)$desc), 0, 400) . (mb_strlen((string)$desc) > 400 ? '…' : '') : '';
        $price = $listing->price ? number_format((float)$listing->price, 0, ',', '.') . ' ' . ($listing->price_currency ?: '₺') : '—';

        $body  = "🏠 <b>" . e((string)$title) . "</b>\n";
        $body .= "Ref: <code>" . e($listing->reference_no ?: (string)$listing->id) . "</code>\n\n";
        $body .= "💰 <b>{$price}</b>";
        if ($listing->listing_type) $body .= " — " . e($listing->listing_type);
        $body .= "\n";
        if ($listing->room_count)  $body .= "🛏 " . e((string)$listing->room_count) . " oda\n";
        if ($listing->gross_sqm)   $body .= "📐 " . e((string)$listing->gross_sqm) . " m² (brüt)\n";
        if ($listing->city || $listing->district) {
            $body .= "📍 " . e(trim(($listing->district ?: '') . ' / ' . ($listing->city ?: ''), ' /')) . "\n";
        }
        if ($desc) {
            $body .= "\n" . e($desc);
        }

        // Try to attach the first media (photo) if available.
        try {
            $media = method_exists($listing, 'getFirstMediaUrl') ? $listing->getFirstMediaUrl('photos') : null;
        } catch (\Throwable $e) {
            $media = null;
        }

        if ($media) {
            $this->telegram->sendPhoto($chatId, $media, $body);
        } else {
            $this->telegram->sendMessage($chatId, $body);
        }
        return true;
    }

    protected function handleUnknown(string $chatId): bool
    {
        $this->telegram->sendMessage($chatId, "Komutu anlamadım. Yardım için <b>/help</b> yazın.");
        return true;
    }

    protected function tempEmoji(int $score): string
    {
        if ($score >= 80) return '🔥';
        if ($score >= 60) return '🟠';
        if ($score >= 40) return '🟡';
        return '🧊';
    }
}
