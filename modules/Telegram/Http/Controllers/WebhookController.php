<?php

namespace Modules\Telegram\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Telegram\Services\TelegramService;

class WebhookController extends Controller
{
    public function __construct(protected TelegramService $telegram) {}

    /**
     * Receive Telegram updates.
     * Telegram POSTs the update body as JSON to this URL.
     * Phase 2 (next session): wire /start, /leads, /today, /hot, /search, etc.
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram webhook received', ['update' => $update]);

        $message = $update['message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text   = trim((string) ($message['text'] ?? ''));
        $username = $message['from']['username'] ?? null;

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        // /start COMMAND
        if (str_starts_with($text, '/start')) {
            $parts = preg_split('/\s+/', $text, 2);
            $code  = $parts[1] ?? null;

            if ($code) {
                $row = $this->telegram->completePairing(strtoupper($code), $chatId, $username);
                if ($row) {
                    $this->telegram->sendMessage($chatId, "✅ Hesabınız RE-OS ile bağlandı, {$row->user?->name}! Artık size buradan bildirim göndereceğiz.\n\nKullanılabilir komutlar:\n/today — Bugünkü görevlerim\n/leads — Aktif lead'lerim\n/hot — Sıcak lead'ler\n/help — Yardım");
                } else {
                    $this->telegram->sendMessage($chatId, "❌ Eşleştirme kodu geçersiz veya süresi dolmuş.\nRE-OS'da /admin/telegram sayfasından yeni bir kod alın.");
                }
            } else {
                $this->telegram->sendMessage($chatId, "👋 Hoş geldiniz. RE-OS hesabınızı bağlamak için RE-OS sisteminden eşleştirme kodu alın ve /start KOD şeklinde gönderin.");
            }
        } elseif ($text === '/help') {
            $this->telegram->sendMessage($chatId, "Komutlar:\n/today — Bugünkü görevler\n/leads — Aktif lead'ler\n/hot — Sıcak lead'ler\n/search KRİTER — Doğal dil ilan ara");
        } else {
            // Other commands handled in Phase 2.
            $this->telegram->sendMessage($chatId, "Komutu anlamadım. Yardım için /help yazın.");
        }

        return response()->json(['ok' => true]);
    }
}
