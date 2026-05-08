@extends('layouts.admin')
@section('title', 'Entegrasyon Detayı')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Entegrasyon Ayarları</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ is_array($integration) ? ($integration['name'] ?? $integration) : $integration }}</p>
        </div>
        <a href="{{ route('admin.integrations.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Bağlantı Ayarları</h2>
                <form action="{{ route('admin.integrations.update', is_array($integration) ? $integration['id'] : $integration) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">API Anahtarı</label>
                        <input type="password" name="api_key" placeholder="••••••••••••••••"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="text-xs text-dark-500 mt-1">Mevcut anahtarı değiştirmek istiyorsanız girin.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">API Gizli Anahtarı</label>
                        <input type="password" name="api_secret" placeholder="••••••••••••••••"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Webhook URL</label>
                        <input type="url" name="webhook_url" placeholder="https://example.com/webhook"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Ayarları Kaydet</button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bağlantı Testi</h2>
                <p class="text-gray-500 dark:text-dark-400 text-sm mb-4">Entegrasyon bağlantısını test etmek için aşağıdaki butona tıklayın.</p>
                <button
                    onclick="testConnection()"
                    class="px-6 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Bağlantıyı Test Et
                </button>
                <div id="test-result" class="hidden mt-4 p-3 rounded-xl text-sm"></div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Durum</h2>
                <div class="space-y-3">
                    @php $status = is_array($integration) ? ($integration['status'] ?? 'inactive') : 'unknown'; @endphp
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full {{ $status === 'active' ? 'bg-green-500' : 'bg-dark-500' }}"></div>
                        <span class="text-white text-sm">{{ $status === 'active' ? 'Aktif' : 'Pasif' }}</span>
                    </div>
                    @if(is_array($integration) && isset($integration['last_sync']))
                    <div class="text-xs text-dark-500">Son sync: {{ is_string($integration['last_sync']) ? $integration['last_sync'] : \Carbon\Carbon::parse($integration['last_sync'])->diffForHumans() }}</div>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Açıklama</h2>
                <p class="text-gray-600 dark:text-dark-300 text-sm">{{ is_array($integration) ? ($integration['description'] ?? 'Entegrasyon açıklaması bulunamadı.') : 'Entegrasyon detayları.' }}</p>
            </div>
        </div>
    </div>
</div>
<script>
async function testConnection() {
    const btn = event.target.closest('button');
    const result = document.getElementById('test-result');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Test ediliyor...';
    try {
        const res = await fetch('{{ route("admin.integrations.test", is_array($integration) ? $integration["id"] : $integration) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        result.className = 'mt-4 p-3 rounded-xl text-sm ' + (data.success ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30');
        result.textContent = data.message || (data.success ? 'Bağlantı başarılı!' : 'Bağlantı hatası!');
        result.classList.remove('hidden');
    } catch (e) {
        result.className = 'mt-4 p-3 rounded-xl text-sm bg-red-500/20 text-red-400 border border-red-500/30';
        result.textContent = 'Bağlantı testi başarısız.';
        result.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Bağlantıyı Test Et';
}
</script>
@endsection
