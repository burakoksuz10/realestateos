@extends('layouts.admin')
@section('title', 'AI Değerleme - İlan Detayı')
@section('content')
<div class="space-y-6" x-data="valuation()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Değerleme Raporu</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $listing->title ?? 'İlan #' . ($listing->id ?? '') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai.valuation.report', $listing) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                PDF İndir
            </a>
            <a href="{{ route('admin.ai.valuation.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Valuation Result -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Değerleme Sonucu</h2>
                    <form action="{{ route('admin.ai.valuation.generate', $listing) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Yeniden Hesapla
                        </button>
                    </form>
                </div>

                @if(isset($valuation))
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 dark:text-dark-400 mb-1">Minimum</p>
                        <p class="text-lg font-bold text-white">{{ number_format($valuation['min_value'] ?? 0, 0, ',', '.') }} ₺</p>
                    </div>
                    <div class="bg-primary-600/20 border border-primary-500/30 rounded-xl p-4 text-center">
                        <p class="text-xs text-primary-400 mb-1">Tahmini Değer</p>
                        <p class="text-2xl font-bold text-primary-300">{{ number_format($valuation['estimated_value'] ?? 0, 0, ',', '.') }} ₺</p>
                    </div>
                    <div class="bg-gray-100 dark:bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 dark:text-dark-400 mb-1">Maksimum</p>
                        <p class="text-lg font-bold text-white">{{ number_format($valuation['max_value'] ?? 0, 0, ',', '.') }} ₺</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-dark-400">m² Birim Fiyat</span>
                        <span class="text-white font-medium">{{ number_format($valuation['price_per_sqm'] ?? 0, 0, ',', '.') }} ₺/m²</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-dark-400">Güven Skoru</span>
                        <div class="flex items-center gap-2">
                            <div class="w-24 h-1.5 bg-gray-200 dark:bg-dark-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $valuation['confidence_score'] ?? 0 }}%"></div>
                            </div>
                            <span class="text-white text-xs">{{ $valuation['confidence_score'] ?? 0 }}%</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-dark-400">Son Güncelleme</span>
                        <span class="text-white">{{ isset($valuation['updated_at']) ? \Carbon\Carbon::parse($valuation['updated_at'])->format('d.m.Y H:i') : 'Bilinmiyor' }}</span>
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz değerleme yapılmamış.</p>
                    <form action="{{ route('admin.ai.valuation.generate', $listing) }}" method="POST" class="mt-3 inline-block">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors text-sm">Değerleme Başlat</button>
                    </form>
                </div>
                @endif
            </div>

            <!-- AI Analysis -->
            @if(isset($valuation['analysis']))
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Analizi</h2>
                <div class="prose prose-invert prose-sm max-w-none">
                    <p class="text-gray-600 dark:text-dark-300 leading-relaxed whitespace-pre-line">{{ $valuation['analysis'] }}</p>
                </div>
            </div>
            @endif

            <!-- Comparable Properties -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Karşılaştırmalı İlanlar</h2>
                @if(isset($comparables) && count($comparables) > 0)
                <div class="space-y-3">
                    @foreach($comparables as $comparable)
                    <div class="flex items-center justify-between p-3 bg-gray-100 dark:bg-dark-800 rounded-xl">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $comparable['title'] ?? 'İlan' }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $comparable['city'] ?? '' }} • {{ $comparable['area'] ?? '' }} m²</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold text-sm">{{ number_format($comparable['price'] ?? 0, 0, ',', '.') }} ₺</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">{{ number_format($comparable['price_per_sqm'] ?? 0, 0, ',', '.') }} ₺/m²</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 dark:text-dark-400 text-sm">Karşılaştırmalı ilan bulunamadı.</p>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <!-- Property Info -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">İlan Bilgileri</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Tip</span><span class="text-white">{{ $listing->listing_type ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Kategori</span><span class="text-white">{{ $listing->property_type ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Alan</span><span class="text-white">{{ $listing->area ?? '-' }} m²</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Oda</span><span class="text-white">{{ $listing->room_count ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Şehir</span><span class="text-white">{{ $listing->city ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">İlan Fiyatı</span><span class="text-white font-semibold">{{ $listing->price ? number_format($listing->price, 0, ',', '.') . ' ₺' : '-' }}</span></div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">İşlemler</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.listings.show', $listing) }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white text-center rounded-xl transition-colors text-sm">İlanı Görüntüle</a>
                    <a href="{{ route('admin.ai.valuation.index') }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white text-center rounded-xl transition-colors text-sm">Tüm Değerlemeler</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
