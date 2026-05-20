<?php

namespace Modules\Telegram\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Telegram\Models\TelegramUser;
use Modules\Telegram\Services\CallbackHandler;
use Modules\Telegram\Services\CommandHandler;
use Modules\Telegram\Services\MediaIngestService;
use Modules\Telegram\Services\TelegramService;

class WebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
        protected CommandHandler $commands,
        protected CallbackHandler $callbacks,
        protected MediaIngestService $media,
    ) {}

    /**
     * Receive Telegram updates.
     * Telegram POSTs the update body as JSON to this URL.
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::debug('Telegram webhook update', ['update' => $update]);

        // 1) Callback queries (button taps)
        if (!empty($update['callback_query'])) {
            $this->callbacks->handle($update['callback_query']);
            return response()->json(['ok' => true]);
        }

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId   = (string) ($message['chat']['id'] ?? '');
        $text     = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
        $username = $message['from']['username'] ?? null;

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        $linkedUser = TelegramUser::where('telegram_chat_id', $chatId)
            ->where('is_active', true)
            ->first();

        // 2) Media uploads (photo / voice / audio / video / document) → CRM activity
        if ($linkedUser && (isset($message['photo']) || isset($message['voice']) || isset($message['audio']) || isset($message['video']) || isset($message['document']))) {
            $this->media->ingest($message, $linkedUser, $text ?: null);
            return response()->json(['ok' => true]);
        }

        // 3) Text / commands
        if ($text !== '') {
            $this->commands->handle($chatId, $text, $username, $linkedUser);
        }

        return response()->json(['ok' => true]);
    }
}
