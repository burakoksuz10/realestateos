@extends('layouts.admin')

@section('title', $listing->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-white">{{ $listing->title }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($listing->status == 'active') bg-green-500/20 text-green-400
                    @elseif($listing->status == 'draft') bg-yellow-500/20 text-yellow-400
                    @elseif($listing->status == 'sold') bg-blue-500/20 text-blue-400
                    @else bg-gray-500/20 text-gray-400
                    @endif">
                    {{ ucfirst($listing->status) }}
                </span>
            </div>
            <p class="text-dark-400 mt-1">{{ $listing->reference_no }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.listings.index') }}" class="px-4 py-2 bg-dark-700 text-dark-300 rounded-lg hover:bg-dark-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Geri
            </a>
            <a href="{{ route('admin.listings.edit', $listing) }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Düzenle
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Fotoğraflar -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fotoğraflar</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse($listing->getMedia('photos') as $photo)
                        <div class="aspect-video bg-dark-700 rounded-lg overflow-hidden">
                            <img src="{{ $photo->getUrl() }}" alt="Fotoğraf" class="w-full h-full object-cover">
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-dark-400">
                            <i class="fas fa-image text-4xl mb-2"></i>
                            <p>Henüz fotoğraf eklenmemiş</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Açıklama -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Açıklama</h2>
                <div class="prose prose-invert max-w-none">
                    {!! nl2br(e($listing->description ?? 'Açıklama eklenmemiş')) !!}
                </div>
            </div>

            <!-- Özellikler -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Özellikler</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-ruler-combined text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Brüt m²</p>
                        <p class="text-white font-semibold">{{ $listing->gross_sqm ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-expand text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Net m²</p>
                        <p class="text-white font-semibold">{{ $listing->net_sqm ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-door-open text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Oda</p>
                        <p class="text-white font-semibold">{{ $listing->room_count ?? '-' }}+{{ $listing->living_room_count ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-bath text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Banyo</p>
                        <p class="text-white font-semibold">{{ $listing->bathroom_count ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-building text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Kat</p>
                        <p class="text-white font-semibold">{{ $listing->floor_number ?? '-' }}/{{ $listing->total_floors ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-calendar text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Bina Yaşı</p>
                        <p class="text-white font-semibold">{{ $listing->building_age ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-fire text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Isıtma</p>
                        <p class="text-white font-semibold">{{ $listing->heating_type ?? '-' }}</p>
                    </div>
                    <div class="bg-dark-700 rounded-lg p-4 text-center">
                        <i class="fas fa-couch text-primary-400 text-xl mb-2"></i>
                        <p class="text-dark-400 text-sm">Eşya</p>
                        <p class="text-white font-semibold">{{ $listing->is_furnished ? 'Eşyalı' : 'Boş' }}</p>
                    </div>
                </div>
            </div>

            <!-- Konum -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Konum</h2>
                <div class="space-y-2">
                    <p class="text-dark-300"><i class="fas fa-map-marker-alt text-primary-400 mr-2"></i>{{ $listing->city }}, {{ $listing->district }}</p>
                    @if($listing->neighborhood)
                        <p class="text-dark-400 ml-6">{{ $listing->neighborhood }}</p>
                    @endif
                    @if($listing->address)
                        <p class="text-dark-400 ml-6">{{ $listing->address }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Fiyat -->
            <div class="bg-gradient-to-br from-primary-900/50 to-dark-800 rounded-xl border border-primary-500/30 p-6">
                <p class="text-dark-400 text-sm mb-1">Fiyat</p>
                <p class="text-3xl font-bold text-white">
                    {{ number_format($listing->price, 0, ',', '.') }}
                    <span class="text-lg">{{ $listing->price_currency }}</span>
                </p>
                <p class="text-dark-400 text-sm mt-2">
                    {{ $listing->listing_type == 'sale' ? 'Satılık' : ($listing->listing_type == 'rent' ? 'Kiralık' : 'Günlük Kiralık') }}
                </p>
            </div>

            <!-- Danışman -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Danışman</h3>
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ substr($listing->agent->name ?? 'N', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $listing->agent->name ?? 'Atanmamış' }}</p>
                        <p class="text-dark-400 text-sm">{{ $listing->agent->email ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">İstatistikler</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Görüntülenme</span>
                        <span class="text-white font-medium">{{ $listing->view_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Favorilere Eklenme</span>
                        <span class="text-white font-medium">{{ $listing->favorite_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Talep Sayısı</span>
                        <span class="text-white font-medium">{{ $listing->inquiry_count ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Aksiyonlar -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Aksiyonlar</h3>
                <div class="space-y-2">
                    @if($listing->status == 'draft')
                        <form action="{{ route('admin.listings.publish', $listing) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-globe mr-2"></i>Yayınla
                            </button>
                        </form>
                    @elseif($listing->status == 'active')
                        <form action="{{ route('admin.listings.unpublish', $listing) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                                <i class="fas fa-eye-slash mr-2"></i>Yayından Kaldır
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.listings.duplicate', $listing) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-dark-700 text-dark-300 rounded-lg hover:bg-dark-600 transition-colors">
                            <i class="fas fa-copy mr-2"></i>Kopyala
                        </button>
                    </form>

                    <form action="{{ route('admin.listings.destroy', $listing) }}" method="POST" onsubmit="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/30 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Sil
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tarihler -->
            <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Tarihler</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Oluşturulma</span>
                        <span class="text-white">{{ $listing->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Güncelleme</span>
                        <span class="text-white">{{ $listing->updated_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($listing->published_at)
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Yayınlanma</span>
                            <span class="text-white">{{ $listing->published_at->format('d.m.Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Benzer İlanlar -->
    @if(isset($similarListings) && $similarListings->count() > 0)
        <div class="bg-dark-800 rounded-xl border border-dark-700 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Benzer İlanlar</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($similarListings as $similar)
                    <a href="{{ route('admin.listings.show', $similar) }}" class="bg-dark-700 rounded-lg p-4 hover:bg-dark-600 transition-colors">
                        <p class="text-white font-medium truncate">{{ $similar->title }}</p>
                        <p class="text-primary-400 font-semibold">{{ number_format($similar->price, 0, ',', '.') }} {{ $similar->price_currency }}</p>
                        <p class="text-dark-400 text-sm">{{ $similar->city }}, {{ $similar->district }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
