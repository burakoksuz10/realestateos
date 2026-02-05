@extends('layouts.admin')

@section('title', 'Yeni Potansiyel Müşteri')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Yeni Potansiyel Müşteri</h1>
            <p class="text-dark-400 mt-1">Yeni bir potansiyel müşteri kaydı oluşturun</p>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
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
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">İletişim Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Ad *</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="Ad">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Soyad *</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="Soyad">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">E-posta</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="ornek@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Telefon *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="+90 5XX XXX XX XX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Details -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Talep Detayları</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlgi Alanı</label>
                                <select name="interest_type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="buy">Satın Alma</option>
                                    <option value="rent">Kiralama</option>
                                    <option value="sell">Satış</option>
                                    <option value="invest">Yatırım</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Gayrimenkul Tipi</label>
                                <select name="property_type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="apartment">Daire</option>
                                    <option value="house">Müstakil Ev</option>
                                    <option value="villa">Villa</option>
                                    <option value="land">Arsa</option>
                                    <option value="commercial">Ticari</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Bütçe (Min)</label>
                                <input type="number" name="budget_min" value="{{ old('budget_min') }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="500000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Bütçe (Max)</label>
                                <input type="number" name="budget_max" value="{{ old('budget_max') }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    placeholder="1500000">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Tercih Edilen Bölge</label>
                            <input type="text" name="preferred_location" value="{{ old('preferred_location') }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Kadıköy, Beşiktaş, Üsküdar">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Notlar</label>
                            <textarea name="notes" rows="4"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Ek notlar...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Source -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Kaynak Bilgisi</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Kaynak</label>
                            <select name="source" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
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
                            <label class="block text-sm font-medium text-dark-300 mb-2">Öncelik</label>
                            <select name="priority" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="low">Düşük</option>
                                <option value="medium" selected>Orta</option>
                                <option value="high">Yüksek</option>
                                <option value="urgent">Acil</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Durum</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Lead Durumu</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="new">Yeni</option>
                                <option value="contacted">İletişime Geçildi</option>
                                <option value="qualified">Nitelikli</option>
                                <option value="proposal">Teklif Verildi</option>
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
                            Kaydı Oluştur
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
