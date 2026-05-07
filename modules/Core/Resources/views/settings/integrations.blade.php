@extends('layouts.admin')

@section('title', 'Entegrasyonlar')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Entegrasyonlar</h1>
            <p class="text-dark-400 mt-1">Harici servis bağlantılarını yönetin</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Portal Integrations -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Emlak Portalları</h2>
                <div class="space-y-4">
                    @php
                    $portals = [
                        ['name' => 'Sahibinden.com', 'color' => 'orange', 'key' => 'sahibinden'],
                        ['name' => 'Hepsiemlak.com', 'color' => 'red', 'key' => 'hepsiemlak'],
                        ['name' => 'EmlakJet', 'color' => 'blue', 'key' => 'emlakjet'],
                        ['name' => 'Zingat', 'color' => 'green', 'key' => 'zingat'],
                    ];
                    @endphp
                    @foreach($portals as $portal)
                    <div class="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-{{ $portal['color'] }}-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-{{ $portal['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $portal['name'] }}</p>
                                <p class="text-dark-400 text-xs">API entegrasyonu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">Yapılandırılmadı</span>
                            <button type="button" class="px-3 py-1.5 bg-dark-700 hover:bg-dark-600 text-white text-sm rounded-lg transition-colors">
                                Ayarla
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Communication -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">İletişim Servisleri</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-dark-800/50 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <p class="text-white font-medium text-sm">WhatsApp Business API</p>
                            </div>
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">Pasif</span>
                        </div>
                        <input type="text" name="whatsapp_api_key" placeholder="API Anahtarı" class="w-full px-3 py-2 bg-dark-700 border border-dark-600 rounded-lg text-white text-sm placeholder-dark-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                    </div>

                    <div class="p-4 bg-dark-800/50 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <p class="text-white font-medium text-sm">SMTP / E-posta</p>
                            </div>
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">Pasif</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="smtp_host" placeholder="SMTP Host" class="px-3 py-2 bg-dark-700 border border-dark-600 rounded-lg text-white text-sm placeholder-dark-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                            <input type="text" name="smtp_port" placeholder="Port" class="px-3 py-2 bg-dark-700 border border-dark-600 rounded-lg text-white text-sm placeholder-dark-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI -->
            <div class="bg-gradient-to-br from-purple-900/20 to-dark-900 border border-purple-500/20 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    Yapay Zeka
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">OpenAI API Anahtarı</label>
                        <input type="password" name="openai_api_key" placeholder="sk-..." class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">Model</label>
                        <select name="openai_model" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="gpt-4o">GPT-4o</option>
                            <option value="gpt-4o-mini">GPT-4o Mini</option>
                            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Ayarlar Menüsü</h2>
                <nav class="space-y-1">
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Genel</a>
                    <a href="{{ route('admin.settings.notifications') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Bildirimler</a>
                    <a href="{{ route('admin.settings.integrations') }}" class="flex items-center px-3 py-2 text-sm text-white bg-dark-800 rounded-lg font-medium">Entegrasyonlar</a>
                    <a href="{{ route('admin.settings.billing') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Faturalama</a>
                </nav>
            </div>
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <button type="submit" form="integrations-form" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
