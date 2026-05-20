@extends('layouts.admin')

@section('title', 'İş Zekası Paneli')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İş Zekası Paneli</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Performans metrikleri ve raporlar</p>
        </div>
    </div>

    {{-- Top KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Bu Ay Lead</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['leads']['this_month']) }}</p>
            @if ($summary['leads']['change'] != 0)
                <p class="text-xs mt-1 {{ $summary['leads']['change'] > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $summary['leads']['change'] > 0 ? '▲' : '▼' }} {{ abs($summary['leads']['change']) }}% (geçen aya göre)
                </p>
            @endif
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Bu Ay Kazanılan</p>
            <p class="text-2xl font-bold text-emerald-500 mt-1">{{ number_format($summary['deals']['this_month']) }}</p>
            @if ($summary['deals']['change'] != 0)
                <p class="text-xs mt-1 {{ $summary['deals']['change'] > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $summary['deals']['change'] > 0 ? '▲' : '▼' }} {{ abs($summary['deals']['change']) }}%
                </p>
            @endif
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Bu Ay Gelir</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">₺{{ number_format($summary['revenue']['this_month'] / 1000, 0) }}K</p>
            @if ($summary['revenue']['change'] != 0)
                <p class="text-xs mt-1 {{ $summary['revenue']['change'] > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $summary['revenue']['change'] > 0 ? '▲' : '▼' }} {{ abs($summary['revenue']['change']) }}%
                </p>
            @endif
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Pipeline Değeri</p>
            <p class="text-2xl font-bold text-sky-500 mt-1">₺{{ number_format($summary['deals']['pipeline_value'] / 1000, 0) }}K</p>
            <p class="text-xs text-gray-500 dark:text-dark-400 mt-1">Açık deal'ların toplamı</p>
        </div>
    </div>

    {{-- Revenue Trend + Funnel side-by-side --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Gelir Trendi (Son 6 Ay)</h2>
                <a href="{{ route('admin.reports.revenue') }}" class="text-sm text-primary-500 hover:text-primary-400">Detay →</a>
            </div>
            <canvas id="revenueTrend" height="100"></canvas>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dönüşüm Hunisi</h2>
                <a href="{{ route('admin.reports.conversion-funnel') }}" class="text-sm text-primary-500 hover:text-primary-400">Detay →</a>
            </div>
            <div class="space-y-2">
                @foreach ($funnel['stages'] as $stage)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700 dark:text-dark-200">{{ $stage['name'] }}</span>
                            <span class="text-gray-500 dark:text-dark-400"><b class="text-white">{{ $stage['count'] }}</b> · {{ $stage['percentage'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 dark:bg-dark-800 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-sky-400 to-blue-600" style="width: {{ max(2, $stage['percentage']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 dark:text-dark-400 mt-4">Lead → satış: <b class="text-emerald-400">%{{ $funnel['conversion_rate'] }}</b></p>
        </div>
    </div>

    {{-- Top Agents + Lead Sources side-by-side --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🏆 En İyi Danışmanlar</h2>
                <a href="{{ route('admin.reports.agent-performance') }}" class="text-sm text-primary-500 hover:text-primary-400">Tümü →</a>
            </div>
            <div class="space-y-3">
                @forelse (array_slice($agents, 0, 5) as $i => $agent)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-blue-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ $i + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium truncate">{{ $agent['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-dark-400">{{ $agent['metrics']['deals_won'] }} kazanılan · {{ $agent['metrics']['leads'] }} lead</p>
                        </div>
                        <div class="text-right">
                            <p class="text-emerald-400 font-semibold text-sm">₺{{ number_format($agent['metrics']['revenue'] / 1000, 0) }}K</p>
                            <p class="text-xs text-gray-500 dark:text-dark-400">%{{ $agent['conversion_rate'] }} dönüşüm</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Bu ay henüz danışman aktivitesi yok.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📊 Lead Kaynakları</h2>
                <a href="{{ route('admin.reports.lead-sources') }}" class="text-sm text-primary-500 hover:text-primary-400">Tümü →</a>
            </div>
            <div class="space-y-3">
                @forelse (array_slice($sources, 0, 6) as $source)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-medium truncate">{{ ucfirst($source['source']) }}</p>
                            <p class="text-xs text-gray-500 dark:text-dark-400">{{ $source['total'] }} lead · skor ort. {{ $source['avg_score'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold text-sm">%{{ $source['conversion_rate'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-dark-400">{{ $source['converted'] }} dönüşüm</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Veri yok.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Report tiles --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detaylı Raporlar</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
            $reports = [
                ['title' => 'Dönüşüm Hunisi', 'route' => 'admin.reports.conversion-funnel', 'icon' => 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12'],
                ['title' => 'Danışman Performansı', 'route' => 'admin.reports.agent-performance', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['title' => 'Lead Kaynakları', 'route' => 'admin.reports.lead-sources', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['title' => 'Portal Performansı', 'route' => 'admin.reports.portal-performance', 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                ['title' => 'İlan Performansı', 'route' => 'admin.reports.listing-performance', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['title' => 'Gelir Raporu', 'route' => 'admin.reports.revenue', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            @endphp
            @foreach ($reports as $report)
                <a href="{{ route($report['route']) }}" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 hover:border-primary-500/50 hover:shadow-sm transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-600/20 rounded-xl flex items-center justify-center group-hover:bg-primary-200 dark:group-hover:bg-primary-600/30 transition-colors">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"></path></svg>
                        </div>
                        <span class="text-gray-900 dark:text-white font-medium">{{ $report['title'] }}</span>
                    </div>
                    <p class="text-gray-500 dark:text-dark-400 text-xs">Raporu görüntüle →</p>
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueTrend');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($revenue, 'label')),
            datasets: [{
                label: 'Gelir (₺)',
                data: @json(array_column($revenue, 'revenue')),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#0ea5e9',
            }, {
                label: 'Deal sayısı',
                data: @json(array_column($revenue, 'deals')),
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                yAxisID: 'y1',
                tension: 0.35,
                pointRadius: 3,
                borderDash: [4, 4],
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { color: '#9ca3af' } } },
            scales: {
                x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156,163,175,0.1)' } },
                y: { ticks: { color: '#9ca3af', callback: v => '₺' + (v/1000).toFixed(0) + 'K' }, grid: { color: 'rgba(156,163,175,0.1)' } },
                y1: { position: 'right', ticks: { color: '#10b981' }, grid: { drawOnChartArea: false } },
            },
        },
    });
});
</script>
@endpush
@endsection
