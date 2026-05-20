@extends('layouts.admin')

@section('title', 'Dönüşüm Hunisi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dönüşüm Hunisi</h1>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Aşamalar</h2>
            <div class="space-y-4">
                @php $maxCount = collect($funnel['stages'])->max('count') ?: 1; @endphp
                @foreach ($funnel['stages'] as $i => $stage)
                    @php $width = max(8, ($stage['count'] / $maxCount) * 100); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-white font-medium">{{ $i + 1 }}. {{ $stage['name'] }}</span>
                            <span class="text-gray-500 dark:text-dark-400"><b class="text-white">{{ number_format($stage['count']) }}</b> · {{ $stage['percentage'] }}%</span>
                        </div>
                        <div class="h-8 rounded-lg bg-gray-100 dark:bg-dark-800 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-sky-500 to-blue-600 flex items-center px-3 text-white text-sm font-medium" style="width: {{ $width }}%">
                                {{ number_format($stage['count']) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="space-y-4">
            <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 border border-emerald-500/30 rounded-2xl p-6">
                <p class="text-emerald-300 text-sm font-medium">Genel Dönüşüm</p>
                <p class="text-4xl font-bold text-emerald-400 mt-2">%{{ $funnel['conversion_rate'] }}</p>
                <p class="text-gray-500 dark:text-dark-400 text-xs mt-2">Lead → kazanılan satış</p>
            </div>
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h3 class="text-white font-semibold mb-3">Aşama Geçişleri</h3>
                <div class="space-y-2 text-sm">
                    @for ($i = 1; $i < count($funnel['stages']); $i++)
                        @php
                            $prev = $funnel['stages'][$i-1];
                            $curr = $funnel['stages'][$i];
                            $stepRate = $prev['count'] > 0 ? round(($curr['count'] / $prev['count']) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center justify-between text-gray-500 dark:text-dark-400">
                            <span class="truncate pr-2">{{ $prev['name'] }} → {{ $curr['name'] }}</span>
                            <span class="text-white font-medium">%{{ $stepRate }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
