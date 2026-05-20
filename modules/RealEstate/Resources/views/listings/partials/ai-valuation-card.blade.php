@php
    $val = is_array($listing->ai_valuation) ? $listing->ai_valuation : [];
    $currency = $listing->price_currency ?? '₺';
    $fmt = fn($v) => $v ? number_format((float) $v, 0, ',', '.') : '-';

    $aiInsights = $val['ai_analysis'] ?? [];
    $marketPos = $aiInsights['market_position'] ?? null;
    $marketPosClass = match($marketPos) {
        'premium' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
        'budget'  => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
        default   => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
    };
    $marketPosLabel = match($marketPos) {
        'premium' => 'Premium Segment',
        'budget'  => 'Ekonomik Segment',
        'average' => 'Orta Segment',
        default   => null,
    };

    $sale = $val['sale_probability'] ?? [];
    $saleProb = $sale['probability'] ?? null;
    $saleProbClass = match(true) {
        $saleProb === null  => 'text-gray-400',
        $saleProb >= 70     => 'text-green-400',
        $saleProb >= 40     => 'text-yellow-400',
        default             => 'text-red-400',
    };

    $trend = $val['market_trends'] ?? [];
    $trendDir = $trend['trend_direction'] ?? null;
    $trendPct = $trend['trend_percentage'] ?? null;
    $trendClass = match($trendDir) {
        'up'   => 'text-green-400',
        'down' => 'text-red-400',
        default => 'text-gray-400',
    };
    $trendIcon = match($trendDir) {
        'up'   => 'M7 17l9.2-9.2M17 17V7H7',
        'down' => 'M17 7l-9.2 9.2M7 7v10h10',
        default => 'M5 12h14',
    };

    $recs = $val['price_recommendations'] ?? [];
    $confidence = $val['confidence_score'] ?? null;
@endphp

<div id="ai-valuation-card" class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Değerleme</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400">Komparable + market trend + AI yorumu</p>
            </div>
        </div>
        @if(!empty($val))
            <button type="button"
                    onclick="generateValuation({{ $listing->id }}, this)"
                    class="text-xs text-gray-500 dark:text-dark-400 hover:text-emerald-400 flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span data-valuation-label>Yeniden Hesapla</span>
            </button>
        @endif
    </div>

    @if(empty($val))
        <div class="text-center py-8 px-4 border-2 border-dashed border-gray-200 dark:border-dark-700 rounded-xl">
            <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-gray-600 dark:text-dark-300 mb-1">Bu ilan için henüz AI değerlemesi yok.</p>
            <p class="text-xs text-gray-500 dark:text-dark-400 mb-4">Komparable satışlar, bölge trendleri ve AI yorumu ile tahmini değer hesaplanır.</p>
            <button type="button"
                    onclick="generateValuation({{ $listing->id }}, this)"
                    class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white text-sm font-medium rounded-xl transition-colors">
                <span data-valuation-label>AI Değerleme Yap</span>
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="md:col-span-2 bg-white dark:bg-dark-900 rounded-xl p-4 border border-gray-200 dark:border-dark-700/50">
                <p class="text-xs text-gray-500 dark:text-dark-400 mb-1">Tahmini Değer</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $fmt($val['estimated_value'] ?? null) }}
                    <span class="text-base font-normal text-gray-500">{{ $currency }}</span>
                </p>
                @if(isset($val['price_range']))
                    <p class="text-xs text-gray-500 dark:text-dark-400 mt-1">
                        Aralık: {{ $fmt($val['price_range']['min'] ?? null) }} – {{ $fmt($val['price_range']['max'] ?? null) }} {{ $currency }}
                    </p>
                @endif
                @if($listing->price)
                    @php
                        $diff = $val['estimated_value'] ? (($listing->price - $val['estimated_value']) / $val['estimated_value']) * 100 : null;
                        $diffClass = $diff === null ? 'text-gray-400' : ($diff > 5 ? 'text-red-400' : ($diff < -5 ? 'text-green-400' : 'text-gray-400'));
                    @endphp
                    @if($diff !== null)
                        <p class="text-xs {{ $diffClass }} mt-2 font-medium">
                            Mevcut fiyat ({{ $fmt($listing->price) }} {{ $currency }}) tahmini değerin
                            @if($diff > 0) %{{ number_format(abs($diff), 1) }} üstünde @elseif($diff < 0) %{{ number_format(abs($diff), 1) }} altında @else neredeyse aynı @endif
                        </p>
                    @endif
                @endif
            </div>

            <div class="bg-white dark:bg-dark-900 rounded-xl p-4 border border-gray-200 dark:border-dark-700/50">
                <p class="text-xs text-gray-500 dark:text-dark-400 mb-1">Güven Skoru</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    %{{ $confidence !== null ? round($confidence * 100) : '-' }}
                </p>
                @if(isset($val['comparables']))
                    <p class="text-xs text-gray-500 dark:text-dark-400 mt-1">
                        {{ is_countable($val['comparables']) ? count($val['comparables']) : 0 }} komparable
                    </p>
                @endif
                @if($marketPosLabel)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] border {{ $marketPosClass }}">{{ $marketPosLabel }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
            <div class="bg-white dark:bg-dark-900 rounded-lg p-3 border border-gray-200 dark:border-dark-700/50">
                <p class="text-[10px] text-gray-500 dark:text-dark-400 uppercase tracking-wide mb-1">Satış İhtimali</p>
                <p class="text-xl font-bold {{ $saleProbClass }}">
                    @if($saleProb !== null) %{{ $saleProb }} @else - @endif
                </p>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-lg p-3 border border-gray-200 dark:border-dark-700/50">
                <p class="text-[10px] text-gray-500 dark:text-dark-400 uppercase tracking-wide mb-1">Tahmini Süre</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $val['estimated_sale_time']['estimated_days'] ?? '-' }} <span class="text-xs font-normal text-gray-500">gün</span>
                </p>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-lg p-3 border border-gray-200 dark:border-dark-700/50">
                <p class="text-[10px] text-gray-500 dark:text-dark-400 uppercase tracking-wide mb-1">Bölge Trendi</p>
                <p class="text-xl font-bold {{ $trendClass }} flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trendIcon }}"/></svg>
                    @if($trendPct !== null) %{{ abs($trendPct) }} @else - @endif
                </p>
            </div>
            <div class="bg-white dark:bg-dark-900 rounded-lg p-3 border border-gray-200 dark:border-dark-700/50">
                <p class="text-[10px] text-gray-500 dark:text-dark-400 uppercase tracking-wide mb-1">m² Birim Fiyat</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $fmt($val['price_per_sqm'] ?? null) }} <span class="text-xs font-normal text-gray-500">{{ $currency }}</span>
                </p>
            </div>
        </div>

        @if(!empty($aiInsights['summary']))
            <div class="bg-purple-500/10 border border-purple-500/30 rounded-xl p-4 mb-5">
                <h3 class="text-sm font-semibold text-purple-400 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Yorumu
                </h3>
                <p class="text-sm text-gray-700 dark:text-dark-200 leading-relaxed">{{ $aiInsights['summary'] }}</p>
            </div>
        @endif

        @if(!empty($aiInsights['strengths']) || !empty($aiInsights['weaknesses']) || !empty($aiInsights['recommendations']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                @if(!empty($aiInsights['strengths']))
                    <div>
                        <h3 class="text-sm font-semibold text-green-400 mb-2">✓ Güçlü Yönler</h3>
                        <ul class="space-y-1 text-sm text-gray-600 dark:text-dark-300">
                            @foreach($aiInsights['strengths'] as $s)
                                <li class="flex items-start gap-1.5"><span class="text-green-500 mt-1 text-xs">•</span><span>{{ $s }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(!empty($aiInsights['weaknesses']))
                    <div>
                        <h3 class="text-sm font-semibold text-red-400 mb-2">⚠ Zayıf Yönler</h3>
                        <ul class="space-y-1 text-sm text-gray-600 dark:text-dark-300">
                            @foreach($aiInsights['weaknesses'] as $w)
                                <li class="flex items-start gap-1.5"><span class="text-red-500 mt-1 text-xs">•</span><span>{{ $w }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(!empty($aiInsights['recommendations']))
                    <div>
                        <h3 class="text-sm font-semibold text-amber-400 mb-2">💡 Öneriler</h3>
                        <ul class="space-y-1 text-sm text-gray-600 dark:text-dark-300">
                            @foreach($aiInsights['recommendations'] as $r)
                                <li class="flex items-start gap-1.5"><span class="text-amber-500 mt-1 text-xs">•</span><span>{{ $r }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if(!empty($recs))
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Fiyatlama Senaryoları</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach(['quick_sale' => ['Hızlı Satış', 'text-sky-400'], 'optimal' => ['Optimal', 'text-emerald-400'], 'premium' => ['Premium', 'text-purple-400']] as $key => [$label, $colorClass])
                        @if(!empty($recs[$key]))
                            <div class="bg-white dark:bg-dark-900 rounded-xl p-4 border border-gray-200 dark:border-dark-700/50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium {{ $colorClass }}">{{ $label }}</span>
                                    @if(!empty($recs[$key]['estimated_days']))
                                        <span class="text-[10px] text-gray-500 dark:text-dark-400">~{{ $recs[$key]['estimated_days'] }} gün</span>
                                    @endif
                                </div>
                                <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                                    {{ $fmt($recs[$key]['price'] ?? null) }} <span class="text-xs font-normal text-gray-500">{{ $currency }}</span>
                                </p>
                                @if(!empty($recs[$key]['description']))
                                    <p class="text-xs text-gray-500 dark:text-dark-400">{{ $recs[$key]['description'] }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(!empty($recs['price_reduction_suggestion']))
                    <div class="mt-3 p-3 bg-red-500/10 border border-red-500/30 rounded-xl">
                        <p class="text-sm text-red-400 font-medium">⚠ Fiyat İndirimi Önerisi</p>
                        <p class="text-xs text-gray-700 dark:text-dark-200 mt-1">{{ $recs['price_reduction_suggestion']['reason'] ?? '' }} — Önerilen yeni fiyat: <strong>{{ $fmt($recs['price_reduction_suggestion']['suggested_price']) }} {{ $currency }}</strong></p>
                    </div>
                @endif
            </div>
        @endif

        @if(!empty($val['generated_at']))
            <p class="text-[10px] text-gray-400 dark:text-dark-500 mt-4 text-right">
                Oluşturma: {{ \Carbon\Carbon::parse($val['generated_at'])->diffForHumans() }}
            </p>
        @endif
    @endif
</div>

<script>
function generateValuation(listingId, btn) {
    const label = btn.querySelector('[data-valuation-label]');
    const originalLabel = label ? label.textContent : btn.textContent;
    const setLabel = (txt) => { if (label) label.textContent = txt; else btn.textContent = txt; };

    btn.disabled = true;
    setLabel('Hesaplanıyor...');

    fetch(`/admin/ai/valuation/${listingId}/generate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json().then(data => ({ok: r.ok, data})))
    .then(({ok, data}) => {
        if (!ok) {
            alert(data.message || 'Değerleme yapılamadı.');
            btn.disabled = false;
            setLabel(originalLabel);
            return;
        }
        setLabel('Tamam, sayfa yenileniyor...');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((e) => {
        alert('Bir hata oluştu: ' + e.message);
        btn.disabled = false;
        setLabel(originalLabel);
    });
}

function generateDescription(listingId, btn) {
    const label = btn.querySelector('[data-description-label]');
    const originalLabel = label ? label.textContent : btn.textContent;
    const setLabel = (txt) => { if (label) label.textContent = txt; else btn.textContent = txt; };

    btn.disabled = true;
    setLabel('Açıklama üretiliyor...');

    fetch(`/admin/ai/content/description/${listingId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ style: 'professional', languages: ['tr'], async: true }),
    })
    .then(r => r.json().then(data => ({ok: r.ok, data})))
    .then(({ok, data}) => {
        if (!ok) {
            alert(data.message || 'Açıklama üretilemedi.');
            btn.disabled = false;
            setLabel(originalLabel);
            return;
        }
        setLabel('Kuyruğa eklendi · 5sn sonra yenilenecek');
        setTimeout(() => window.location.reload(), 5000);
    })
    .catch((e) => {
        alert('Bir hata oluştu: ' + e.message);
        btn.disabled = false;
        setLabel(originalLabel);
    });
}
</script>
