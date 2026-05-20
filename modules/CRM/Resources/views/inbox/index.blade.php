@extends('layouts.admin')

@section('title', 'Gelen Kutusu')

@php
    use Illuminate\Support\Str;

    $channelStyles = [
        'telegram'           => ['label' => 'Telegram',  'bg' => 'bg-sky-100 dark:bg-sky-900/30',     'text' => 'text-sky-700 dark:text-sky-300'],
        'whatsapp'           => ['label' => 'WhatsApp',  'bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300'],
        'sms'                => ['label' => 'SMS',       'bg' => 'bg-purple-100 dark:bg-purple-900/30','text' => 'text-purple-700 dark:text-purple-300'],
        'email'              => ['label' => 'E-posta',   'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300'],
        'instagram_dm'       => ['label' => 'Instagram', 'bg' => 'bg-pink-100 dark:bg-pink-900/30',   'text' => 'text-pink-700 dark:text-pink-300'],
        'facebook_messenger' => ['label' => 'Messenger', 'bg' => 'bg-blue-100 dark:bg-blue-900/30',   'text' => 'text-blue-700 dark:text-blue-300'],
    ];

    $statusTabs = [
        'open'     => ['label' => 'Açık',        'count' => $counts['open']     ?? 0],
        'archived' => ['label' => 'Arşiv',       'count' => $counts['archived'] ?? 0],
        'closed'   => ['label' => 'Kapalı',      'count' => $counts['closed']   ?? 0],
    ];
@endphp

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gelen Kutusu</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Tüm kanallarınız tek yerde — Telegram, WhatsApp, SMS, E-posta
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300">
                <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                {{ $counts['unread'] ?? 0 }} okunmamış
            </span>
        </div>
    </div>

    <!-- Inbox Layout: 2-column grid -->
    <div class="grid grid-cols-12 gap-4 h-[calc(100vh-220px)] min-h-[500px]">

        <!-- LEFT: Conversation list -->
        <aside class="col-span-12 lg:col-span-4 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 flex flex-col overflow-hidden">

            <!-- Status tabs -->
            <div class="p-3 border-b border-gray-100 dark:border-dark-700">
                <div class="flex items-center space-x-1 bg-gray-50 dark:bg-dark-700 p-1 rounded-xl">
                    @foreach($statusTabs as $key => $tab)
                        <a href="{{ route('admin.inbox.index', array_merge(request()->except('status'), ['status' => $key])) }}"
                           class="flex-1 text-center px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                  {{ $activeStatus === $key ? 'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                            {{ $tab['label'] }}
                            <span class="ml-1 opacity-60">{{ $tab['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Channel chips -->
            <div class="px-3 py-2 border-b border-gray-100 dark:border-dark-700 overflow-x-auto">
                <div class="flex items-center space-x-2 whitespace-nowrap">
                    <a href="{{ route('admin.inbox.index', array_merge(request()->except('channel'), ['status' => $activeStatus])) }}"
                       class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors
                              {{ !$activeChannel ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-600' }}">
                        Tümü
                    </a>
                    @foreach($channels as $channel)
                        @php
                            $name = $channel->name();
                            $style = $channelStyles[$name] ?? ['label' => Str::headline($name)];
                            $isActive = $activeChannel === $name;
                            $enabled = $channel->isEnabled();
                        @endphp
                        <a href="{{ route('admin.inbox.index', array_merge(request()->except('channel'), ['channel' => $name, 'status' => $activeStatus])) }}"
                           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full transition-colors
                                  {{ $isActive ? ($style['bg'] . ' ' . $style['text']) : 'bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-600' }}
                                  {{ !$enabled ? 'opacity-50' : '' }}">
                            {{ $style['label'] }}
                            @unless($enabled)
                                <span class="ml-1 w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            @endunless
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Conversation list -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-dark-700">
                @forelse($conversations as $conv)
                    @php
                        $style = $channelStyles[$conv->channel] ?? ['label' => $conv->channel, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                        $contactName = $conv->contact?->full_name
                            ?? $conv->subject
                            ?? ('#' . ($conv->channel_thread_id ?? $conv->id));
                        $initials = $conv->contact
                            ? strtoupper(mb_substr($conv->contact->first_name, 0, 1) . mb_substr($conv->contact->last_name, 0, 1))
                            : strtoupper(mb_substr($contactName, 0, 2));
                    @endphp
                    <a href="{{ route('admin.inbox.show', $conv) }}"
                       class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-dark-700/60 transition-colors {{ $conv->unread_count > 0 ? 'bg-primary-50/40 dark:bg-primary-900/10' : '' }}">
                        <div class="flex items-start space-x-3">
                            <!-- Avatar -->
                            <div class="relative flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white text-xs font-semibold">
                                    {{ $initials ?: '??' }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $style['bg'] }} {{ $style['text'] }} border-2 border-white dark:border-dark-800">
                                    {{ mb_substr($style['label'], 0, 2) }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $contactName }}
                                    </p>
                                    @if($conv->last_message_at)
                                        <span class="ml-2 text-[11px] text-gray-400 flex-shrink-0">
                                            {{ $conv->last_message_at->diffForHumans(null, true) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between mt-0.5">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate {{ $conv->unread_count > 0 ? 'font-medium text-gray-700 dark:text-gray-300' : '' }}">
                                        @if($conv->last_message_direction === 'out')
                                            <span class="text-gray-400">→</span>
                                        @endif
                                        {{ $conv->last_message_preview ?: '—' }}
                                    </p>
                                    @if($conv->unread_count > 0)
                                        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-semibold rounded-full bg-primary-500 text-white flex-shrink-0">
                                            {{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                @if($conv->lead)
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        Lead #{{ $conv->lead->id }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-dark-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Sohbet yok</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Bu filtreyle eşleşen sohbet bulunamadı.
                        </p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- RIGHT: Empty state -->
        <section class="hidden lg:flex col-span-8 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 items-center justify-center">
            <div class="text-center p-8 max-w-md">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900/30 dark:to-blue-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Bir sohbet seçin</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sol panelden bir sohbete tıklayın veya filtrelerle aramanızı daraltın.
                    Mesajları yanıtlamak, atama yapmak ve durum güncellemek için açın.
                </p>
            </div>
        </section>
    </div>
</div>
@endsection
