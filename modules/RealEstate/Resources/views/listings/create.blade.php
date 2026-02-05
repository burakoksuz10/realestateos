@extends('layouts.admin')

@section('title', 'Yeni İlan Oluştur')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Yeni İlan Oluştur</h1>
            <p class="text-dark-400 mt-1">Yeni bir gayrimenkul ilanı ekleyin</p>
        </div>
        <a href="{{ route('admin.listings.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Geri Dön
        </a>
    </div>

    <form action="{{ route('admin.listings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Temel Bilgiler</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">İlan Başlığı *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Örn: Kadıköy'de Deniz Manzaralı 3+1 Daire">
                            @error('title')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlan Tipi *</label>
                                <select name="listing_type" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="sale" {{ old('listing_type') == 'sale' ? 'selected' : '' }}>Satılık</option>
                                    <option value="rent" {{ old('listing_type') == 'rent' ? 'selected' : '' }}>Kiralık</option>
                                    <option value="daily_rent" {{ old('listing_type') == 'daily_rent' ? 'selected' : '' }}>Günlük Kiralık</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Gayrimenkul Tipi *</label>
                                <select name="property_type" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Daire</option>
                                    <option value="house" {{ old('property_type') == 'house' ? 'selected' : '' }}>Müstakil Ev</option>
                                    <option value="villa" {{ old('property_type') == 'villa' ? 'selected' : '' }}>Villa</option>
                                    <option value="land" {{ old('property_type') == 'land' ? 'selected' : '' }}>Arsa</option>
                                    <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Ticari</option>
                                    <option value="office" {{ old('property_type') == 'office' ? 'selected' : '' }}>Ofis</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="5"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="İlan hakkında detaylı açıklama yazın...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Konum Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İl *</label>
                                <input type="text" name="city" value="{{ old('city') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="İstanbul">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlçe *</label>
                                <input type="text" name="district" value="{{ old('district') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="Kadıköy">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Mahalle</label>
                            <input type="text" name="neighborhood" value="{{ old('neighborhood') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Caferağa">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Adres</label>
                            <textarea name="address" rows="2"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Tam adres...">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Gayrimenkul Özellikleri</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Brüt m²</label>
                            <input type="number" name="gross_area" value="{{ old('gross_area') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="120">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Net m²</label>
                            <input type="number" name="net_area" value="{{ old('net_area') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Oda Sayısı</label>
                            <select name="rooms" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Seçiniz</option>
                                <option value="1+0">1+0</option>
                                <option value="1+1">1+1</option>
                                <option value="2+1">2+1</option>
                                <option value="3+1">3+1</option>
                                <option value="4+1">4+1</option>
                                <option value="5+1">5+1</option>
                                <option value="6+">6+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Banyo Sayısı</label>
                            <input type="number" name="bathrooms" value="{{ old('bathrooms') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Bina Yaşı</label>
                            <input type="number" name="building_age" value="{{ old('building_age') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Bulunduğu Kat</label>
                            <input type="number" name="floor" value="{{ old('floor') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Toplam Kat</label>
                            <input type="number" name="total_floors" value="{{ old('total_floors') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Isıtma</label>
                            <select name="heating" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Seçiniz</option>
                                <option value="central">Merkezi</option>
                                <option value="individual">Bireysel</option>
                                <option value="floor">Yerden Isıtma</option>
                                <option value="ac">Klima</option>
                                <option value="stove">Soba</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Fotoğraflar</h2>
                    
                    <div class="border-2 border-dashed border-dark-600 rounded-xl p-8 text-center">
                        <svg class="w-12 h-12 text-dark-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-dark-400 mb-2">Fotoğrafları sürükleyip bırakın veya</p>
                        <label class="cursor-pointer">
                            <span class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors inline-block">Dosya Seçin</span>
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden">
                        </label>
                        <p class="text-dark-500 text-sm mt-2">PNG, JPG, WEBP - Maksimum 10MB</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Price -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Fiyat Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Fiyat *</label>
                            <div class="relative">
                                <input type="number" name="price" value="{{ old('price') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="1500000">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Para Birimi</label>
                            <select name="currency" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="TRY">₺ TRY</option>
                                <option value="USD">$ USD</option>
                                <option value="EUR">€ EUR</option>
                                <option value="GBP">£ GBP</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Durum</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">İlan Durumu</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="draft">Taslak</option>
                                <option value="active">Aktif</option>
                                <option value="pending">Onay Bekliyor</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <div class="space-y-3">
                        <button type="submit" class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            İlanı Kaydet
                        </button>
                        <button type="button" onclick="history.back()" class="w-full px-6 py-3 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors">
                            İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
