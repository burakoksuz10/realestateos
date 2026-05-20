@extends('layouts.admin')

@section('title', 'Portal Senkronizasyonu')

@section('content')
<div class="space-y-6" x-data="portalSync()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Portal Senkronizasyonu</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">İlanlarınızı Türkiye'nin emlak portallarına otomatik yayınlayın</p>
        </div>
    </div>

    <!-- Status Message -->
    <div x-show="statusMsg" x-transition x-cloak class="p-4 rounded-xl text-sm"
         :class="statusType === 'success' ? 'bg-green-500/20 border border-green-500/30 text-green-400' : 'bg-red-500/20 border border-red-500/30 text-red-400'">
        <span x-text="statusMsg"></span>
    </div>

    <!-- Portal Status Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ($portalInfo as $key => $portal)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-white font-semibold">{{ $portal['name'] }}</span>
                    @if ($portal['configured'])
                        <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-500/20 text-emerald-300">Aktif</span>
                    @else
                        <span class="px-2 py-0.5 text-xs rounded-full bg-amber-500/20 text-amber-300">Kurulum gerek</span>
                    @endif
                </div>
                @if ($portal['configured'])
                    <p class="text-gray-500 dark:text-dark-400 text-xs">
                        Son senkron: {{ $portal['last_sync'] ? $portal['last_sync']->diffForHumans() : 'henüz yok' }}
                    </p>
                @else
                    <p class="text-gray-500 dark:text-dark-400 text-xs">.env'e API anahtarı eklenmemiş.</p>
                    <code class="text-xs text-gray-400">{{ strtoupper($key) }}_API_KEY=...</code>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Listings -->
        <div class="lg:col-span-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-200 dark:border-dark-700/50">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktif İlanlar</h2>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-dark-700/50">
                @forelse ($listings as $listing)
                    <div class="flex items-center justify-between p-4">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.listings.show', $listing) }}" class="text-white text-sm font-medium hover:text-primary-400 truncate block">{{ $listing->title }}</a>
                            <p class="text-gray-500 dark:text-dark-400 text-xs mt-0.5">
                                {{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }} ·
                                ₺{{ number_format($listing->price ?? 0, 0, ',', '.') }} · {{ $listing->reference_no }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @foreach ($portalInfo as $key => $portal)
                                <button @click="syncListing({{ $listing->id }}, '{{ $key }}')"
                                        :disabled="busy[`{{ $listing->id }}-{{ $key }}`]"
                                        class="px-2 py-1 text-xs rounded-lg transition-colors disabled:opacity-50
                                               {{ $portal['configured'] ? 'bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300' : 'bg-gray-100 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}"
                                        title="{{ $portal['name'] }}{{ $portal['configured'] ? '' : ' (yapılandırılmamış)' }}">
                                    {{ strtoupper(substr($key, 0, 3)) }}
                                </button>
                            @endforeach
                            <button @click="syncAll({{ $listing->id }})" :disabled="busy[`{{ $listing->id }}-all`]"
                                    class="px-3 py-1 text-xs bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 rounded-lg transition-colors disabled:opacity-50">
                                Tümü
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="text-gray-500 dark:text-dark-400 text-sm">Aktif ilan bulunamadı.</p>
                    </div>
                @endforelse
            </div>
            @if ($listings->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-dark-700/50">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>

        <!-- Logs -->
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Son İşlemler</h2>
            <div class="space-y-3 max-h-[600px] overflow-y-auto">
                @forelse ($logs as $log)
                    <div class="flex items-start gap-3 pb-3 border-b border-gray-100 dark:border-dark-800/50 last:border-0">
                        <span class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $log->status === 'success' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-xs font-medium">{{ $log->portal_label }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs truncate">
                                {{ $log->listing?->title ?? "İlan #{$log->listing_id}" }}
                            </p>
                            <p class="text-gray-400 dark:text-dark-500 text-xs">
                                {{ ucfirst($log->action) }} · {{ $log->error_message ? mb_strimwidth($log->error_message, 0, 60, '…') : 'OK' }}
                            </p>
                            <p class="text-gray-500 dark:text-dark-500 text-xs mt-0.5">{{ optional($log->synced_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-dark-400 text-sm text-center py-4">Henüz işlem yok.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function portalSync() {
    return {
        busy: {},
        statusMsg: '',
        statusType: 'success',

        async syncAll(listingId) {
            const key = `${listingId}-all`;
            this.busy[key] = true;
            try {
                const res = await fetch(`/admin/portal-sync/${listingId}/sync-all`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.showStatus(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                this.showStatus('İşlem sırasında hata oluştu.', 'error');
            } finally {
                this.busy[key] = false;
            }
        },

        async syncListing(listingId, portal) {
            const key = `${listingId}-${portal}`;
            this.busy[key] = true;
            try {
                const res = await fetch(`/admin/portal-sync/${listingId}/sync`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ portal })
                });
                const data = await res.json();
                this.showStatus(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => window.location.reload(), 1500);
            } catch (e) {
                this.showStatus('Senkronizasyon başarısız.', 'error');
            } finally {
                this.busy[key] = false;
            }
        },

        showStatus(msg, type) {
            this.statusMsg = msg;
            this.statusType = type;
            setTimeout(() => { this.statusMsg = ''; }, 5000);
        }
    }
}
</script>
@endsection
