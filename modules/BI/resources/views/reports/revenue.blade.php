@extends('layouts.admin')

@section('title', 'Gelir Raporu')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gelir Raporu</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Son {{ $months }} ay</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors">← Raporlar</a>
    </div>

    <form method="GET" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-4 flex items-end gap-3">
        <div class="flex-1 max-w-xs">
            <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Ay sayısı</label>
            <select name="months" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white text-sm">
                @foreach ([3, 6, 12, 24] as $opt)
                    <option value="{{ $opt }}" {{ $months == $opt ? 'selected' : '' }}>{{ $opt }} ay</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl">Uygula</button>
    </form>

    @php
        $totalRevenue = array_sum(array_column($trend, 'revenue'));
        $totalDeals = array_sum(array_column($trend, 'deals'));
        $totalCommission = array_sum(array_column($trend, 'commission'));
        $avgDealValue = $totalDeals > 0 ? $totalRevenue / $totalDeals : 0;
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Gelir</p>
            <p class="text-2xl font-bold text-emerald-500 mt-1">₺{{ number_format($totalRevenue / 1000, 0) }}K</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Deal</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalDeals) }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Ortalama Deal Değeri</p>
            <p class="text-2xl font-bold text-sky-500 mt-1">₺{{ number_format($avgDealValue / 1000, 0) }}K</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Komisyon</p>
            <p class="text-2xl font-bold text-primary-400 mt-1">₺{{ number_format($totalCommission / 1000, 0) }}K</p>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aylık Trend</h2>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark-800/50 text-gray-500 dark:text-dark-400 uppercase text-xs">
                <tr>
                    <th class="text-left p-3">Ay</th>
                    <th class="text-right p-3">Gelir</th>
                    <th class="text-right p-3">Deal Sayısı</th>
                    <th class="text-right p-3">Ortalama Değer</th>
                    <th class="text-right p-3">Komisyon</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trend as $t)
                    <tr class="border-t border-gray-100 dark:border-dark-800">
                        <td class="p-3 text-white">{{ $t['label'] }}</td>
                        <td class="p-3 text-right text-emerald-400 font-medium">₺{{ number_format((float) $t['revenue'], 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-gray-300">{{ $t['deals'] }}</td>
                        <td class="p-3 text-right text-gray-300">
                            ₺{{ $t['deals'] > 0 ? number_format($t['revenue'] / $t['deals'], 0, ',', '.') : '0' }}
                        </td>
                        <td class="p-3 text-right text-primary-400">₺{{ number_format((float) $t['commission'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(array_column($trend, 'label')),
            datasets: [{
                label: 'Gelir (₺)',
                data: @json(array_column($trend, 'revenue')),
                backgroundColor: 'rgba(14, 165, 233, 0.7)',
                borderColor: '#0ea5e9',
                borderWidth: 1,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#9ca3af' } } },
            scales: {
                x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(156,163,175,0.1)' } },
                y: { ticks: { color: '#9ca3af', callback: v => '₺' + (v/1000).toFixed(0) + 'K' }, grid: { color: 'rgba(156,163,175,0.1)' } },
            },
        },
    });
});
</script>
@endpush
@endsection
