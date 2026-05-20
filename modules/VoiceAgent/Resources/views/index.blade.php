@extends('layouts.admin')

@section('title', 'Sesli AI Sekreter')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sesli AI Sekreter</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            ElevenLabs Agents + Netgsm hat ile gerçek zamanlı sesli müşteri karşılama. Çağrılar otomatik karşılanır, ilan sorgulanır, lead oluşturulur, gerektiğinde danışmana bağlanır.
        </p>
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

    <!-- Status banner -->
    <div class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-900/20 dark:to-indigo-900/20 border border-sky-200 dark:border-sky-800 rounded-2xl p-5 flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-white dark:bg-dark-800 flex items-center justify-center shadow-sm">
            <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m-4 0h8m-12-9V8a4 4 0 118 0v3"/>
            </svg>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ $config->is_active ? 'Aktif' : 'Pasif' }}
                </h3>
                @if($config->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                        Yayında
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                ElevenLabs Agent ID: <code class="text-[11px] bg-white dark:bg-dark-800 px-1.5 py-0.5 rounded">{{ $config->elevenlabs_agent_id ?: '— henüz kurulmadı —' }}</code>
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.voice-agent.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Aktivasyon -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Yayın Durumu</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pasifken gelen çağrılar normal akıştan ilerler.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 peer-focus:ring-2 peer-focus:ring-sky-400 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
                </label>
            </div>
        </div>

        <!-- Routing -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Yönlendirme Modu</h3>
            <div class="space-y-2">
                @foreach($modeOptions as $key => $opt)
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 dark:border-dark-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700/40 transition-colors {{ $config->routing_mode === $key ? 'ring-2 ring-sky-500 border-sky-500' : '' }}">
                        <input type="radio" name="routing_mode" value="{{ $key }}" {{ $config->routing_mode === $key ? 'checked' : '' }} class="mt-1 text-sky-500 focus:ring-sky-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $opt['label'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $opt['description'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Telefon numaraları -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Transfer Numaraları</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sekreter telefonu</label>
                    <input type="text" name="secretary_phone" value="{{ old('secretary_phone', $config->secretary_phone) }}" placeholder="+90 555 ..."
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Varsayılan danışman (fallback)</label>
                    <input type="text" name="default_agent_phone" value="{{ old('default_agent_phone', $config->default_agent_phone) }}" placeholder="+90 555 ..."
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Çalma süresi (sn)</label>
                    <input type="number" name="ring_timeout_seconds" min="5" max="60" value="{{ old('ring_timeout_seconds', $config->ring_timeout_seconds ?: 15) }}"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
        </div>

        <!-- Mesai saatleri -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Mesai Saatleri</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Başlangıç</label>
                    <input type="time" name="business_hours_start" value="{{ old('business_hours_start', $config->business_hours_start ? \Carbon\Carbon::parse($config->business_hours_start)->format('H:i') : '09:00') }}"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Bitiş</label>
                    <input type="time" name="business_hours_end" value="{{ old('business_hours_end', $config->business_hours_end ? \Carbon\Carbon::parse($config->business_hours_end)->format('H:i') : '19:00') }}"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Saat dilimi</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $config->timezone ?: 'Europe/Istanbul') }}"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 pb-2">
                        <input type="checkbox" name="weekend_active" value="1" {{ $config->weekend_active ? 'checked' : '' }} class="rounded text-sky-500 focus:ring-sky-500">
                        Hafta sonu açık
                    </label>
                </div>
            </div>
        </div>

        <!-- ElevenLabs -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">ElevenLabs Agent Bağlantısı</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Agent ID</label>
                    <input type="text" name="elevenlabs_agent_id" value="{{ old('elevenlabs_agent_id', $config->elevenlabs_agent_id) }}" placeholder="agent_xxxxxxxx..."
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white font-mono">
                    <p class="text-[11px] text-gray-400 mt-1">ElevenLabs dashboard → Conversational AI → Agents'tan kopyala</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Varsayılan ses (Voice ID)</label>
                    <input type="text" name="default_voice_id" value="{{ old('default_voice_id', $config->default_voice_id) }}" placeholder="EXAVITQu4vr4xnSDxMaL"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white font-mono">
                </div>
            </div>
        </div>

        <!-- Prompt + Karşılama -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">AI Karakteri</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Karşılama cümlesi</label>
                    <input type="text" name="greeting_template" value="{{ old('greeting_template', $config->greeting_template) }}"
                           class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sistem Promptu (ElevenLabs Agent'a kopyala)</label>
                    <textarea name="system_prompt" rows="14"
                              class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white font-mono resize-y">{{ old('system_prompt', $config->system_prompt ?: $defaultPrompt) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Dil kodu</label>
                        <input type="text" name="language" value="{{ old('language', $config->language ?: 'tr') }}" maxlength="5"
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ofis Telegram kanal ID</label>
                        <input type="text" name="telegram_office_channel" value="{{ old('telegram_office_channel', $config->telegram_office_channel) }}" placeholder="-100..."
                               class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white font-mono">
                    </div>
                </div>
            </div>
        </div>

        <!-- API entegrasyon bilgisi -->
        <div class="bg-gray-50 dark:bg-dark-700/40 rounded-2xl border border-gray-200 dark:border-dark-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">ElevenLabs Agent İçin Tool Endpoint'leri</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Bu URL'leri ElevenLabs Agent dashboard'da Tools bölümüne ekle. Authorization header: <code class="bg-white dark:bg-dark-800 px-1.5 py-0.5 rounded">X-Voice-Agent-Token: {{ $sharedSecret ?: '— .env\'e VOICE_AGENT_SHARED_SECRET ekle —' }}</code></p>
            <div class="space-y-1 text-xs font-mono text-gray-700 dark:text-gray-300">
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-green-600 font-semibold">POST</span> /api/voice-agent/tools/search-listing</span>
                    <span class="text-gray-400">İlan ara</span>
                </div>
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-green-600 font-semibold">POST</span> /api/voice-agent/tools/create-lead</span>
                    <span class="text-gray-400">Lead oluştur</span>
                </div>
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-green-600 font-semibold">POST</span> /api/voice-agent/tools/request-transfer</span>
                    <span class="text-gray-400">İnsana bağlanma kararı</span>
                </div>
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-green-600 font-semibold">POST</span> /api/voice-agent/tools/pre-call-brief</span>
                    <span class="text-gray-400">Danışmana ön bilgi</span>
                </div>
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-green-600 font-semibold">POST</span> /api/voice-agent/tools/book-callback</span>
                    <span class="text-gray-400">Randevu al</span>
                </div>
                <div class="flex items-center justify-between bg-white dark:bg-dark-800 px-3 py-2 rounded-lg">
                    <span><span class="text-blue-600 font-semibold">POST</span> /api/voice-agent/webhook</span>
                    <span class="text-gray-400">Post-call webhook</span>
                </div>
            </div>
        </div>

        <!-- Kaydet -->
        <div class="flex items-center justify-end">
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white rounded-xl text-sm font-medium shadow-sm shadow-blue-500/20">
                Ayarları kaydet
            </button>
        </div>
    </form>
</div>
@endsection
