@extends('layouts.admin')

@section('title', 'İlanlar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">İlanlar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tüm emlak ilanlarını yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.listings.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni İlan
            </a>
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
@endsection
