@extends('layouts.admin')

@section('title', 'Bugünkü AI Önerileri')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bugünkü AI Önerileri</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ now()->translatedFormat('d F Y, l') }}</p>
        </div>
        <form action="{{ route('admin.ai.copilot.daily-plan') }}" method="GET">
            <input type="hidden" name="force" value="1">
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Yeniden Üret
            </button>
        </form>
    </div>

    @if (!empty($plan['summary']))
        <div class="bg-gradient-to-r from-sky-500/10 to-purple-500/10 border border-sky-500/30 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold mb-1">Günün stratejisi</h3>
                    <p class="text-gray-300 text-sm">{{ $plan['summary'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (empty($plan['priorities']))
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="text-xl font-semibold text-white mb-2">Bugün aktif iş yok</h2>
            <p class="text-gray-500 dark:text-dark-400 text-sm">Yeni lead aramaya veya portfoyü gözden geçirmeye odaklan.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($plan['priorities'] as $i => $item)
                @php
                    $impact = $item['impact'] ?? 'orta';
                    $impactClass = match (mb_strtolower($impact)) {
                        'yüksek', 'high' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                        'orta', 'medium' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                        'düşük', 'low'   => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
                        default          => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                    };
                @endphp
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 hover:border-primary-500/50 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-700 flex items-center justify-center flex-shrink-0 text-white font-bold">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <h3 class="text-white font-semibold">{{ $item['action'] ?? 'Aksiyon' }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded-full border {{ $impactClass }}">{{ $impact }} etki</span>
                                @if (!empty($item['effort_minutes']))
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-dark-700 text-gray-500 dark:text-dark-300">~{{ $item['effort_minutes'] }} dk</span>
                                @endif
                            </div>
                            @if (!empty($item['reason']))
                                <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">{{ $item['reason'] }}</p>
                            @endif
                            @if (!empty($item['target']))
                                <p class="text-sky-400 text-xs mt-2 font-medium">📎 {{ $item['target'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-right text-xs text-gray-500 dark:text-dark-400">
        Kaynak: <b class="text-gray-700 dark:text-gray-300">{{ $plan['source'] ?? 'ai' }}</b> ·
        Üretildi: {{ \Carbon\Carbon::parse($plan['generated_at'] ?? now())->diffForHumans() }}
    </div>
</div>
@endsection
