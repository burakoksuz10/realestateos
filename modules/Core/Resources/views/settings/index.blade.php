@extends('layouts.admin')
@section('title', 'Ayarlar')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ayarlar</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Uygulama ayarlarını yönetin</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Nav -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-3">
                <nav class="space-y-1">
                    <a href="#genel" onclick="showSection('genel')" id="nav-genel"
                        class="settings-nav-item active flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Genel
                    </a>
                    <a href="#firma" onclick="showSection('firma')" id="nav-firma"
                        class="settings-nav-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors text-gray-600 dark:text-dark-300 hover:bg-gray-100 dark:bg-dark-800">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Firma Bilgileri
                    </a>
                    <a href="#profil" onclick="showSection('profil')" id="nav-profil"
                        class="settings-nav-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors text-gray-600 dark:text-dark-300 hover:bg-gray-100 dark:bg-dark-800">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Profil
                    </a>
                    <a href="#bildirimler" onclick="showSection('bildirimler')" id="nav-bildirimler"
                        class="settings-nav-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors text-gray-600 dark:text-dark-300 hover:bg-gray-100 dark:bg-dark-800">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Bildirimler
                    </a>
                    <a href="#guvenlik" onclick="showSection('guvenlik')" id="nav-guvenlik"
                        class="settings-nav-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors text-gray-600 dark:text-dark-300 hover:bg-gray-100 dark:bg-dark-800">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Güvenlik
                    </a>
                    <a href="#ai" onclick="showSection('ai')" id="nav-ai"
                        class="settings-nav-item flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors text-gray-600 dark:text-dark-300 hover:bg-gray-100 dark:bg-dark-800">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        AI Ayarları
                    </a>
                </nav>
            </div>
        </div>

        <!-- Content -->
        <div class="lg:col-span-3 space-y-6">

            <!-- Genel -->
            <div id="section-genel" class="settings-section bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Genel Ayarlar</h2>
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Uygulama Adı</label>
                        <input type="text" name="app_name" value="{{ config('app.name', 'RE-OS') }}"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Dil</label>
                            <select name="language" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="tr" selected>Türkçe</option>
                                <option value="en">English</option>
                                <option value="de">Deutsch</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Saat Dilimi</label>
                            <select name="timezone" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="Europe/Istanbul" selected>Europe/Istanbul (UTC+3)</option>
                                <option value="Europe/London">Europe/London (UTC+0)</option>
                                <option value="America/New_York">America/New_York (UTC-5)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Para Birimi</label>
                        <select name="currency" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="TRY" selected>Türk Lirası (₺)</option>
                            <option value="USD">US Dollar ($)</option>
                            <option value="EUR">Euro (€)</option>
                        </select>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Firma -->
            <div id="section-firma" class="settings-section hidden bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Firma Bilgileri</h2>
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Firma Adı</label>
                        <input type="text" name="company_name" value="{{ config('app.company_name', '') }}"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">E-posta</label>
                            <input type="email" name="company_email"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Telefon</label>
                            <input type="tel" name="company_phone"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Adres</label>
                        <textarea name="company_address" rows="3"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Profil -->
            <div id="section-profil" class="settings-section hidden bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Profil Bilgileri</h2>
                <form action="{{ route('admin.settings.profile.update') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Ad Soyad</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">E-posta</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Profili Güncelle</button>
                    </div>
                </form>
            </div>

            <!-- Bildirimler -->
            <div id="section-bildirimler" class="settings-section hidden bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Bildirim Ayarları</h2>
                <div class="space-y-4">
                    @foreach([
                        ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'blue', 'title' => 'E-posta Bildirimleri', 'desc' => 'E-posta ile bildirim al', 'checked' => true],
                        ['icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'green', 'title' => 'Push Bildirimleri', 'desc' => 'Tarayıcı bildirimleri', 'checked' => true],
                        ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'purple', 'title' => 'SMS Bildirimleri', 'desc' => 'SMS ile bildirim al', 'checked' => false],
                        ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'orange', 'title' => 'Yeni Potansiyel Müşteri Bildirimi', 'desc' => 'Yeni potansiyel müşteri geldiğinde bildir', 'checked' => true],
                    ] as $notif)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-{{ $notif['color'] }}-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-{{ $notif['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $notif['icon'] }}"></path></svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $notif['title'] }}</p>
                                <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $notif['desc'] }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" {{ $notif['checked'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                    @endforeach
                    <div class="pt-2">
                        <button type="button" onclick="alert('Kaydedildi')" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                    </div>
                </div>
            </div>

            <!-- Güvenlik -->
            <div id="section-guvenlik" class="settings-section hidden bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Şifre Değiştir</h2>
                <form action="{{ route('admin.settings.password.update') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Mevcut Şifre</label>
                        <input type="password" name="current_password"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Yeni Şifre</label>
                            <input type="password" name="password"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Şifre Tekrar</label>
                            <input type="password" name="password_confirmation"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Şifreyi Güncelle</button>
                    </div>
                </form>
            </div>

            <!-- AI -->
            <div id="section-ai" class="settings-section hidden bg-white dark:bg-dark-900 border border-gray-200 dark:border-violet-500/20 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    AI Ayarları
                </h2>
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
                    @csrf @method('PUT')

                    <!-- OpenAI API Key -->
                    <div class="p-4 bg-violet-50 dark:bg-violet-900/10 border border-violet-200 dark:border-violet-500/20 rounded-xl">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-white mb-1.5">OpenAI API Anahtarı</label>
                        <div class="relative">
                            <input type="password" name="openai_api_key" id="openai_key_input"
                                placeholder="sk-proj-..."
                                value="{{ $openaiKey ? str_repeat('•', 20) . substr($openaiKey, -4) : '' }}"
                                autocomplete="new-password"
                                class="w-full px-4 py-2.5 pr-24 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500">
                            <button type="button" onclick="clearAndFocusKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-violet-600 dark:text-violet-400 hover:underline font-medium">
                                {{ $openaiKey ? 'Değiştir' : 'Gir' }}
                            </button>
                        </div>
                        @if($openaiKey)
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            API anahtarı kayıtlı
                        </p>
                        @else
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">AI özellikleri ve haber çekme için gereklidir. <a href="https://platform.openai.com/api-keys" target="_blank" class="text-violet-600 dark:text-violet-400 hover:underline">API anahtarı al →</a></p>
                        @endif
                    </div>

                    <!-- OpenAI Model -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">AI Modeli</label>
                        <select name="openai_model" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
                            @foreach([
                                'gpt-4o-mini'  => 'GPT-4o Mini (Hızlı & Ekonomik)',
                                'gpt-4.1-mini' => 'GPT-4.1 Mini (En Güncel)',
                                'gpt-4o'       => 'GPT-4o (Güçlü)',
                                'gpt-4.1'      => 'GPT-4.1 (En Güçlü)',
                            ] as $value => $label)
                            <option value="{{ $value }}" {{ ($openaiModel ?? 'gpt-4o-mini') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Haber özetleme ve AI özellikleri için kullanılacak model.</p>
                    </div>

                    <!-- AI Features -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-3">AI Özellikleri</label>
                        <div class="space-y-2">
                            @foreach([
                                ['key' => 'news_enabled',  'label' => 'Haber Takibi',       'desc' => 'Emlak haberlerini otomatik çek ve özetle (her 3 saatte)'],
                                ['key' => 'ai_copilot',    'label' => 'AI Copilot',          'desc' => 'Potansiyel müşteri ve fırsat önerileri'],
                                ['key' => 'ai_valuation',  'label' => 'Otomatik Değerleme', 'desc' => 'AI destekli gayrimenkul değerlemesi'],
                                ['key' => 'ai_content',    'label' => 'İçerik Üretimi',     'desc' => 'Otomatik ilan açıklaması ve sosyal medya içeriği'],
                                ['key' => 'ai_matching',   'label' => 'Akıllı Eşleştirme', 'desc' => 'Potansiyel müşteri-ilan eşleştirme'],
                            ] as $feature)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $feature['label'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $feature['desc'] }}</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="{{ $feature['key'] }}" value="1" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                        <a href="{{ route('admin.ai.news.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-300 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">Haberlere Git →</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
.settings-nav-item.active {
    background-color: rgb(var(--color-primary-600) / 0.2);
    color: rgb(var(--color-primary-400));
    border: 1px solid rgb(var(--color-primary-500) / 0.3);
}
</style>

<script>
function clearAndFocusKey() {
    const input = document.getElementById('openai_key_input');
    input.value = '';
    input.focus();
    input.placeholder = 'sk-proj-...';
}
function showSection(name) {
    document.querySelectorAll('.settings-section').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.settings-nav-item').forEach(el => el.classList.remove('active', 'bg-primary-600/20', 'text-primary-400', 'border', 'border-primary-500/30'));
    document.getElementById('section-' + name).classList.remove('hidden');
    const navItem = document.getElementById('nav-' + name);
    navItem.classList.add('active');
    navItem.classList.remove('text-gray-600 dark:text-dark-300');
}
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('section-' + hash)) showSection(hash);
});
</script>
@endsection
