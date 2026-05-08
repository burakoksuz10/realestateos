@extends('layouts.admin')
@section('title', 'Reklam Yönetimi')
@section('content')
<div class="space-y-6" x-data="reklamMerkezi()" x-init="init()">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Müşteri Getirme Merkezi</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Meta reklamlarını randevu, mesaj ve müşteri maliyeti üzerinden yönetin.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button @click="syncMeta()" :disabled="syncing"
                class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center gap-2 text-sm disabled:opacity-50">
                <svg x-show="!syncing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <svg x-show="syncing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Meta Sync
            </button>
            <button @click="showCreateModal = true"
                class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Kampanya Kur
            </button>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 border-l-4 border-l-fuchsia-500 rounded-2xl p-5">
            <svg class="w-5 h-5 text-fuchsia-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-xl font-bold text-white">{{ number_format($totals['harcama'], 0, ',', '.') }} ₺</p>
            <p class="text-xs text-dark-500 mt-0.5">30 gün harcama</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 border-l-4 border-l-emerald-500 rounded-2xl p-5">
            <svg class="w-5 h-5 text-emerald-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <p class="text-xl font-bold text-white">{{ number_format($totals['result_count']) }}</p>
            <p class="text-xs text-dark-500 mt-0.5">Toplam sonuç</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 border-l-4 border-l-blue-500 rounded-2xl p-5">
            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            <p class="text-xl font-bold text-white">{{ number_format($totals['cost_per_result'], 0, ',', '.') }} ₺</p>
            <p class="text-xs text-dark-500 mt-0.5">Sonuç maliyeti</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 border-l-4 border-l-cyan-500 rounded-2xl p-5">
            <svg class="w-5 h-5 text-cyan-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p class="text-xl font-bold text-white">{{ number_format($totals['erisme']) }}</p>
            <p class="text-xs text-dark-500 mt-0.5">Erişim</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 border-l-4 border-l-amber-500 rounded-2xl p-5">
            <svg class="w-5 h-5 text-amber-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
            <p class="text-xl font-bold text-white">{{ $totals['avg_health'] }}/100</p>
            <p class="text-xs text-dark-500 mt-0.5">Reklam sağlık skoru</p>
        </div>
    </div>

    <!-- Chart + Best Campaign -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <!-- Chart -->
        <div class="xl:col-span-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-white">Performans Akışı</h2>
                <p class="text-sm text-gray-500 dark:text-dark-400">Harcama ve sonuçlar (son 30 gün)</p>
            </div>
            @if($dailyMetrics->count() > 0)
            <div class="h-48 flex items-end gap-1" id="chart-container">
                @php
                    $maxHarcama = $dailyMetrics->max('harcama') ?: 1;
                @endphp
                @foreach($dailyMetrics->take(30) as $metric)
                @php
                    $height = round(($metric->harcama / $maxHarcama) * 100);
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-200 dark:bg-dark-700 text-white text-xs px-2 py-1 rounded whitespace-nowrap hidden group-hover:block z-10">
                        {{ number_format($metric->harcama, 0, ',', '.') }} ₺
                    </div>
                    <div class="w-full rounded-t-sm bg-violet-500/60 hover:bg-violet-500 transition-colors" style="height: {{ $height }}%"></div>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between mt-3 text-xs text-dark-500">
                <span>{{ $dailyMetrics->first()?->tarih?->format('d.m') }}</span>
                <span>{{ $dailyMetrics->last()?->tarih?->format('d.m') }}</span>
            </div>
            @else
            <div class="h-48 flex items-center justify-center text-center">
                <div>
                    <svg class="w-10 h-10 text-dark-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <p class="text-gray-500 dark:text-dark-400 font-medium text-sm">Henüz metrik yok</p>
                    <p class="text-dark-500 text-xs">Kampanya yayınlandıktan sonra veriler burada görünür.</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right column -->
        <div class="space-y-4">
            <!-- Automation principle -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 dark:from-gray-800 dark:to-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-white/10">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white text-sm">Otomasyon Kurgusu</h2>
                        <p class="text-xs text-gray-500 dark:text-dark-400 mt-1.5 leading-relaxed">
                            Yeni kampanyalar geniş hedefleme, otomatik yerleşim ve düşük maliyet odaklı teklif stratejisiyle hazırlanır.
                        </p>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Advantage+ audience mantığı
                    </div>
                    <div class="flex items-center gap-2 text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Facebook, Instagram, Messenger
                    </div>
                    <div class="flex items-center gap-2 text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Kreatif geliştirme opt-in
                    </div>
                </div>
            </div>

            <!-- Best campaign -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <h2 class="font-semibold text-white text-sm mb-3">En İyi Fırsat</h2>
                @php
                    $bestCampaign = $campaigns
                        ->filter(fn($c) => ($c->metrics->sum('lead') + $c->metrics->sum('mesaj') + $c->metrics->sum('donusum')) > 0)
                        ->sortBy(fn($c) => $c->totals['cost_per_result'])
                        ->first();
                @endphp
                @if($bestCampaign)
                <p class="text-xs text-dark-500">En düşük sonuç maliyeti</p>
                <p class="font-semibold text-white mt-1">{{ $bestCampaign->name }}</p>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-500 dark:text-dark-400">maliyet/sonuç</span>
                    <span class="font-bold text-emerald-400">{{ number_format($bestCampaign->totals['cost_per_result'], 0, ',', '.') }} ₺</span>
                </div>
                @else
                <p class="text-xs text-dark-500">Sonuç oluştukça öneri burada belirecek.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Campaigns -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
            <div>
                <h2 class="font-semibold text-white">Kampanyalar</h2>
                <p class="text-sm text-gray-500 dark:text-dark-400">Her kampanyayı sonuç maliyeti ve sağlık skoruyla takip edin.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['tumu' => 'Tümü', 'randevu' => 'Randevu', 'mesaj' => 'Mesaj', 'tanitim' => 'Tanıtım', 'etkilesim' => 'Etkileşim', 'trafik' => 'Web Ziyareti'] as $fkey => $flabel)
                <button
                    @click="filterHedef = '{{ $fkey }}'"
                    :class="filterHedef === '{{ $fkey }}' ? 'bg-dark-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-500 dark:text-dark-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-full text-xs transition-colors">
                    {{ $flabel }}
                </button>
                @endforeach
            </div>
        </div>

        @if($campaigns->count() === 0)
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <p class="text-gray-500 dark:text-dark-400 font-medium">Henüz kampanya kurulmamış</p>
            <p class="text-dark-500 text-sm mt-1">İlk kampanyayı 3 dakikada hazırlayabilirsiniz.</p>
            <button @click="showCreateModal = true" class="mt-4 px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl text-sm transition-colors">Kampanya Kur</button>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($campaigns as $campaign)
            @php
                $campaignMetricsTotal = [
                    'harcama' => $campaign->metrics->sum('harcama'),
                    'result_count' => $campaign->metrics->sum('lead') + $campaign->metrics->sum('mesaj') + $campaign->metrics->sum('donusum'),
                    'erisme' => $campaign->metrics->sum('erisme'),
                ];
                $campaignMetricsTotal['cost_per_result'] = $campaignMetricsTotal['result_count'] > 0
                    ? $campaignMetricsTotal['harcama'] / $campaignMetricsTotal['result_count'] : 0;

                $hedefColors = [
                    'randevu' => ['border' => 'border-emerald-200/20', 'bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400'],
                    'mesaj' => ['border' => 'border-green-200/20', 'bg' => 'bg-green-500/20', 'text' => 'text-green-400'],
                    'tanitim' => ['border' => 'border-blue-200/20', 'bg' => 'bg-primary-100 dark:bg-primary-500/20', 'text' => 'text-primary-600 dark:text-primary-400'],
                    'etkilesim' => ['border' => 'border-rose-200/20', 'bg' => 'bg-rose-500/20', 'text' => 'text-rose-400'],
                    'trafik' => ['border' => 'border-cyan-200/20', 'bg' => 'bg-cyan-500/20', 'text' => 'text-cyan-400'],
                ];
                $hc = $hedefColors[$campaign->hedef] ?? $hedefColors['randevu'];
                $healthLabel = $campaign->health_score >= 85 ? ['Çok iyi', 'bg-emerald-500/20 text-emerald-400']
                    : ($campaign->health_score >= 70 ? ['Sağlıklı', 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400']
                    : ($campaign->health_score >= 55 ? ['İzlenmeli', 'bg-amber-500/20 text-amber-400']
                    : ['Eksik kurgu', 'bg-red-500/20 text-red-400']));
            @endphp
            <div class="rounded-xl border {{ $hc['border'] }} border-gray-200 dark:border-dark-700/50 p-4 hover:border-dark-600 transition-colors"
                x-show="filterHedef === 'tumu' || filterHedef === '{{ $campaign->hedef }}'">
                <!-- Campaign header -->
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg {{ $hc['bg'] }} {{ $hc['border'] }} border">
                            <svg class="w-5 h-5 {{ $hc['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white text-sm">{{ $campaign->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-dark-400 mt-0.5">
                                {{ ['randevu' => 'Randevu Talebi', 'mesaj' => 'Mesaj', 'tanitim' => 'Tanıtım', 'etkilesim' => 'Etkileşim', 'trafik' => 'Web Ziyareti'][$campaign->hedef] ?? $campaign->hedef }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $campaign->durum === 'ACTIVE' ? 'bg-green-500/20 text-green-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}">
                        {{ $campaign->durum === 'ACTIVE' ? 'Yayında' : 'Taslak' }}
                    </span>
                </div>

                <!-- Metrics -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <div class="bg-gray-100 dark:bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-dark-400">Harcama</p>
                        <p class="font-semibold text-white text-sm">{{ number_format($campaignMetricsTotal['harcama'], 0, ',', '.') }} ₺</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-dark-400">Sonuç</p>
                        <p class="font-semibold text-white text-sm">{{ number_format($campaignMetricsTotal['result_count']) }}</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-dark-800 rounded-lg p-3">
                        <p class="text-xs text-gray-500 dark:text-dark-400">Maliyet</p>
                        <p class="font-semibold text-white text-sm">{{ $campaignMetricsTotal['result_count'] > 0 ? number_format($campaignMetricsTotal['cost_per_result'], 0, ',', '.') . ' ₺' : '-' }}</p>
                    </div>
                </div>

                <!-- Health + Actions -->
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $healthLabel[1] }}">
                        Sağlık: {{ $campaign->health_score }}/100 · {{ $healthLabel[0] }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button
                            @click="toggleCampaign({{ $campaign->id }}, '{{ $campaign->durum }}')"
                            class="p-2 rounded-lg bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-600 dark:text-dark-300 transition-colors"
                            title="{{ $campaign->durum === 'ACTIVE' ? 'Duraklat' : 'Başlat' }}">
                            @if($campaign->durum === 'ACTIVE')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @endif
                        </button>
                        <button
                            @click="confirmDelete = {{ $campaign->id }}"
                            class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- AI Analysis -->
                <div class="rounded-xl bg-violet-500/10 border border-violet-500/20 p-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <button
                            @click="analyzeAI({{ $campaign->id }})"
                            :disabled="aiLoading === {{ $campaign->id }}"
                            class="flex items-center justify-center gap-2 px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg text-sm font-semibold transition-colors disabled:opacity-60">
                            <svg x-show="aiLoading !== {{ $campaign->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            <svg x-show="aiLoading === {{ $campaign->id }}" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="aiLoading === {{ $campaign->id }} ? 'İnceliyor...' : 'Yapay Zeka Analizi'"></span>
                        </button>
                        <p class="text-xs text-violet-300 sm:text-right leading-relaxed">
                            @if($campaign->latest_ai_analysis)
                            Son analiz: {{ \Carbon\Carbon::parse($campaign->latest_ai_analysis['created_at'] ?? now())->diffForHumans() }}
                            @else
                            Analiz alınınca burada görünecek.
                            @endif
                        </p>
                    </div>
                    <!-- AI Result -->
                    <div x-show="aiResults['{{ $campaign->id }}']" class="mt-3 text-xs text-violet-200 leading-relaxed whitespace-pre-line" x-text="aiResults['{{ $campaign->id }}']"></div>
                    @if($campaign->latest_ai_analysis && isset($campaign->latest_ai_analysis['analysis']))
                    <div class="mt-3 text-xs text-violet-200 leading-relaxed whitespace-pre-line" x-show="!aiResults['{{ $campaign->id }}']">
                        {{ Str::limit($campaign->latest_ai_analysis['analysis'], 200) }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Create Campaign Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" @click="showCreateModal = false"></div>
        <div class="relative bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl w-full max-w-lg">
            <div class="p-6 border-b border-gray-200 dark:border-dark-700/50 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kampanya Kur</h2>
                <button @click="showCreateModal = false" class="text-gray-500 dark:text-dark-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kampanya Adı *</label>
                    <input type="text" x-model="createForm.name" placeholder="ör. Bahar Dönemi İlan Kampanyası"
                        class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kampanya Hedefi</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['randevu' => 'Randevu Talebi', 'mesaj' => 'Mesaj', 'tanitim' => 'Tanıtım', 'etkilesim' => 'Etkileşim', 'trafik' => 'Web Ziyareti'] as $hkey => $hlabel)
                        <button
                            @click="createForm.hedef = '{{ $hkey }}'"
                            :class="createForm.hedef === '{{ $hkey }}' ? 'border-primary-500 bg-primary-600/20 text-primary-400' : 'border-gray-200 dark:border-dark-700 text-gray-500 dark:text-dark-400 hover:border-dark-600'"
                            class="px-3 py-2 border rounded-xl text-xs transition-colors text-left">
                            {{ $hlabel }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Günlük Bütçe (₺)</label>
                        <input type="number" x-model="createForm.budget" min="0" step="10"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Şehir</label>
                        <input type="text" x-model="createForm.city" placeholder="ör. Samsun"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                    </div>
                </div>
                <div x-show="createError" class="p-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm" x-text="createError"></div>
            </div>
            <div class="p-6 border-t border-gray-200 dark:border-dark-700/50 flex items-center justify-end gap-3">
                <button @click="showCreateModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors text-sm">İptal</button>
                <button @click="createCampaign()" :disabled="creating"
                    class="px-5 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors text-sm flex items-center gap-2 disabled:opacity-50">
                    <svg x-show="creating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Kampanya Oluştur
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" @click="confirmDelete = null"></div>
        <div class="relative bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 w-full max-w-sm text-center">
            <svg class="w-10 h-10 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            <h3 class="text-white font-semibold">Kampanyayı Sil</h3>
            <p class="text-gray-500 dark:text-dark-400 text-sm mt-2">Bu kampanyayı silmek istediğinizden emin misiniz?</p>
            <div class="flex items-center justify-center gap-3 mt-5">
                <button @click="confirmDelete = null" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl text-sm transition-colors">Vazgeç</button>
                <button @click="deleteCampaign(confirmDelete)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm transition-colors">Sil</button>
            </div>
        </div>
    </div>
</div>

<script>
function reklamMerkezi() {
    return {
        filterHedef: 'tumu',
        syncing: false,
        showCreateModal: false,
        creating: false,
        createError: null,
        confirmDelete: null,
        aiLoading: null,
        aiResults: {},
        createForm: { name: '', hedef: 'randevu', budget: 100, city: '' },

        init() {},

        async syncMeta() {
            this.syncing = true;
            try {
                const res = await fetch('/admin/advertising/sync', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await res.json();
                alert(data.message || 'Senkronizasyon tamamlandı.');
            } finally {
                this.syncing = false;
            }
        },

        async createCampaign() {
            this.creating = true;
            this.createError = null;
            try {
                const res = await fetch('/admin/advertising/campaigns', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.createForm),
                });
                const data = await res.json();
                if (!res.ok) { this.createError = data.message || 'Bir hata oluştu.'; return; }
                this.showCreateModal = false;
                window.location.reload();
            } catch (e) {
                this.createError = 'Bağlantı hatası.';
            } finally {
                this.creating = false;
            }
        },

        async toggleCampaign(id, currentStatus) {
            const res = await fetch(`/admin/advertising/campaigns/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            });
            if (res.ok) window.location.reload();
        },

        async deleteCampaign(id) {
            const res = await fetch(`/admin/advertising/campaigns/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (res.ok) window.location.reload();
            this.confirmDelete = null;
        },

        async analyzeAI(id) {
            this.aiLoading = id;
            try {
                const res = await fetch(`/admin/advertising/campaigns/${id}/analyze`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) {
                    this.aiResults[id] = data.analysis;
                } else {
                    this.aiResults[id] = 'Analiz alınamadı: ' + (data.error || 'Bilinmeyen hata');
                }
            } catch (e) {
                this.aiResults[id] = 'Bağlantı hatası.';
            } finally {
                this.aiLoading = null;
            }
        },
    };
}
</script>
@endsection
