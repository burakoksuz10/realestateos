@extends('layouts.admin')

@section('title', 'İlan Düzenle - ' . $listing->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İlan Düzenle</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $listing->reference_no }} - {{ $listing->title }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.listings.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-dark-300 rounded-lg hover:bg-dark-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Geri
            </a>
            <a href="{{ route('admin.listings.show', $listing) }}" class="px-4 py-2 bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-dark-300 rounded-lg hover:bg-dark-600 transition-colors">
                <i class="fas fa-eye mr-2"></i>Görüntüle
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.listings.update', $listing) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Temel Bilgiler -->
        <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Temel Bilgiler</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlan Başlığı *</label>
                    <input type="text" name="title" value="{{ old('title', $listing->title) }}" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlan Tipi *</label>
                    <select name="listing_type" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="sale" {{ old('listing_type', $listing->listing_type) == 'sale' ? 'selected' : '' }}>Satılık</option>
                        <option value="rent" {{ old('listing_type', $listing->listing_type) == 'rent' ? 'selected' : '' }}>Kiralık</option>
                        <option value="daily_rent" {{ old('listing_type', $listing->listing_type) == 'daily_rent' ? 'selected' : '' }}>Günlük Kiralık</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Gayrimenkul Tipi *</label>
                    <select name="type" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Seçiniz</option>
                        @foreach(config('locations.property_types', []) as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $listing->type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kategori *</label>
                    <select name="category" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="residential" {{ old('category', $listing->category) == 'residential' ? 'selected' : '' }}>Konut</option>
                        <option value="commercial" {{ old('category', $listing->category) == 'commercial' ? 'selected' : '' }}>Ticari</option>
                        <option value="land" {{ old('category', $listing->category) == 'land' ? 'selected' : '' }}>Arsa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Durum</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($listing->status == 'active') bg-green-500/20 text-green-400
                        @elseif($listing->status == 'draft') bg-yellow-500/20 text-yellow-400
                        @elseif($listing->status == 'sold') bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400
                        @else bg-gray-500/20 text-gray-400
                        @endif">
                        {{ ucfirst($listing->status) }}
                    </span>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Açıklama</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $listing->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Fiyat Bilgileri -->
        <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fiyat Bilgileri</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Fiyat *</label>
                    <input type="number" name="price" value="{{ old('price', $listing->price) }}" required min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Para Birimi *</label>
                    <select name="price_currency" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="TRY" {{ old('price_currency', $listing->price_currency) == 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                        <option value="USD" {{ old('price_currency', $listing->price_currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ old('price_currency', $listing->price_currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ old('price_currency', $listing->price_currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Danışman *</label>
                    <select name="agent_id" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id', $listing->agent_id) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Konum Bilgileri -->
        <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konum Bilgileri</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php $locationCities = config('locations.cities', []); @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İl *</label>
                    <select name="city" id="citySelect" required onchange="updateDistricts(this.value)"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Seçiniz</option>
                        @foreach(array_keys($locationCities) as $c)
                            <option value="{{ $c }}" {{ old('city', $listing->city) === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlçe *</label>
                    <select name="district" id="districtSelect" required
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Seçiniz</option>
                        @php $selCity = old('city', $listing->city); @endphp
                        @if($selCity && isset($locationCities[$selCity]))
                            @foreach($locationCities[$selCity] as $d)
                                <option value="{{ $d }}" {{ old('district', $listing->district) === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <script>
                var locationData = @json($locationCities);
                function updateDistricts(city) {
                    var sel = document.getElementById('districtSelect');
                    sel.innerHTML = '<option value="">Seçiniz</option>';
                    (locationData[city] || []).forEach(function(d) {
                        sel.innerHTML += '<option value="' + d + '">' + d + '</option>';
                    });
                }
                </script>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Mahalle</label>
                    <input type="text" name="neighborhood" value="{{ old('neighborhood', $listing->neighborhood) }}"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Adres</label>
                    <textarea name="address" rows="2"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('address', $listing->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Özellikler -->
        <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Özellikler</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Brüt m²</label>
                    <input type="number" name="gross_sqm" value="{{ old('gross_sqm', $listing->gross_sqm) }}" min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Net m²</label>
                    <input type="number" name="net_sqm" value="{{ old('net_sqm', $listing->net_sqm) }}" min="0" step="0.01"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Oda Sayısı</label>
                    <input type="number" name="room_count" value="{{ old('room_count', $listing->room_count) }}" min="0"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Salon Sayısı</label>
                    <input type="number" name="living_room_count" value="{{ old('living_room_count', $listing->living_room_count) }}" min="0"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Banyo Sayısı</label>
                    <input type="number" name="bathroom_count" value="{{ old('bathroom_count', $listing->bathroom_count) }}" min="0"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bulunduğu Kat</label>
                    <input type="number" name="floor_number" value="{{ old('floor_number', $listing->floor_number) }}"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Toplam Kat</label>
                    <input type="number" name="total_floors" value="{{ old('total_floors', $listing->total_floors) }}" min="1"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Bina Yaşı</label>
                    <input type="number" name="building_age" value="{{ old('building_age', $listing->building_age) }}" min="0"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Isıtma Tipi</label>
                    <select name="heating_type"
                        class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Seçiniz</option>
                        <option value="central" {{ old('heating_type', $listing->heating_type) == 'central' ? 'selected' : '' }}>Merkezi</option>
                        <option value="individual" {{ old('heating_type', $listing->heating_type) == 'individual' ? 'selected' : '' }}>Bireysel</option>
                        <option value="floor" {{ old('heating_type', $listing->heating_type) == 'floor' ? 'selected' : '' }}>Yerden Isıtma</option>
                        <option value="air_conditioner" {{ old('heating_type', $listing->heating_type) == 'air_conditioner' ? 'selected' : '' }}>Klima</option>
                        <option value="stove" {{ old('heating_type', $listing->heating_type) == 'stove' ? 'selected' : '' }}>Soba</option>
                        <option value="none" {{ old('heating_type', $listing->heating_type) == 'none' ? 'selected' : '' }}>Yok</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_furnished" value="1" {{ old('is_furnished', $listing->is_furnished) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-dark-600 bg-gray-200 dark:bg-dark-700 text-primary-500 focus:ring-primary-500">
                        <span class="ml-2 text-gray-600 dark:text-dark-300">Eşyalı</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Versiyon Notu -->
        <div class="bg-gray-100 dark:bg-dark-800 rounded-xl border border-gray-200 dark:border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Değişiklik Notu</h2>
            <input type="text" name="version_reason" placeholder="Bu değişikliğin nedenini yazın (opsiyonel)"
                class="w-full px-4 py-2.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.listings.index') }}" class="px-6 py-2.5 bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-dark-300 rounded-lg hover:bg-dark-600 transition-colors">
                İptal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-lg hover:from-sky-500 hover:to-blue-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Kaydet
            </button>
        </div>
    </form>
</div>
@endsection
