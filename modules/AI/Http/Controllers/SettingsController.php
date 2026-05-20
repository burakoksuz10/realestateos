<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Services\AI\AiCreditService;
use Illuminate\Http\Request;
use Modules\AI\Services\AIService;

class SettingsController extends Controller
{
    public function __construct(
        protected AiCreditService $credits,
    ) {
    }

    public function index()
    {
        $officeId = auth()->user()?->office_id;
        $setting = $officeId ? AiSetting::firstOrCreate(['office_id' => $officeId]) : null;
        $credit  = $officeId ? $this->credits->ensure($officeId) : null;

        $monthlyUsage = AiUsageLog::forOffice($officeId)
            ->thisMonth()
            ->selectRaw('COUNT(*) as total_calls, SUM(prompt_tokens) as prompt, SUM(completion_tokens) as completion, SUM(cost_usd) as cost')
            ->first();

        $byFeature = AiUsageLog::forOffice($officeId)
            ->thisMonth()
            ->selectRaw('feature, COUNT(*) as calls, SUM(total_tokens) as tokens, SUM(cost_usd) as cost')
            ->groupBy('feature')
            ->orderByDesc('calls')
            ->get();

        $isEnvKey = !empty(config('reos.ai.openai_key')) && config('reos.ai.openai_key') !== 'sk-placeholder-not-configured';

        return view('ai::settings.index', compact('setting', 'credit', 'monthlyUsage', 'byFeature', 'isEnvKey'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'openai_key'          => 'nullable|string|max:200',
            'openai_organization' => 'nullable|string|max:120',
            'preferred_model'     => 'nullable|string|in:gpt-4o,gpt-4o-mini,gpt-4-turbo,gpt-4-turbo-preview',
            'features_enabled'    => 'nullable|array',
            'monthly_quota'       => 'nullable|integer|min:0|max:1000000',
        ]);

        $officeId = auth()->user()?->office_id;
        abort_unless($officeId, 403, 'Office context missing.');

        $setting = AiSetting::firstOrCreate(['office_id' => $officeId]);

        $payload = collect($validated)
            ->only(['openai_organization', 'preferred_model', 'features_enabled'])
            ->toArray();

        // Only overwrite the key when a new value is provided (don't wipe the existing one).
        if (!empty($validated['openai_key']) && !str_contains($validated['openai_key'], '…')) {
            $payload['openai_key'] = $validated['openai_key'];
        }

        $setting->update($payload);

        if (isset($validated['monthly_quota'])) {
            $credit = $this->credits->ensure($officeId);
            $credit?->update(['monthly_quota' => (int) $validated['monthly_quota']]);
        }

        return back()->with('success', 'AI ayarları güncellendi.');
    }

    public function testConnection(AIService $ai)
    {
        $officeId = auth()->user()?->office_id;
        $ai->withContext($officeId, auth()->id(), 'settings.test');

        if (!$ai->isConfigured()) {
            return response()->json([
                'ok' => false,
                'message' => 'OpenAI API anahtarı yapılandırılmamış.',
            ], 200);
        }

        $reply = $ai->chat([
            ['role' => 'system', 'content' => 'Yalnızca tek bir cümle döndür: bağlantı çalışıyor.'],
            ['role' => 'user',   'content' => 'Test'],
        ], ['max_tokens' => 30, 'model' => config('reos.ai.mini_model', 'gpt-4o-mini')]);

        return response()->json([
            'ok'      => !empty($reply),
            'message' => $reply ?: 'Yanıt alınamadı — anahtar geçersiz olabilir veya kota dolmuş olabilir.',
        ]);
    }

    public function clearKey()
    {
        $officeId = auth()->user()?->office_id;
        abort_unless($officeId, 403);

        $setting = AiSetting::where('office_id', $officeId)->first();
        $setting?->update(['openai_key' => null]);

        return back()->with('success', 'API anahtarı silindi. Sistem .env üzerindeki anahtara geri döndü.');
    }

    public function grantBonus(Request $request)
    {
        $request->validate(['amount' => 'required|integer|min:1|max:100000']);
        $officeId = auth()->user()?->office_id;
        abort_unless($officeId, 403);

        $this->credits->grantExtra($officeId, (int) $request->amount);

        return back()->with('success', $request->amount . ' bonus AI kredisi eklendi.');
    }
}
