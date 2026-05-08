@extends('layouts.admin')

@section('title', 'AI Değerleme')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Gayrimenkul Değerleme</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Yapay zeka destekli anlık gayrimenkul değerlemesi</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Valuation Form -->
        <div class="lg:col-span-1 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Gayrimenkul Bilgileri</h2>
            <form action="#" method="POST" class="space-y-4" onsubmit="event.preventDefault(); alert('Değerleme hesaplanıyor...');">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Gayrimenkul Tipi</label>
                    <select name="property_type" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Seçiniz</option>
                        @foreach(config('locations.property_types', []) as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Konum</label>
                    <input type="text" name="location" placeholder="Adres veya mahalle girin" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Alan (m²)</label>
                        <input type="number" name="size" placeholder="120" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Oda</label>
                        <select name="rooms" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="1">1+0</option>
                            <option value="2">1+1</option>
                            <option value="3" selected>2+1</option>
                            <option value="4">3+1</option>
                            <option value="5">4+1</option>
                            <option value="6">5+</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Bina Yaşı</label>
                    <input type="number" name="building_age" placeholder="5" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-dark-300 mb-2">Kat</label>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" name="floor" placeholder="3" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <input type="number" name="total_floors" placeholder="10" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-medium rounded-xl transition-all flex items-center justify-center shadow-lg shadow-emerald-600/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Değerleme Hesapla
                    </button>
                </div>
            </form>
        </div>

        <!-- Valuation Results -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Estimated Value -->
            <div class="bg-emerald-50 dark:bg-gradient-to-br dark:from-emerald-900/30 dark:to-dark-900 border border-emerald-200 dark:border-emerald-500/30 rounded-2xl p-6">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tahmini Değer</h3>
                        <p class="text-gray-500 dark:text-dark-400 text-sm">AI destekli piyasa analizi</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-white/70 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">Minimum</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">₺2,850,000</p>
                    </div>
                    <div class="text-center p-4 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl border border-emerald-300 dark:border-emerald-500/30">
                        <p class="text-emerald-700 dark:text-emerald-400 text-sm mb-1">Tahmin</p>
                        <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">₺3,250,000</p>
                    </div>
                    <div class="text-center p-4 bg-white/70 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">Maksimum</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">₺3,650,000</p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-white/70 dark:bg-dark-800/50 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Güven Seviyesi</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">87%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-dark-700 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: 87%"></div>
                    </div>
                </div>
            </div>

            <!-- Comparable Properties -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Emsal Gayrimenkuller</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-dark-700 rounded-lg"></div>
                            <div>
                                <p class="text-gray-900 dark:text-white font-medium">Benzer Daire - Kadıköy</p>
                                <p class="text-gray-500 dark:text-dark-400 text-sm">115 m² • 3+1 • 4 yıllık</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-900 dark:text-white font-semibold">₺3,100,000</p>
                            <p class="text-gray-500 dark:text-dark-400 text-sm">2 hafta önce satıldı</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-dark-700 rounded-lg"></div>
                            <div>
                                <p class="text-gray-900 dark:text-white font-medium">Benzer Daire - Kadıköy</p>
                                <p class="text-gray-500 dark:text-dark-400 text-sm">125 m² • 3+1 • 6 yıllık</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-900 dark:text-white font-semibold">₺3,350,000</p>
                            <p class="text-gray-500 dark:text-dark-400 text-sm">1 ay önce satıldı</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-dark-700 rounded-lg"></div>
                            <div>
                                <p class="text-gray-900 dark:text-white font-medium">Benzer Daire - Kadıköy</p>
                                <p class="text-gray-500 dark:text-dark-400 text-sm">110 m² • 2+1 • 3 yıllık</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-900 dark:text-white font-semibold">₺2,950,000</p>
                            <p class="text-gray-500 dark:text-dark-400 text-sm">3 hafta önce satıldı</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="bg-violet-50 dark:bg-gradient-to-br dark:from-violet-900/30 dark:to-dark-900 border border-violet-200 dark:border-violet-500/30 rounded-2xl p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-violet-100 dark:bg-violet-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Öngörüleri</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <p class="text-gray-700 dark:text-dark-300 text-sm">Bu bölgedeki gayrimenkul değerleri son 6 ayda %12 arttı</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-700 dark:text-dark-300 text-sm">Benzer gayrimenkuller için ortalama piyasada kalma süresi: 45 gün</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-gray-700 dark:text-dark-300 text-sm">Yakında açılacak yeni metro hattı değeri %5-8 artırabilir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
