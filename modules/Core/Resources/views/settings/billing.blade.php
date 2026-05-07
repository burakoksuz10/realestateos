@extends('layouts.admin')

@section('title', 'Faturalama')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Faturalama</h1>
            <p class="text-dark-400 mt-1">Abonelik ve ödeme bilgileri</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Plan -->
            <div class="bg-gradient-to-br from-primary-900/30 to-dark-900 border border-primary-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Mevcut Plan</h2>
                    <span class="px-3 py-1 bg-primary-500/20 text-primary-400 text-sm font-medium rounded-full">Aktif</span>
                </div>
                <div class="flex items-end gap-2 mb-4">
                    <span class="text-4xl font-bold text-white">Pro</span>
                    <span class="text-dark-400 mb-1">/ aylık</span>
                </div>
                <ul class="space-y-2 mb-6">
                    @foreach(['Sınırsız İlan', '50 Kullanıcı', '10 Ofis', 'AI Copilot', 'Portal Senkronizasyonu', 'API Erişimi'] as $feature)
                    <li class="flex items-center gap-2 text-dark-300 text-sm">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <button class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white text-sm rounded-xl transition-colors">
                    Planı Değiştir
                </button>
            </div>

            <!-- Payment Method -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Ödeme Yöntemi</h2>
                <div class="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">**** **** **** 4242</p>
                            <p class="text-dark-400 text-xs">Son kullanma: 12/27</p>
                        </div>
                    </div>
                    <button class="px-3 py-1.5 bg-dark-700 hover:bg-dark-600 text-white text-sm rounded-lg transition-colors">
                        Değiştir
                    </button>
                </div>
            </div>

            <!-- Invoice History -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Fatura Geçmişi</h2>
                <div class="space-y-3">
                    @php
                    $invoices = [
                        ['date' => 'Mayıs 2026', 'amount' => '₺2.499', 'status' => 'Ödendi'],
                        ['date' => 'Nisan 2026', 'amount' => '₺2.499', 'status' => 'Ödendi'],
                        ['date' => 'Mart 2026', 'amount' => '₺2.499', 'status' => 'Ödendi'],
                    ];
                    @endphp
                    @foreach($invoices as $invoice)
                    <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $invoice['date'] }}</p>
                            <p class="text-dark-400 text-xs">Pro Plan</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-white font-medium text-sm">{{ $invoice['amount'] }}</span>
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">{{ $invoice['status'] }}</span>
                            <button class="text-primary-400 hover:text-primary-300 text-xs">İndir</button>
                        </div>
                    </div>
                    @endforeach
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
                    <a href="{{ route('admin.settings.integrations') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Entegrasyonlar</a>
                    <a href="{{ route('admin.settings.billing') }}" class="flex items-center px-3 py-2 text-sm text-white bg-dark-800 rounded-lg font-medium">Faturalama</a>
                </nav>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-3">Sonraki Ödeme</h2>
                <p class="text-white font-bold text-xl">₺2.499</p>
                <p class="text-dark-400 text-sm mt-1">1 Haziran 2026</p>
            </div>

            <div class="bg-red-900/20 border border-red-500/20 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-red-400 mb-2">Aboneliği İptal Et</h2>
                <p class="text-dark-400 text-xs mb-4">Aboneliği iptal etmek verilerinizi silmez ancak erişiminizi kısıtlar.</p>
                <button class="w-full px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-medium rounded-xl transition-colors">
                    İptal Et
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
