@extends('layouts.admin')
@section('title', 'Sosyal Medya Takvimi')
@section('content')
@php
    $current = \Carbon\Carbon::parse($month . '-01');
    $startOfMonth = $current->copy()->startOfMonth();
    $endOfMonth = $current->copy()->endOfMonth();
    // Pazartesi başlangıç
    $gridStart = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $gridEnd = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

    $postsByDate = $posts->groupBy(function ($p) {
        $when = $p->scheduled_at ?? $p->published_at ?? $p->created_at;
        return \Carbon\Carbon::parse($when)->format('Y-m-d');
    });

    $prev = $current->copy()->subMonth()->format('Y-m');
    $next = $current->copy()->addMonth()->format('Y-m');
    $monthLabel = $current->translatedFormat('F Y');
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sosyal Medya Takvimi</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Planlanmış ve yayınlanmış gönderiler aylık görünümde</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.social-media.calendar', ['month' => $prev]) }}"
                class="p-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="px-4 py-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-xl text-gray-900 dark:text-white font-medium min-w-[180px] text-center">
                {{ ucfirst($monthLabel) }}
            </div>
            <a href="{{ route('admin.social-media.calendar', ['month' => $next]) }}"
                class="p-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('admin.social-media.index') }}"
                class="ml-3 px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors text-sm">
                ← Gönderi Listesi
            </a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <!-- Days of week header -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-dark-700/50 bg-gray-50 dark:bg-dark-800/50">
            @foreach(['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $dayName)
                <div class="px-3 py-2 text-xs font-semibold text-gray-600 dark:text-dark-300 text-center">{{ $dayName }}</div>
            @endforeach
        </div>

        <!-- Date cells -->
        <div class="grid grid-cols-7">
            @php $cursor = $gridStart->copy(); @endphp
            @while($cursor->lte($gridEnd))
                @php
                    $dateKey = $cursor->format('Y-m-d');
                    $dayPosts = $postsByDate->get($dateKey, collect());
                    $isOtherMonth = $cursor->month !== $current->month;
                    $isToday = $cursor->isToday();
                @endphp
                <div class="min-h-[120px] border-b border-r border-gray-200 dark:border-dark-700/50 p-2 {{ $isOtherMonth ? 'bg-gray-50 dark:bg-dark-950/50' : '' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-medium {{ $isOtherMonth ? 'text-gray-400 dark:text-dark-600' : 'text-gray-700 dark:text-gray-200' }} {{ $isToday ? 'text-violet-600 dark:text-violet-400 font-bold' : '' }}">
                            {{ $cursor->day }}
                        </span>
                        @if($dayPosts->count())
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300">{{ $dayPosts->count() }}</span>
                        @endif
                    </div>
                    @foreach($dayPosts->take(3) as $post)
                        @php
                            $statusColor = match($post->status) {
                                'yayinlandi' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-green-300/50',
                                'planlandi' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-300/50',
                                'draft' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 border-yellow-300/50',
                                default => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-red-300/50',
                            };
                            $platformIcon = match($post->platform) {
                                'instagram' => 'IG',
                                'facebook' => 'FB',
                                'both' => 'IG/FB',
                                default => strtoupper(substr($post->platform, 0, 2)),
                            };
                            $time = optional($post->scheduled_at ?? $post->published_at)->format('H:i');
                        @endphp
                        <div class="text-[10px] mb-1 px-1.5 py-1 rounded border {{ $statusColor }} truncate" title="{{ $post->caption }}">
                            <span class="font-semibold">{{ $time }}</span>
                            <span class="opacity-70">·</span>
                            <span>{{ $platformIcon }}</span>
                            <span class="opacity-70">·</span>
                            <span>{{ \Illuminate\Support\Str::limit($post->caption ?: '(altyazı yok)', 24) }}</span>
                        </div>
                    @endforeach
                    @if($dayPosts->count() > 3)
                        <p class="text-[10px] text-gray-500 dark:text-dark-400 mt-0.5">+{{ $dayPosts->count() - 3 }} daha</p>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-dark-300">
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-yellow-400"></span> Taslak
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-blue-400"></span> Planlandı
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-green-400"></span> Yayınlandı
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-red-400"></span> Hata
        </span>
    </div>
</div>
@endsection
