<?php

namespace Modules\Telegram\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Telegram\Models\TelegramUser;

class TelegramService
{
    protected string $botToken;
    protected string $apiBase = 'https://api.telegram.org';

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    /**
     * Send a plain text message to a Telegram chat.
     */
    public function sendMessage(string $chatId, string $text, array $options = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot not configured; skipping sendMessage');
            return false;
        }

        try {
            $payload = array_merge([
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $res = Http::timeout(10)->post("{$this->apiBase}/bot{$this->botToken}/sendMessage", $payload);

            return $res->ok();
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a photo by URL with an optional caption.
     */
    public function sendPhoto(string $chatId, string $photoUrl, ?string $caption = null, array $options = []): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram bot not configured; skipping sendPhoto');
            return false;
        }

        try {
            $payload = array_merge([
                'chat_id' => $chatId,
                'photo'   => $photoUrl,
            ], $options);

            if ($caption !== null) {
                $payload['caption']    = mb_substr($caption, 0, 1024);
                $payload['parse_mode'] = $options['parse_mode'] ?? 'HTML';
            }

            $res = Http::timeout(15)->post("{$this->apiBase}/bot{$this->botToken}/sendPhoto", $payload);
            return $res->ok();
        } catch (\Throwable $e) {
            Log::error('Telegram sendPhoto failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Download a file the user sent to the bot. Returns local path or null.
     */
    public function downloadFile(string $fileId, string $destinationDir): ?string
    {
        if (!$this->isConfigured()) return null;

        try {
            $info = Http::timeout(10)->get("{$this->apiBase}/bot{$this->botToken}/getFile", ['file_id' => $fileId])->json();
            $filePath = $info['result']['file_path'] ?? null;
            if (!$filePath) return null;

            $contents = Http::timeout(30)->get("{$this->apiBase}/file/bot{$this->botToken}/{$filePath}")->body();
            if (!$contents) return null;

            if (!is_dir($destinationDir)) {
                @mkdir($destinationDir, 0775, true);
            }

            $name = basename($filePath);
            $full = rtrim($destinationDir, '/') . '/' . uniqid() . '_' . $name;
            file_put_contents($full, $contents);

            return $full;
        } catch (\Throwable $e) {
            Log::error('Telegram downloadFile failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send a message with an inline keyboard (interactive buttons).
     */
    public function sendMessageWithButtons(string $chatId, string $text, array $buttons, array $options = []): bool
    {
        $options['reply_markup'] = json_encode([
            'inline_keyboard' => $buttons,
        ]);
        return $this->sendMessage($chatId, $text, $options);
    }

    /**
     * Answer a callback query (the "loading" spinner that appears when a button is tapped).
     */
    public function answerCallback(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        if (!$this->isConfigured()) return false;

        try {
            $payload = ['callback_query_id' => $callbackQueryId];
            if ($text !== null)  $payload['text']       = mb_substr($text, 0, 200);
            if ($showAlert)      $payload['show_alert'] = true;

            $res = Http::timeout(10)->post("{$this->apiBase}/bot{$this->botToken}/answerCallbackQuery", $payload);
            return $res->ok();
        } catch (\Throwable $e) {
            Log::error('Telegram answerCallback failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a message to all linked Telegram users of a given CRM user.
     */
    public function notifyUser(int $userId, string $text, array $options = []): int
    {
        $sent = 0;
        TelegramUser::where('user_id', $userId)->where('is_active', true)->each(function ($tu) use ($text, $options, &$sent) {
            if ($this->sendMessage($tu->telegram_chat_id, $text, $options)) {
                $sent++;
            }
        });
        return $sent;
    }

    /**
     * Broadcast to every active Telegram-linked CRM user in an office.
     */
    public function notifyOffice(int $officeId, string $text, array $options = []): int
    {
        $sent = 0;
        TelegramUser::query()
            ->whereHas('user', fn ($q) => $q->where('office_id', $officeId))
            ->where('is_active', true)
            ->each(function ($tu) use ($text, $options, &$sent) {
                if ($this->sendMessage($tu->telegram_chat_id, $text, $options)) {
                    $sent++;
                }
            });
        return $sent;
    }

    /**
     * Set the webhook URL where Telegram should deliver updates.
     */
    public function setWebhook(string $url): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'Bot token not configured'];
        }

        try {
            $res = Http::post("{$this->apiBase}/bot{$this->botToken}/setWebhook", ['url' => $url]);
            return $res->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function getWebhookInfo(): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'Bot token not configured'];
        }

        try {
            $res = Http::get("{$this->apiBase}/bot{$this->botToken}/getWebhookInfo");
            return $res->json() ?? ['ok' => false];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generate a one-time pairing code for the current user (so the bot can link).
     */
    public function generatePairingCode(int $userId): string
    {
        $code = strtoupper(\Illuminate\Support\Str::random(6));
        TelegramUser::updateOrCreate(
            ['user_id' => $userId, 'telegram_chat_id' => null],
            ['pairing_code' => $code, 'pairing_expires_at' => now()->addMinutes(15), 'is_active' => false],
        );
        return $code;
    }

    /**
     * Complete pairing when the bot receives /start <code>.
     */
    public function completePairing(string $code, string $chatId, ?string $telegramUsername = null): ?TelegramUser
    {
        $row = TelegramUser::where('pairing_code', $code)
            ->where('pairing_expires_at', '>', now())
            ->first();

        if (!$row) return null;

        $row->update([
            'telegram_chat_id'   => $chatId,
            'telegram_username'  => $telegramUsername,
            'pairing_code'       => null,
            'pairing_expires_at' => null,
            'is_active'          => true,
            'linked_at'          => now(),
        ]);

        return $row->fresh();
    }
}
