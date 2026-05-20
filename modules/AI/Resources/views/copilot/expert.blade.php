@extends('layouts.admin')

@section('title', 'AI Emlak Uzmanı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Emlak Uzmanı</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Prensip tabanlı operasyonel sorun tespiti</p>
        </div>
        <form action="{{ route('admin.ai.copilot.expert') }}" method="GET">
            <input type="hidden" name="force" value="1">
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Yeniden Analiz Et
            </button>
        </form>
    </div>

    @if (!empty($analysis['summary']))
        <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/30 rounded-2xl p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold mb-1">Genel Durum</h3>
                    <p class="text-gray-300 text-sm">{{ $analysis['summary'] }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (empty($analysis['insights']))
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-emerald-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="text-xl font-semibold text-white mb-2">Tespit edilen operasyonel sorun yok</h2>
            <p class="text-gray-500 dark:text-dark-400 text-sm">Tüm prensipler için ofis temiz görünüyor. İyi gidiyorsun.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($analysis['insights'] as $insight)
                @php
                    $sev = $insight['severity'] ?? 'info';
                    $sevClass = match ($sev) {
                        'critical' => 'border-rose-500/40 bg-rose-500/5',
                        'warning'  => 'border-amber-500/40 bg-amber-500/5',
                        default    => 'border-sky-500/40 bg-sky-500/5',
                    };
                    $sevBadge = match ($sev) {
                        'critical' => 'bg-rose-500/20 text-rose-300',
                        'warning'  => 'bg-amber-500/20 text-amber-300',
                        default    => 'bg-sky-500/20 text-sky-300',
                    };
                    $sevLabel = match ($sev) {
                        'critical' => 'Kritik',
                        'warning'  => 'Uyarı',
                        default    => 'Bilgi',
                    };
                    $catIcon = match ($insight['category'] ?? '') {
                        'listing' => '🏠',
                        'lead'    => '🎯',
                        'deal'    => '💼',
                        'agent'   => '👤',
                        'source'  => '📊',
                        default   => '⚡',
                    };
                @endphp
                <div class="bg-white dark:bg-dark-900 border-2 {{ $sevClass }} rounded-2xl p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">{{ $catIcon }}</span>
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $sevBadge }}">{{ $sevLabel }}</span>
                            @if (!empty($insight['affected_count']))
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-dark-700 text-gray-500 dark:text-dark-300">{{ $insight['affected_count'] }} adet</span>
                            @endif
                        </div>
                    </div>
                    <h3 class="text-white font-semibold mb-2">{{ $insight['title'] ?? 'Tespit' }}</h3>
                    <p class="text-gray-500 dark:text-dark-300 text-sm mb-3">{{ $insight['description'] ?? '' }}</p>
                    @if (!empty($insight['recommendation']))
                        <div class="bg-gray-50 dark:bg-dark-800/50 rounded-xl p-3 mb-3">
                            <p class="text-xs text-gray-500 dark:text-dark-400 font-medium mb-1">ÖNERİ</p>
                            <p class="text-emerald-300 text-sm">{{ $insight['recommendation'] }}</p>
                        </div>
                    @endif
                    @if (!empty($insight['examples']))
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500 dark:text-dark-400 font-medium">ÖRNEKLER</p>
                            @foreach ($insight['examples'] as $ex)
                                <p class="text-xs text-gray-400 dark:text-dark-300 truncate">• {{ is_array($ex) ? json_encode($ex, JSON_UNESCAPED_UNICODE) : $ex }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Principles --}}
    <details class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
        <summary class="cursor-pointer text-white font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Uzman Prensipleri ({{ count($analysis['principles'] ?? []) }})
        </summary>
        <ul class="space-y-2 mt-4 list-disc list-inside text-sm text-gray-500 dark:text-dark-300">
            @foreach ($analysis['principles'] ?? [] as $p)
                <li>{{ $p }}</li>
            @endforeach
        </ul>
    </details>

    <div class="text-right text-xs text-gray-500 dark:text-dark-400">
        Kaynak: <b class="text-gray-700 dark:text-gray-300">{{ $analysis['source'] ?? 'ai' }}</b> ·
        Üretildi: {{ \Carbon\Carbon::parse($analysis['generated_at'] ?? now())->diffForHumans() }}
    </div>
</div>
@endsection
