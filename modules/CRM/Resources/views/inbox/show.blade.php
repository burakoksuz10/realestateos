@extends('layouts.admin')

@section('title', 'Sohbet — ' . ($conversation->contact?->full_name ?? $conversation->channel_thread_id ?? '#' . $conversation->id))

@php
    $channelStyles = [
        'telegram'           => ['label' => 'Telegram',  'bg' => 'bg-sky-100 dark:bg-sky-900/30',     'text' => 'text-sky-700 dark:text-sky-300'],
        'whatsapp'           => ['label' => 'WhatsApp',  'bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300'],
        'sms'                => ['label' => 'SMS',       'bg' => 'bg-purple-100 dark:bg-purple-900/30','text' => 'text-purple-700 dark:text-purple-300'],
        'email'              => ['label' => 'E-posta',   'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300'],
        'instagram_dm'       => ['label' => 'Instagram', 'bg' => 'bg-pink-100 dark:bg-pink-900/30',   'text' => 'text-pink-700 dark:text-pink-300'],
        'facebook_messenger' => ['label' => 'Messenger', 'bg' => 'bg-blue-100 dark:bg-blue-900/30',   'text' => 'text-blue-700 dark:text-blue-300'],
    ];
    $style = $channelStyles[$conversation->channel] ?? ['label' => $conversation->channel, 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'];

    $contactName = $conversation->contact?->full_name
        ?? $conversation->subject
        ?? ('#' . ($conversation->channel_thread_id ?? $conversation->id));
    $initials = $conversation->contact
        ? strtoupper(mb_substr($conversation->contact->first_name, 0, 1) . mb_substr($conversation->contact->last_name, 0, 1))
        : strtoupper(mb_substr($contactName, 0, 2));

    $statusOptions = [
        'open'     => 'Açık',
        'archived' => 'Arşiv',
        'closed'   => 'Kapalı',
    ];
@endphp

@section('content')
<div class="space-y-4">

    <!-- Back link -->
    <a href="{{ route('admin.inbox.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Gelen Kutusu
    </a>

    @if(session('success'))
        <div class="px-4 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm rounded-xl border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 flex flex-col h-[calc(100vh-220px)] min-h-[500px]">

        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700 flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="h-11 w-11 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                    {{ $initials ?: '??' }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                            {{ $contactName }}
                        </h2>
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full {{ $style['bg'] }} {{ $style['text'] }}">
                            {{ $style['label'] }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center space-x-3">
                        @if($conversation->contact?->phone)
                            <span>{{ $conversation->contact->phone }}</span>
                        @endif
                        @if($conversation->contact?->email)
                            <span>{{ $conversation->contact->email }}</span>
                        @endif
                        @if($conversation->lead)
                            <a href="{{ route('admin.leads.show', $conversation->lead) }}" class="text-primary-600 dark:text-primary-400 hover:underline">
                                Lead #{{ $conversation->lead->id }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-2">
                <!-- Assignee -->
                <form method="POST" action="{{ route('admin.inbox.assign', $conversation) }}" class="flex items-center space-x-2">
                    @csrf
                    <select name="assigned_to" onchange="this.form.submit()"
                            class="text-xs px-3 py-1.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Atanmamış</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ $conversation->assigned_to === $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Status -->
                <form method="POST" action="{{ route('admin.inbox.status', $conversation) }}" class="flex items-center space-x-2">
                    @csrf
                    <select name="status" onchange="this.form.submit()"
                            class="text-xs px-3 py-1.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" {{ $conversation->status === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Messages -->
        <div id="inbox-messages" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50 dark:bg-dark-900/30">
            @forelse($messages as $msg)
                @php
                    $isOut = $msg->direction === 'out';
                @endphp
                <div class="flex {{ $isOut ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] {{ $isOut ? 'order-2' : '' }}">
                        <div class="{{ $isOut ? 'bg-gradient-to-br from-sky-500 to-blue-600 text-white' : 'bg-white dark:bg-dark-700 text-gray-900 dark:text-white border border-gray-100 dark:border-dark-600' }} rounded-2xl px-4 py-2.5 shadow-sm">
                            @if($msg->body)
                                <p class="text-sm whitespace-pre-wrap break-words">{{ $msg->body }}</p>
                            @endif

                            @if(!empty($msg->attachments))
                                <div class="mt-2 space-y-1">
                                    @foreach($msg->attachments as $att)
                                        @php
                                            $type = $att['type'] ?? 'file';
                                            $url  = $att['url']  ?? $att['path'] ?? null;
                                            $name = $att['name'] ?? basename((string) $url);
                                        @endphp
                                        @if($type === 'photo' && $url)
                                            <img src="{{ $url }}" alt="" class="rounded-lg max-w-full max-h-64 object-cover">
                                        @elseif($type === 'voice' || $type === 'audio')
                                            <div class="flex items-center space-x-2 text-xs {{ $isOut ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m-4 0h8m-12-9V8a4 4 0 118 0v3"/>
                                                </svg>
                                                <span>Sesli not{{ isset($att['duration']) ? ' • ' . $att['duration'] . 's' : '' }}</span>
                                            </div>
                                        @else
                                            <div class="flex items-center space-x-2 text-xs {{ $isOut ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                <span>{{ $name ?: 'Ek' }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if($msg->ai_summary)
                                <div class="mt-2 pt-2 border-t {{ $isOut ? 'border-white/20' : 'border-gray-200 dark:border-dark-600' }}">
                                    <p class="text-[11px] {{ $isOut ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }}">
                                        <span class="font-medium">AI özet:</span> {{ $msg->ai_summary }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div class="mt-1 text-[10px] {{ $isOut ? 'text-right' : '' }} text-gray-400">
                            @if($msg->sentByUser)
                                <span>{{ $msg->sentByUser->name }} • </span>
                            @endif
                            <span>{{ $msg->created_at->format('d.m H:i') }}</span>
                            @if($isOut)
                                <span class="ml-1">
                                    @if($msg->status === 'read') · okundu
                                    @elseif($msg->status === 'delivered') · iletildi
                                    @elseif($msg->status === 'sent') · gönderildi
                                    @elseif($msg->status === 'failed') · <span class="text-red-500">başarısız</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Henüz mesaj yok.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Composer -->
        <div class="p-4 border-t border-gray-100 dark:border-dark-700">
            <form method="POST" action="{{ route('admin.inbox.send', $conversation) }}" class="flex items-end space-x-3">
                @csrf
                <div class="flex-1">
                    <textarea name="body" rows="2" required
                              placeholder="Mesajınızı yazın..."
                              class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl hover:from-sky-600 hover:to-blue-700 transition-colors text-sm font-medium shadow-sm shadow-blue-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Gönder
                </button>
            </form>
            <p class="text-[11px] text-gray-400 mt-2">
                Kanal: <span class="font-medium">{{ $style['label'] }}</span>
                @if($conversation->channel_thread_id)
                    · <span>{{ $conversation->channel_thread_id }}</span>
                @endif
            </p>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom on load
    (function() {
        const el = document.getElementById('inbox-messages');
        if (el) el.scrollTop = el.scrollHeight;
    })();
</script>
@endsection
