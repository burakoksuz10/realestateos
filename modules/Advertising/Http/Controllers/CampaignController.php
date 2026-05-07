<?php

namespace Modules\Advertising\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Advertising\Models\Campaign;
use Modules\Advertising\Models\CampaignMetric;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $campaigns = Campaign::where('user_id', $userId)
            ->with('metrics')
            ->orderBy('created_at', 'desc')
            ->get();

        $dailyMetrics = CampaignMetric::whereHas('campaign', fn($q) => $q->where('user_id', $userId))
            ->selectRaw('tarih, SUM(harcama) as harcama, SUM(lead) as lead, SUM(mesaj) as mesaj, SUM(donusum) as donusum, SUM(erisme) as erisme')
            ->groupBy('tarih')
            ->orderBy('tarih')
            ->get();

        $totals = [
            'harcama' => $campaigns->sum(fn($c) => $c->metrics->sum('harcama')),
            'result_count' => $campaigns->sum(fn($c) => $c->metrics->sum('lead') + $c->metrics->sum('mesaj') + $c->metrics->sum('donusum')),
            'erisme' => $campaigns->sum(fn($c) => $c->metrics->sum('erisme')),
            'avg_health' => $campaigns->count() > 0 ? round($campaigns->avg('health_score')) : 0,
        ];
        $totals['cost_per_result'] = $totals['result_count'] > 0
            ? round($totals['harcama'] / $totals['result_count'], 2) : 0;

        return view('advertising::index', compact('campaigns', 'dailyMetrics', 'totals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hedef' => 'required|in:randevu,mesaj,tanitim,etkilesim,trafik',
            'budget' => 'required|numeric|min:0',
            'city' => 'nullable|string|max:100',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['durum'] = 'PAUSED';
        $validated['health_score'] = 50;

        $campaign = Campaign::create($validated);

        return response()->json(['success' => true, 'campaign' => $campaign->load('metrics')]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hedef' => 'required|in:randevu,mesaj,tanitim,etkilesim,trafik',
            'budget' => 'required|numeric|min:0',
            'city' => 'nullable|string|max:100',
        ]);

        $campaign->update($validated);

        return response()->json(['success' => true, 'campaign' => $campaign->load('metrics')]);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, Campaign $campaign)
    {
        $newStatus = $campaign->durum === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';
        $campaign->update(['durum' => $newStatus]);
        return response()->json(['success' => true, 'durum' => $newStatus]);
    }

    public function analyze(Request $request, Campaign $campaign)
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'error' => 'OpenAI API anahtarı gereklidir.',
            ]);
        }

        $metrics = $campaign->metrics()->orderByDesc('tarih')->take(30)->get();
        $totals = $campaign->totals;

        $prompt = "Şu kampanya verilerini analiz et ve Türkçe öneriler sun:\n"
            . "Kampanya: {$campaign->name}\n"
            . "Hedef: {$campaign->hedef}\n"
            . "Toplam Harcama: {$totals['harcama']} TL\n"
            . "Toplam Sonuç: {$totals['result_count']}\n"
            . "Sonuç Maliyeti: {$totals['cost_per_result']} TL\n"
            . "Sağlık Skoru: {$campaign->health_score}/100\n"
            . "\nGüçlü yanlar, zayıf yanlar ve öneriler başlıkları altında kısa analiz yap.";

        try {
            $client = \OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sen bir Meta reklam uzmanısın. Türkçe analiz yap.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 800,
            ]);
            $analysis = $response->choices[0]->message->content;

            $campaign->update(['latest_ai_analysis' => ['analysis' => $analysis, 'created_at' => now()]]);

            return response()->json(['success' => true, 'analysis' => $analysis, 'created_at' => now()->toISOString()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function sync(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Meta senkronizasyonu tamamlandı. (Demo mod - gerçek entegrasyon için Meta API anahtarı gereklidir.)',
            'imported' => 0,
            'updated' => 0,
            'synced' => 0,
        ]);
    }
}
