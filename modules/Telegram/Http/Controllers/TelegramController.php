<?php

namespace Modules\Telegram\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Telegram\Models\TelegramUser;
use Modules\Telegram\Services\TelegramService;

class TelegramController extends Controller
{
    public function __construct(protected TelegramService $telegram) {}

    public function index()
    {
        $myLinks = TelegramUser::where('user_id', auth()->id())
            ->orderByDesc('is_active')
            ->get();

        $webhook = $this->telegram->isConfigured() ? $this->telegram->getWebhookInfo() : null;

        return view('telegram::index', [
            'links' => $myLinks,
            'configured' => $this->telegram->isConfigured(),
            'webhook' => $webhook,
            'botUsername' => env('TELEGRAM_BOT_USERNAME', ''),
        ]);
    }

    public function pair()
    {
        $code = $this->telegram->generatePairingCode(auth()->id());
        return back()->with('success', "Eşleştirme kodu: <code>{$code}</code> — Telegram bot'una /start {$code} yazın.");
    }

    public function unlink(int $id)
    {
        $row = TelegramUser::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $row->delete();
        return back()->with('success', 'Telegram bağlantısı kaldırıldı.');
    }

    public function setWebhook(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $res = $this->telegram->setWebhook($request->url);
        return back()->with($res['ok'] ?? false ? 'success' : 'warning', 'Webhook sonucu: ' . json_encode($res));
    }
}
