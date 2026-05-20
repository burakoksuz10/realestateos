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
