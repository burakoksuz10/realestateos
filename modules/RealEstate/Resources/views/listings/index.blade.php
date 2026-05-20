@extends('layouts.admin')

@section('title', 'İlanlar')

@section('content')
<div class="space-y-6" x-data="listingImport()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İlanlar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tüm emlak ilanlarını yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="open = true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                AI ile İçe Aktar
            </button>
            <a href="{{ route('admin.listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni İlan
            </a>
        </div>
    </div>

    {{-- ============ IMPORT MODAL ============ --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @click.self="closeModal()">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">AI ile İlan İçe Aktar</h2>
                    <p class="text-xs text-gray-500 dark:text-dark-400 mt-1">Sahibinden, Hepsiemlak veya EmlakJet linkini yapıştır, sistem ilanını otomatik doldursun.</p>
                </div>
                <button @click="closeModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6">
                {{-- Step 1: URL --}}
                <template x-if="step === 'url'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-dark-200 mb-2">Portal İlan Linki</label>
                        <input type="url" x-model="url" placeholder="https://www.sahibinden.com/ilan/..."
                               class="w-full px-4 py-3 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p x-show="error" x-cloak class="mt-2 text-sm text-rose-400" x-text="error"></p>
                        <button @click="fetchPreview()" :disabled="!url || loading"
                                class="w-full mt-4 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50 text-white font-medium rounded-xl flex items-center justify-center gap-2">
                            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span x-text="loading ? 'AI sayfa okuyor...' : 'İlanı Getir'"></span>
                        </button>
                    </div>
                </template>

                {{-- Step 2: Preview --}}
                <template x-if="step === 'preview'">
                    <div class="space-y-4">
                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3 text-emerald-300 text-sm">
                            ✓ AI sayfayı çözümledi. Kontrol et, eksikleri düzelt, kaydet.
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Başlık</label>
                            <input type="text" x-model="data.title" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Fiyat (TL)</label>
                                <input type="number" x-model.number="data.price" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Brüt m²</label>
                                <input type="number" x-model.number="data.gross_sqm" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Oda</label>
                                <input type="number" x-model.number="data.room_count" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Tip</label>
                                <select x-model="data.type" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                                    <option value="apartment">Daire</option>
                                    <option value="house">Müstakil Ev</option>
                                    <option value="villa">Villa</option>
                                    <option value="office">Ofis</option>
                                    <option value="shop">Dükkan</option>
                                    <option value="land">Arsa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Satılık/Kiralık</label>
                                <select x-model="data.listing_type" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                                    <option value="sale">Satılık</option>
                                    <option value="rent">Kiralık</option>
                                    <option value="daily_rent">Günlük</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Şehir</label>
                                <input type="text" x-model="data.city" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">İlçe</label>
                                <input type="text" x-model="data.district" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Mahalle</label>
                                <input type="text" x-model="data.neighborhood" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Açıklama</label>
                            <textarea x-model="data.description" rows="4" class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-lg text-white text-sm"></textarea>
                        </div>

                        <div x-show="data.photos && data.photos.length > 0">
                            <label class="block text-xs text-gray-500 dark:text-dark-400 mb-2">Bulunan Fotoğraflar (<span x-text="data.photos?.length || 0"></span> adet — arka planda indirilecek)</label>
                            <div class="grid grid-cols-6 gap-2">
                                <template x-for="(p, i) in (data.photos || []).slice(0, 6)" :key="i">
                                    <img :src="p" class="w-full h-12 object-cover rounded-lg" loading="lazy">
                                </template>
                            </div>
                        </div>

                        <p x-show="error" x-cloak class="text-sm text-rose-400" x-text="error"></p>

                        <div class="flex gap-3 pt-2">
                            <button @click="step = 'url'" class="flex-1 px-4 py-2.5 bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-white rounded-xl">Geri</button>
                            <button @click="saveListing()" :disabled="saving"
                                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 disabled:opacity-50 text-white font-medium rounded-xl"
                                    x-text="saving ? 'Kaydediliyor...' : 'İlanı Oluştur'"></button>
                        </div>
                    </div>
                </template>

                {{-- Step 3: Done --}}
                <template x-if="step === 'done'">
                    <div class="text-center py-6">
                        <div class="w-16 h-16 mx-auto bg-emerald-500/20 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">İlan oluşturuldu</h3>
                        <p class="text-gray-500 dark:text-dark-400 text-sm mb-4" x-text="resultMessage"></p>
                        <div class="flex gap-3 justify-center">
                            <button @click="closeModal()" class="px-4 py-2 bg-gray-200 dark:bg-dark-700 text-gray-700 dark:text-white rounded-xl">Kapat</button>
                            <a :href="resultUrl" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl">Düzenle</a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <form method="GET" action="{{ route('admin.listings.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durum</label>
                <select name="status" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tümü</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Taslak</option>
                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Satıldı</option>
                    <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Kiralandı</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">İlan Tipi</label>
                <select name="listing_type" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tümü</option>
                    <option value="sale" {{ request('listing_type') == 'sale' ? 'selected' : '' }}>Satılık</option>
                    <option value="rent" {{ request('listing_type') == 'rent' ? 'selected' : '' }}>Kiralık</option>
                    <option value="daily_rent" {{ request('listing_type') == 'daily_rent' ? 'selected' : '' }}>Günlük Kiralık</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Şehir</label>
                <select name="city" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tümü</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-600 transition-colors">
                    Filtrele
                </button>
            </div>
        </form>
    </div>

    <!-- Listings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($listings as $listing)
        <div class="bg-white dark:bg-dark-800 rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-dark-700 hover:shadow-lg transition-shadow">
            <div class="relative">
                @if($listing->getFirstMediaUrl('photos'))
                    <img src="{{ $listing->getFirstMediaUrl('photos', 'thumb') }}" alt="{{ $listing->title }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 dark:bg-dark-700 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-lg
                        {{ $listing->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $listing->status === 'draft' ? 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400' : '' }}
                        {{ $listing->status === 'sold' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : '' }}
                        {{ $listing->status === 'rented' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}">
                        {{ $listing->status === 'active' ? 'Aktif' : ($listing->status === 'draft' ? 'Taslak' : ($listing->status === 'sold' ? 'Satıldı' : 'Kiralandı')) }}
                    </span>
                </div>
                <div class="absolute top-3 right-3">
                    <span class="px-2 py-1 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 rounded-lg">
                        {{ $listing->listing_type === 'sale' ? 'Satılık' : ($listing->listing_type === 'rent' ? 'Kiralık' : 'Günlük') }}
                    </span>
                </div>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $listing->reference_no }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $listing->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-1">{{ $listing->title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $listing->district }}, {{ $listing->city }}
                </p>
                <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
                    @if($listing->room_count)
                        <span>{{ $listing->room_count }}+{{ $listing->living_room_count ?? 1 }}</span>
                    @endif
                    @if($listing->gross_sqm)
                        <span>{{ $listing->gross_sqm }} m²</span>
                    @endif
                    @if($listing->floor_number)
                        <span>{{ $listing->floor_number }}. Kat</span>
                    @endif
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-dark-700">
                    <span class="text-lg font-bold text-primary-600">₺{{ number_format($listing->price, 0, ',', '.') }}</span>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.listings.show', $listing) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.listings.edit', $listing) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-dark-800 rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 dark:text-dark-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Henüz ilan yok</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">İlk ilanınızı oluşturarak başlayın.</p>
                <a href="{{ route('admin.listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Yeni İlan Oluştur
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($listings->hasPages())
    <div class="flex justify-center">
        {{ $listings->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function listingImport() {
    return {
        open: false,
        step: 'url',
        url: '',
        loading: false,
        saving: false,
        error: '',
        data: {},
        resultMessage: '',
        resultUrl: '',

        closeModal() {
            this.open = false;
            this.step = 'url';
            this.url = '';
            this.data = {};
            this.error = '';
        },

        async fetchPreview() {
            if (!this.url) return;
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch('{{ route("admin.listings.import.preview") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.url })
                });
                const result = await res.json();
                if (result.success) {
                    this.data = result.data || {};
                    this.step = 'preview';
                } else {
                    this.error = result.message || 'İçe aktarma başarısız.';
                }
            } catch (e) {
                this.error = 'Sunucuya ulaşılamadı.';
            } finally {
                this.loading = false;
            }
        },

        async saveListing() {
            this.saving = true;
            this.error = '';
            try {
                const res = await fetch('{{ route("admin.listings.import.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ data: this.data })
                });
                const result = await res.json();
                if (result.success) {
                    this.resultMessage = result.message;
                    this.resultUrl = result.listing.url;
                    this.step = 'done';
                } else {
                    this.error = result.message || 'Kaydetme başarısız.';
                }
            } catch (e) {
                this.error = 'Sunucuya ulaşılamadı.';
            } finally {
                this.saving = false;
            }
        },
    }
}
</script>
@endpush
@endsection
