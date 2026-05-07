@extends('layouts.admin')

@section('title', 'Portal Senkronizasyonu')

@section('content')
<div class="space-y-6" x-data="portalSync()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Portal Senkronizasyonu</h1>
            <p class="text-dark-400 mt-1">İlanlarınızı emlak portallarına senkronize edin</p>
        </div>
        <button @click="syncAll()" :disabled="syncing" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" :class="syncing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Tümünü Senkronize Et
        </button>
    </div>

    <!-- Status Message -->
    <div x-show="statusMsg" x-transition x-cloak class="p-4 rounded-xl text-sm"
         :class="statusType === 'success' ? 'bg-green-500/20 border border-green-500/30 text-green-400' : 'bg-red-500/20 border border-red-500/30 text-red-400'">
        <span x-text="statusMsg"></span>
    </div>

    <!-- Portal Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($portals as $key => $portal)
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-white font-semibold text-sm">{{ $portal['name'] }}</span>
                <span class="w-2.5 h-2.5 rounded-full bg-{{ $portal['color'] }}-400"></span>
            </div>
            <p class="text-dark-400 text-xs mb-3">API entegrasyonu yapılandırılmamış</p>
            <span class="px-2 py-0.5 text-xs rounded-full bg-dark-700 text-dark-400">Demo Mod</span>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Listings Table -->
        <div class="lg:col-span-2 bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-dark-700/50">
                <h2 class="text-lg font-semibold text-white">Aktif İlanlar</h2>
            </div>
            <div class="divide-y divide-dark-700/50">
                @forelse($listings as $listing)
                <div class="flex items-center justify-between p-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('admin.listings.show', $listing) }}" class="text-white text-sm font-medium hover:text-primary-400 truncate block">{{ $listing->title }}</a>
                        <p class="text-dark-400 text-xs mt-0.5">{{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }} · ₺{{ number_format($listing->price ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        @foreach($portals as $key => $portal)
                        <button @click="syncListing({{ $listing->id }}, '{{ $key }}')"
                                :disabled="syncingId === {{ $listing->id }}"
                                class="px-2 py-1 text-xs bg-dark-700 hover:bg-dark-600 text-dark-300 hover:text-white rounded-lg transition-colors disabled:opacity-50">
                            {{ strtoupper(substr($key, 0, 3)) }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <p class="text-dark-400 text-sm">Aktif ilan bulunamadı.</p>
                </div>
                @endforelse
            </div>
            @if($listings->hasPages())
            <div class="p-4 border-t border-dark-700/50">
                {{ $listings->links() }}
            </div>
            @endif
        </div>

        <!-- Recent Logs -->
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-white mb-4">Son İşlemler</h2>
            <div class="space-y-3">
                @foreach($logs as $log)
                <div class="flex items-start gap-3">
                    <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $log['status'] === 'success' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-medium">{{ $log['portal'] }}</p>
                        <p class="text-dark-400 text-xs truncate">{{ $log['listing'] }}</p>
                        <p class="text-dark-500 text-xs">{{ $log['message'] }}</p>
                        <p class="text-dark-600 text-xs mt-0.5">{{ $log['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
                <p class="text-yellow-400 text-xs font-medium mb-1">Demo Mod</p>
                <p class="text-dark-400 text-xs">Gerçek portal entegrasyonu için ayarlar sayfasından API anahtarlarını ekleyin.</p>
            </div>
        </div>
    </div>
</div>

<script>
function portalSync() {
    return {
        syncing: false,
        syncingId: null,
        statusMsg: '',
        statusType: 'success',

        async syncAll() {
            this.syncing = true;
            try {
                const res = await fetch('{{ route('admin.portal-sync.sync-all') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.showStatus(data.message, data.success ? 'success' : 'error');
            } catch (e) {
                this.showStatus('İşlem sırasında bir hata oluştu.', 'error');
            } finally {
                this.syncing = false;
            }
        },

        async syncListing(id, portal) {
            this.syncingId = id;
            try {
                const res = await fetch(`/admin/portal-sync/${id}/sync`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ portal })
                });
                const data = await res.json();
                this.showStatus(data.message, data.success ? 'success' : 'error');
            } catch (e) {
                this.showStatus('Senkronizasyon başarısız.', 'error');
            } finally {
                this.syncingId = null;
            }
        },

        showStatus(msg, type) {
            this.statusMsg = msg;
            this.statusType = type;
            setTimeout(() => { this.statusMsg = ''; }, 4000);
        }
    }
}
</script>
@endsection
