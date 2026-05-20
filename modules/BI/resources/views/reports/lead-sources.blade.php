@extends('layouts.admin')

@section('title', 'Lead Kaynakları')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Lead Kaynakları</h1>
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
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark-800/50 text-gray-500 dark:text-dark-400 uppercase text-xs">
                <tr>
                    <th class="text-left p-3">Kaynak</th>
                    <th class="text-right p-3">Lead</th>
                    <th class="text-right p-3">Dönüşüm</th>
                    <th class="text-right p-3">Dönüşüm Oranı</th>
                    <th class="text-right p-3">Ortalama Skor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sources as $s)
                    <tr class="border-t border-gray-100 dark:border-dark-800 hover:bg-gray-50 dark:hover:bg-dark-800/30">
                        <td class="p-3 text-white font-medium">{{ ucfirst($s['source']) }}</td>
                        <td class="p-3 text-right text-gray-300">{{ number_format($s['total']) }}</td>
                        <td class="p-3 text-right text-emerald-400">{{ number_format($s['converted']) }}</td>
                        <td class="p-3 text-right">
                            @php
                                $rate = $s['conversion_rate'];
                                $cls = $rate >= 30 ? 'text-emerald-400' : ($rate >= 10 ? 'text-amber-400' : 'text-rose-400');
                            @endphp
                            <span class="font-semibold {{ $cls }}">%{{ $rate }}</span>
                        </td>
                        <td class="p-3 text-right text-white">{{ $s['avg_score'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500 dark:text-dark-400">Bu tarih aralığında kaynak verisi yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
