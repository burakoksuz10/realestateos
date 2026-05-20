@extends('layouts.admin')

@section('title', 'Potansiyel Müşteri Düzenle')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Potansiyel Müşteri Düzenle</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $lead->contact->first_name ?? '' }} {{ $lead->contact->last_name ?? '' }}</p>
        </div>
        <a href="{{ route('admin.leads.show', $lead) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.leads.update', $lead) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact Info (read-only) -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İletişim Bilgileri</h2>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr($lead->contact->first_name ?? 'L', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $lead->contact->first_name ?? '' }} {{ $lead->contact->last_name ?? '' }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $lead->contact->phone ?? $lead->contact->email ?? '-' }}</p>
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
                                    <option value="buy" {{ $lead->interest_type === 'buy' ? 'selected' : '' }}>Satın Alma</option>
                                    <option value="rent" {{ $lead->interest_type === 'rent' ? 'selected' : '' }}>Kiralama</option>
                                    <option value="sell" {{ $lead->interest_type === 'sell' ? 'selected' : '' }}>Satış</option>
                                    <option value="invest" {{ $lead->interest_type === 'invest' ? 'selected' : '' }}>Yatırım</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Gayrimenkul Tipi</label>
                                <select name="property_type" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    @foreach(config('locations.property_types', []) as $val => $label)
                                        <option value="{{ $val }}" {{ old('property_type', $lead->property_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bütçe (Min) ₺</label>
                                <input type="number" name="budget_min" value="{{ old('budget_min', $lead->budget_min) }}"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bütçe (Max) ₺</label>
                                <input type="number" name="budget_max" value="{{ old('budget_max', $lead->budget_max) }}"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Tercih Edilen Bölge</label>
                            <input type="text" name="preferred_locations"
                                value="{{ old('preferred_locations', implode(', ', $lead->preferred_locations ?? [])) }}"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Atakum, İlkadım, Canik">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Skor (0-100)</label>
                            <input type="number" name="score" value="{{ old('score', $lead->score) }}" min="0" max="100"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <!-- İlgilenilen İlanlar -->
                        @php $selectedIds = old('interested_listings', $lead->interestedListings->pluck('id')->toArray()); @endphp
                        <div x-data="listingPicker({{ $listings->toJson() }}, {{ json_encode($selectedIds) }})">
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
                            <textarea name="notes" rows="4" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('notes', $lead->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Durum & Atama</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Durum *</label>
                            <select name="status" required class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['new'=>'Yeni','contacted'=>'İletişime Geçildi','qualified'=>'Nitelikli','proposal'=>'Teklif','negotiation'=>'Müzakere'] as $val => $label)
                                <option value="{{ $val }}" {{ $lead->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Öncelik</label>
                            <select name="priority" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'] as $val => $label)
                                <option value="{{ $val }}" {{ $lead->priority === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Atanan Danışman *</label>
                            <select name="assigned_to" required class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kaynak</label>
                            <select name="source" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Seçiniz</option>
                                <option value="website" {{ $lead->source === 'website' ? 'selected' : '' }}>Website</option>
                                <option value="referral" {{ $lead->source === 'referral' ? 'selected' : '' }}>Referans</option>
                                <option value="social_media" {{ $lead->source === 'social_media' ? 'selected' : '' }}>Sosyal Medya</option>
                                <option value="portal" {{ $lead->source === 'portal' ? 'selected' : '' }}>Emlak Portalı</option>
                                <option value="walk_in" {{ $lead->source === 'walk_in' ? 'selected' : '' }}>Ofis Ziyareti</option>
                                <option value="phone" {{ $lead->source === 'phone' ? 'selected' : '' }}>Telefon</option>
                                <option value="other" {{ $lead->source === 'other' ? 'selected' : '' }}>Diğer</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                        Güncelle
                    </button>
                    <a href="{{ route('admin.leads.show', $lead) }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors text-center">
                        İptal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function listingPicker(listings, initial) {
    return {
        listings, selected: initial.map(Number), search: '', open: false,
        get filtered() {
            if (!this.search) return this.listings.slice(0, 20);
            const q = this.search.toLowerCase();
            return this.listings.filter(l => l.title.toLowerCase().includes(q) || (l.reference_no && l.reference_no.toLowerCase().includes(q))).slice(0, 20);
        },
        toggle(l) { const idx = this.selected.indexOf(l.id); if (idx === -1) this.selected.push(l.id); else this.selected.splice(idx, 1); this.search = ''; },
        byId(id) { return this.listings.find(l => l.id === id); },
        labelOf(id) { const l = this.byId(id); return l ? (l.reference_no ? '#' + l.reference_no + ' ' : '') + l.title : id; },
    }
}
</script>
@endpush
