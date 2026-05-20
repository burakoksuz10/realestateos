@extends('layouts.admin')

@section('title', 'Yeni Potansiyel Müşteri')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Potansiyel Müşteri</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Yeni bir potansiyel müşteri kaydı oluşturun</p>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Geri Dön
        </a>
    </div>

    <form action="{{ route('admin.leads.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact Info -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">İletişim Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Ad *</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="Ad">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Soyad *</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="Soyad">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">E-posta</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="ornek@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Telefon *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="+90 5XX XXX XX XX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Details -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Talep Detayları</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlgi Alanı</label>
                                <select name="interest_type" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="buy">Satın Alma</option>
                                    <option value="rent">Kiralama</option>
                                    <option value="sell">Satış</option>
                                    <option value="invest">Yatırım</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Gayrimenkul Tipi</label>
                                <select name="property_type" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    @foreach(config('locations.property_types', []) as $val => $label)
                                        <option value="{{ $val }}" {{ old('property_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bütçe (Min)</label>
                                <input type="number" name="budget_min" value="{{ old('budget_min') }}"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="500000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bütçe (Max)</label>
                                <input type="number" name="budget_max" value="{{ old('budget_max') }}"
                                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="1500000">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Tercih Edilen Bölge</label>
                            <input type="text" name="preferred_locations" value="{{ old('preferred_locations') }}"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Atakum, İlkadım, Canik">
                        </div>

                        <!-- İlgilenilen İlanlar -->
                        <div x-data="listingPicker({{ $listings->toJson() }}, {{ json_encode(old('interested_listings', [])) }})">
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlgilendiği İlanlar</label>
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" x-model="search" @focus="open=true" @click.outside="open=false"
                                    placeholder="İlan ara (başlık veya ref. no)..."
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                                <div x-show="open && filtered.length" x-cloak
                                    class="absolute z-20 w-full mt-1 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                                    <template x-for="l in filtered" :key="l.id">
                                        <button type="button" @click="toggle(l)"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors text-left">
                                            <div :class="selected.includes(l.id) ? 'bg-primary-600 border-primary-600' : 'border-gray-300 dark:border-dark-600'"
                                                class="w-4 h-4 rounded border-2 flex-shrink-0 flex items-center justify-center transition-colors">
                                                <svg x-show="selected.includes(l.id)" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="l.title"></p>
                                                <p class="text-xs text-gray-400" x-text="'#' + l.reference_no + (l.price ? ' · ₺' + Number(l.price).toLocaleString('tr') : '')"></p>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <!-- Selected chips -->
                            <div class="flex flex-wrap gap-2 mt-2" x-show="selected.length">
                                <template x-for="id in selected" :key="id">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 text-xs rounded-lg border border-primary-200 dark:border-primary-500/30">
                                        <span x-text="labelOf(id)"></span>
                                        <button type="button" @click="toggle(byId(id))" class="hover:text-red-500 transition-colors">×</button>
                                        <input type="hidden" name="interested_listings[]" :value="id">
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Notlar</label>
                            <textarea name="notes" rows="4"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ek notlar...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Source -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Kaynak Bilgisi</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kaynak</label>
                            <select name="source" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Seçiniz</option>
                                <option value="website">Website</option>
                                <option value="referral">Referans</option>
                                <option value="social_media">Sosyal Medya</option>
                                <option value="portal">Emlak Portalı</option>
                                <option value="walk_in">Ofis Ziyareti</option>
                                <option value="phone">Telefon</option>
                                <option value="other">Diğer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Öncelik</label>
                            <select name="priority" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="low">Düşük</option>
                                <option value="medium" selected>Orta</option>
                                <option value="high">Yüksek</option>
                                <option value="urgent">Acil</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status & Assignment -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Durum & Atama</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Durum</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="new">Yeni</option>
                                <option value="contacted">İletişime Geçildi</option>
                                <option value="qualified">Nitelikli</option>
                                <option value="proposal">Teklif Verildi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Atanan Danışman *</label>
                            <select name="assigned_to" required class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Seçiniz</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ old('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <div class="space-y-3">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Kaydı Oluştur
                        </button>
                        <button type="button" onclick="history.back()" class="w-full px-6 py-3 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">
                            İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function listingPicker(listings, initial) {
    return {
        listings,
        selected: initial.map(Number),
        search: '',
        open: false,
        get filtered() {
            if (!this.search) return this.listings.slice(0, 20);
            const q = this.search.toLowerCase();
            return this.listings.filter(l =>
                l.title.toLowerCase().includes(q) ||
                (l.reference_no && l.reference_no.toLowerCase().includes(q))
            ).slice(0, 20);
        },
        toggle(l) {
            const idx = this.selected.indexOf(l.id);
            if (idx === -1) this.selected.push(l.id);
            else this.selected.splice(idx, 1);
            this.search = '';
        },
        byId(id) { return this.listings.find(l => l.id === id); },
        labelOf(id) {
            const l = this.byId(id);
            return l ? (l.reference_no ? '#' + l.reference_no + ' ' : '') + l.title : id;
        },
    }
}
</script>
@endpush
@endsection
