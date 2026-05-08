@extends('layouts.admin')

@section('title', 'İş Zekası Paneli')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İş Zekası Paneli</h1>
        <p class="text-gray-500 dark:text-dark-400 mt-1">Performans metrikleri ve raporlar</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam İlan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_listings']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Aktif İlan</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($stats['active_listings']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Potansiyel Müşteri</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_leads']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Kazanılan Fırsat</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['won_deals']) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Gelir</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">₺{{ number_format($stats['total_revenue'] / 1000000, 1) }}M</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
        $reports = [
            ['title' => 'Dönüşüm Hunisi',     'route' => 'admin.bi.reports.conversion-funnel',    'icon' => 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12'],
            ['title' => 'Danışman Performansı','route' => 'admin.bi.reports.agent-performance',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['title' => 'Potansiyel Müşteri Kaynakları',     'route' => 'admin.bi.reports.lead-sources',         'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['title' => 'Portal Performansı',  'route' => 'admin.bi.reports.portal-performance',   'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
            ['title' => 'İlan Performansı',    'route' => 'admin.bi.reports.listing-performance',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['title' => 'Gelir Raporu',        'route' => 'admin.bi.reports.revenue',              'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ]
        @endphp
        @foreach($reports as $report)
        <a href="{{ route($report['route']) }}" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 hover:border-primary-300 dark:hover:border-primary-500/50 hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-600/20 rounded-xl flex items-center justify-center group-hover:bg-primary-200 dark:group-hover:bg-primary-600/30 transition-colors">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"></path>
                    </svg>
                </div>
                <span class="text-gray-900 dark:text-white font-medium">{{ $report['title'] }}</span>
            </div>
            <p class="text-gray-500 dark:text-dark-400 text-xs">Raporu görüntüle →</p>
        </a>
        @endforeach
    </div>
</div>
@endsection
