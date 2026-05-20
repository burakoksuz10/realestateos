@extends('layouts.admin')

@section('title', $campaign->name)

@php
    $stepTypeStyles = [
        'send_message' => ['label' => 'Mesaj Gönder', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'bg' => 'bg-sky-100 dark:bg-sky-900/30',     'text' => 'text-sky-700 dark:text-sky-300'],
        'wait'         => ['label' => 'Bekle',        'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                                'bg' => 'bg-gray-100 dark:bg-dark-700',      'text' => 'text-gray-700 dark:text-gray-300'],
        'create_task'  => ['label' => 'Görev Oluştur','icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-300'],
        'branch'       => ['label' => 'Koşul',        'icon' => 'M8 9l4-4 4 4m0 6l-4 4-4-4',                                                                  'bg' => 'bg-amber-100 dark:bg-amber-900/30',  'text' => 'text-amber-700 dark:text-amber-300'],
        'ai_action'    => ['label' => 'AI Aksiyonu',  'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'bg' => 'bg-pink-100 dark:bg-pink-900/30', 'text' => 'text-pink-700 dark:text-pink-300'],
    ];

    $channelLabels = [
        'email' => 'E-posta',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
    ];

    $statusStyles = [
        'active'    => ['label' => 'Aktif',     'bg' => 'bg-green-100 dark:bg-green-900/30',  'text' => 'text-green-700 dark:text-green-400'],
        'paused'    => ['label' => 'Durdurulmuş','bg' => 'bg-yellow-100 dark:bg-yellow-900/30','text' => 'text-yellow-700 dark:text-yellow-400'],
        'completed' => ['label' => 'Tamam',     'bg' => 'bg-gray-100 dark:bg-dark-700',       'text' => 'text-gray-700 dark:text-gray-300'],
        'cancelled' => ['label' => 'İptal',     'bg' => 'bg-gray-100 dark:bg-dark-700',       'text' => 'text-gray-500 dark:text-gray-500'],
        'failed'    => ['label' => 'Hata',      'bg' => 'bg-red-100 dark:bg-red-900/30',      'text' => 'text-red-700 dark:text-red-400'],
    ];
@endphp

@section('content')
<div class="space-y-6">

    <!-- Back link + Header -->
    <div>
        <a href="{{ route('admin.campaigns.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kampanyalar
        </a>
        <div class="mt-2 flex items-start justify-between">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign->name }}</h1>
                    @if($campaign->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400">
                            Duraklatıldı
                        </span>
                    @endif
                </div>
                @if($campaign->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $campaign->description }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.campaigns.toggle', $campaign) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-dark-600">
                    {{ $campaign->is_active ? 'Duraklat' : 'Aktifleştir' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm rounded-xl border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-4 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Aktif</p>
            <p class="mt-1 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-4 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tamam</p>
            <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-4 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Durdurulmuş</p>
            <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['paused'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-4 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hata</p>
            <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Steps timeline -->
        <div class="lg:col-span-1 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Akış Adımları</h3>
            <ol class="relative space-y-4 ml-4 border-l-2 border-gray-200 dark:border-dark-700">
                @forelse($campaign->steps as $step)
                    @php
                        $style = $stepTypeStyles[$step->type] ?? ['label' => $step->type, 'icon' => '', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        $cfg = $step->config ?? [];
                    @endphp
                    <li class="ml-4 -mt-1">
                        <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full {{ $style['bg'] }} ring-4 ring-white dark:ring-dark-800">
                            <svg class="w-3.5 h-3.5 {{ $style['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $style['icon'] }}"/>
                            </svg>
                        </span>
                        <div class="pl-2">
                            <p class="text-xs font-medium {{ $style['text'] }}">{{ $style['label'] }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $step->label ?: '#' . $step->order }}</p>

                            @if($step->type === 'send_message')
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Kanal: <span class="font-medium">{{ $channelLabels[$cfg['channel'] ?? ''] ?? ($cfg['channel'] ?? '-') }}</span>
                                </p>
                                @if(!empty($cfg['body']))
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 line-clamp-2 bg-gray-50 dark:bg-dark-700/50 rounded-lg p-2">
                                        {{ \Illuminate\Support\Str::limit($cfg['body'], 140) }}
                                    </p>
                                @endif
                            @elseif($step->type === 'wait')
                                @php
                                    $parts = [];
                                    if (!empty($cfg['days']))    $parts[] = $cfg['days'] . ' gün';
                                    if (!empty($cfg['hours']))   $parts[] = $cfg['hours'] . ' saat';
                                    if (!empty($cfg['minutes'])) $parts[] = $cfg['minutes'] . ' dk';
                                @endphp
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Süre: <span class="font-medium">{{ implode(', ', $parts) ?: '—' }}</span>
                                </p>
                            @elseif($step->type === 'create_task')
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $cfg['title'] ?? $cfg['subject'] ?? 'Görev' }}
                                </p>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-gray-500 dark:text-gray-400 pl-2">Henüz adım yok.</li>
                @endforelse
            </ol>
        </div>

        <!-- Enrollments -->
        <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-dark-700 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Son Enrollment'lar</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">son 50</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-dark-700/50">
                        <tr>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Lead</th>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Durum</th>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adım</th>
                            <th class="px-5 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sonraki</th>
                            <th class="px-5 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                        @forelse($enrollments as $enr)
                            @php $st = $statusStyles[$enr->status] ?? ['label' => $enr->status, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700']; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50">
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if($enr->lead)
                                        <a href="{{ route('admin.leads.show', $enr->lead) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                            {{ $enr->lead->contact?->full_name ?? 'Lead #' . $enr->lead->id }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">silinmiş</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $st['bg'] }} {{ $st['text'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                    @if($enr->last_error)
                                        <p class="text-[10px] text-red-500 mt-1 max-w-[280px] truncate" title="{{ $enr->last_error }}">{{ $enr->last_error }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                    {{ $enr->steps_completed }}/{{ $campaign->steps->count() }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    @if($enr->next_run_at)
                                        {{ $enr->next_run_at->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-right">
                                    @if($enr->status === 'active')
                                        <form method="POST" action="{{ route('admin.campaigns.enrollments.cancel', $enr) }}" class="inline" onsubmit="return confirm('Enrollment iptal edilsin mi?')">
                                            @csrf
                                            <button type="submit" class="text-xs px-2 py-1 text-red-600 dark:text-red-400 hover:underline">
                                                İptal
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Henüz enrollment yok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
