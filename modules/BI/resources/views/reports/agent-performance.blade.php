@extends('layouts.admin')

@section('title', 'Danışman Performansı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Danışman Performansı</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">
                {{ $filters['date_from']->format('d.m.Y') }} — {{ $filters['date_to']->format('d.m.Y') }}
            </p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors">← Raporlar</a>
    </div>

    <form method="GET" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Başlangıç</label>
                <input type="date" name="date_from" value="{{ $filters['date_from']->toDateString() }}" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Bitiş</label>
                <input type="date" name="date_to" value="{{ $filters['date_to']->toDateString() }}" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white text-sm">
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl">Uygula</button>
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark-800/50 text-gray-500 dark:text-dark-400 uppercase text-xs">
                    <tr>
                        <th class="text-left p-3">#</th>
                        <th class="text-left p-3">Danışman</th>
                        <th class="text-left p-3">Ofis</th>
                        <th class="text-right p-3">Lead</th>
                        <th class="text-right p-3">Dönüşüm</th>
                        <th class="text-right p-3">Açık Deal</th>
                        <th class="text-right p-3">Kazanılan</th>
                        <th class="text-right p-3">Aktivite</th>
                        <th class="text-right p-3">Gösterim</th>
                        <th class="text-right p-3">Gelir</th>
                        <th class="text-right p-3">Komisyon</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agents as $i => $agent)
                        <tr class="border-t border-gray-100 dark:border-dark-800 hover:bg-gray-50 dark:hover:bg-dark-800/30">
                            <td class="p-3 text-gray-500 dark:text-dark-400 font-medium">{{ $i + 1 }}</td>
                            <td class="p-3">
                                <div class="text-white font-medium">{{ $agent['name'] }}</div>
                            </td>
                            <td class="p-3 text-gray-500 dark:text-dark-400">{{ $agent['office'] ?? '—' }}</td>
                            <td class="p-3 text-right text-white">{{ $agent['metrics']['leads'] }}</td>
                            <td class="p-3 text-right">
                                <span class="text-emerald-400 font-medium">%{{ $agent['conversion_rate'] }}</span>
                            </td>
                            <td class="p-3 text-right text-gray-300">{{ $agent['metrics']['deals'] }}</td>
                            <td class="p-3 text-right text-emerald-400 font-semibold">{{ $agent['metrics']['deals_won'] }}</td>
                            <td class="p-3 text-right text-gray-300">{{ $agent['metrics']['activities'] }}</td>
                            <td class="p-3 text-right text-gray-300">{{ $agent['metrics']['showings'] }}</td>
                            <td class="p-3 text-right text-white font-semibold">₺{{ number_format((float) $agent['metrics']['revenue'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-primary-400">₺{{ number_format((float) $agent['metrics']['commission'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="p-8 text-center text-gray-500 dark:text-dark-400">Bu tarih aralığında danışman verisi yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
