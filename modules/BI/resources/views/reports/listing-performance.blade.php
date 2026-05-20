@extends('layouts.admin')

@section('title', 'İlan Performansı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İlan Performansı</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">En çok görüntülenen 20 ilan</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors">← Raporlar</a>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800/50 text-gray-500 dark:text-dark-400 uppercase text-xs">
                    <tr>
                        <th class="text-left p-3">İlan</th>
                        <th class="text-left p-3">Konum</th>
                        <th class="text-right p-3">Fiyat</th>
                        <th class="text-right p-3">Görüntülenme</th>
                        <th class="text-right p-3">Talep</th>
                        <th class="text-right p-3">Gösterim</th>
                        <th class="text-right p-3">Kalite</th>
                        <th class="text-right p-3">Gün</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($listings as $l)
                        <tr class="border-t border-gray-100 dark:border-dark-800 hover:bg-gray-50 dark:hover:bg-dark-800/30">
                            <td class="p-3">
                                <div class="text-white font-medium truncate max-w-xs">{{ $l['title'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-dark-400">{{ $l['reference_no'] }}</div>
                            </td>
                            <td class="p-3 text-gray-300">{{ $l['location'] }}</td>
                            <td class="p-3 text-right text-white font-semibold">{{ $l['price'] }}</td>
                            <td class="p-3 text-right text-sky-400">{{ number_format($l['views']) }}</td>
                            <td class="p-3 text-right text-gray-300">{{ $l['inquiries'] }}</td>
                            <td class="p-3 text-right text-gray-300">{{ $l['showings'] }}</td>
                            <td class="p-3 text-right">
                                @php
                                    $q = (int) ($l['quality_score'] ?? 0);
                                    $cls = $q >= 70 ? 'text-emerald-400' : ($q >= 40 ? 'text-amber-400' : 'text-rose-400');
                                @endphp
                                <span class="{{ $cls }} font-medium">{{ $q }}</span>
                            </td>
                            <td class="p-3 text-right text-gray-500 dark:text-dark-400">{{ $l['days_on_market'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-8 text-center text-gray-500 dark:text-dark-400">Veri yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
