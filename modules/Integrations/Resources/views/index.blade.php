@extends('layouts.admin')

@section('title', 'Entegrasyonlar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Entegrasyonlar</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Harici servis ve portal entegrasyonlarını yönetin</p>
        </div>
    </div>

    <!-- Portal Entegrasyonları -->
    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-globe text-primary-400 mr-2"></i>Portal Entegrasyonları
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($integrations as $integration)
                @if(in_array($integration['id'], ['sahibinden', 'hepsiemlak', 'emlakjet']))
                    <div class="bg-gray-200 dark:bg-dark-700 rounded-xl p-5 border border-dark-600 hover:border-{{ $integration['color'] }}-500/50 transition-colors">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-{{ $integration['color'] }}-500/20 rounded-xl flex items-center justify-center">
                                    <i class="{{ $integration['icon'] }} text-{{ $integration['color'] }}-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold">{{ $integration['name'] }}</h3>
                                    <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $integration['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($integration['status'] == 'active')
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="text-green-400 text-sm">Aktif</span>
                                @else
                                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                    <span class="text-gray-400 text-sm">Pasif</span>
                                @endif
                            </div>
                            <button class="px-3 py-1.5 bg-dark-600 text-gray-600 dark:text-dark-300 rounded-lg text-sm hover:bg-dark-500 transition-colors">
                                Ayarlar
                            </button>
                        </div>
                        
                        @if($integration['last_sync'])
                            <div class="mt-3 pt-3 border-t border-dark-600">
                                <p class="text-gray-500 dark:text-dark-400 text-xs">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Son senkronizasyon: {{ $integration['last_sync']->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- İletişim Entegrasyonları -->
    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-comments text-green-400 mr-2"></i>İletişim Entegrasyonları
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($integrations as $integration)
                @if(in_array($integration['id'], ['whatsapp', 'sms', 'call']))
                    <div class="bg-gray-200 dark:bg-dark-700 rounded-xl p-5 border border-dark-600 hover:border-{{ $integration['color'] }}-500/50 transition-colors">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-{{ $integration['color'] }}-500/20 rounded-xl flex items-center justify-center">
                                    <i class="{{ $integration['icon'] }} text-{{ $integration['color'] }}-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold">{{ $integration['name'] }}</h3>
                                    <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $integration['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($integration['status'] == 'active')
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="text-green-400 text-sm">Aktif</span>
                                @else
                                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                    <span class="text-gray-400 text-sm">Pasif</span>
                                @endif
                            </div>
                            <button class="px-3 py-1.5 bg-dark-600 text-gray-600 dark:text-dark-300 rounded-lg text-sm hover:bg-dark-500 transition-colors">
                                Ayarlar
                            </button>
                        </div>
                        
                        @if($integration['last_sync'])
                            <div class="mt-3 pt-3 border-t border-dark-600">
                                <p class="text-gray-500 dark:text-dark-400 text-xs">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Son aktivite: {{ $integration['last_sync']->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Diğer Entegrasyonlar -->
    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-plug text-purple-400 mr-2"></i>Diğer Entegrasyonlar
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($integrations as $integration)
                @if(in_array($integration['id'], ['payment', 'google_maps']))
                    <div class="bg-gray-200 dark:bg-dark-700 rounded-xl p-5 border border-dark-600 hover:border-{{ $integration['color'] }}-500/50 transition-colors">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-{{ $integration['color'] }}-500/20 rounded-xl flex items-center justify-center">
                                    <i class="{{ $integration['icon'] }} text-{{ $integration['color'] }}-400 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold">{{ $integration['name'] }}</h3>
                                    <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $integration['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($integration['status'] == 'active')
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="text-green-400 text-sm">Aktif</span>
                                @else
                                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                    <span class="text-gray-400 text-sm">Pasif</span>
                                @endif
                            </div>
                            <button class="px-3 py-1.5 bg-dark-600 text-gray-600 dark:text-dark-300 rounded-lg text-sm hover:bg-dark-500 transition-colors">
                                Ayarlar
                            </button>
                        </div>
                        
                        @if($integration['last_sync'])
                            <div class="mt-3 pt-3 border-t border-dark-600">
                                <p class="text-gray-500 dark:text-dark-400 text-xs">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Son aktivite: {{ $integration['last_sync']->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- API Bilgileri -->
    <div class="bg-gradient-to-br from-purple-900/30 to-dark-800 rounded-xl border border-purple-500/30 p-6">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-key text-purple-400"></i>
            </div>
            <div>
                <h3 class="text-white font-semibold">API Erişimi</h3>
                <p class="text-gray-500 dark:text-dark-400 text-sm">Harici uygulamalar için API anahtarları</p>
            </div>
        </div>
        
        <div class="bg-gray-50 dark:bg-dark-800/50 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">API Anahtarı</p>
                    <code class="text-white font-mono text-sm">••••••••••••••••••••••••••••••••</code>
                </div>
                <button class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition-colors">
                    <i class="fas fa-eye mr-1"></i>Göster
                </button>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            <button class="px-4 py-2 bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-dark-300 rounded-lg text-sm hover:bg-dark-600 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Yenile
            </button>
            <a href="#" class="text-purple-400 text-sm hover:text-purple-300">
                <i class="fas fa-book mr-1"></i>API Dokümantasyonu
            </a>
        </div>
    </div>

    <!-- Webhook Ayarları -->
    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-link text-cyan-400"></i>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Webhook Endpoints</h3>
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Gelen webhook istekleri için URL'ler</p>
                </div>
            </div>
            <button class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-lg text-sm hover:from-sky-500 hover:to-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Yeni Webhook
            </button>
        </div>
        
        <div class="space-y-3">
            <div class="bg-gray-200 dark:bg-dark-700 rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-white font-medium">Portal Sync Webhook</p>
                    <code class="text-gray-500 dark:text-dark-400 text-sm">{{ url('/api/webhooks/portal-sync') }}</code>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span class="text-green-400 text-sm">Aktif</span>
                </div>
            </div>
            
            <div class="bg-gray-200 dark:bg-dark-700 rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-white font-medium">Potansiyel Müşteri Yakalama Webhook</p>
                    <code class="text-gray-500 dark:text-dark-400 text-sm">{{ url('/api/webhooks/lead-capture') }}</code>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span class="text-green-400 text-sm">Aktif</span>
                </div>
            </div>
            
            <div class="bg-gray-200 dark:bg-dark-700 rounded-lg p-4 flex items-center justify-between">
                <div>
                    <p class="text-white font-medium">Call Events Webhook</p>
                    <code class="text-gray-500 dark:text-dark-400 text-sm">{{ url('/api/webhooks/call-events') }}</code>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                    <span class="text-gray-400 text-sm">Pasif</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
