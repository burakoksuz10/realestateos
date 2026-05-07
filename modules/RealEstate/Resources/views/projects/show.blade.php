@extends('layouts.admin')

@section('title', $project->name . ' - Proje Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
            <p class="text-dark-400 mt-1">{{ $project->city }}{{ $project->district ? ', ' . $project->district : '' }}
                @if($project->developer) · {{ $project->developer }} @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.projects.toggle-featured', $project) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 {{ $project->is_featured ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30' : 'bg-dark-700 hover:bg-dark-600 text-white' }} rounded-xl transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="{{ $project->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    {{ $project->is_featured ? 'Öne Çıkarıldı' : 'Öne Çıkar' }}
                </button>
            </form>
            <a href="{{ route('admin.projects.edit', $project) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <p class="text-dark-400 text-sm">Toplam Daire</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $project->total_units ?? '-' }}</p>
        </div>
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <p class="text-dark-400 text-sm">Mevcut Daire</p>
            <p class="text-2xl font-bold text-green-400 mt-1">{{ $project->available_units ?? '-' }}</p>
        </div>
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <p class="text-dark-400 text-sm">Min. Fiyat</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $project->min_price ? '₺' . number_format($project->min_price, 0, ',', '.') : '-' }}</p>
        </div>
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <p class="text-dark-400 text-sm">Maks. Fiyat</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $project->max_price ? '₺' . number_format($project->max_price, 0, ',', '.') : '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if($project->description)
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-3">Açıklama</h2>
                <p class="text-dark-300 text-sm leading-relaxed">{{ $project->description }}</p>
            </div>
            @endif

            <!-- Listings -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">İlanlar</h2>
                    <a href="{{ route('admin.listings.create') }}?project_id={{ $project->id }}" class="text-primary-400 hover:text-primary-300 text-sm">+ Yeni İlan</a>
                </div>
                @forelse($project->listings as $listing)
                <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                    <div>
                        <a href="{{ route('admin.listings.show', $listing) }}" class="text-white text-sm font-medium hover:text-primary-400">{{ $listing->title }}</a>
                        <p class="text-dark-400 text-xs">{{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white text-sm font-semibold">₺{{ number_format($listing->price ?? 0, 0, ',', '.') }}</p>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $listing->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                            {{ ucfirst($listing->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-dark-400 text-sm">Bu projeye ait ilan yok.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Proje Bilgileri</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Durum</span>
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $project->status === 'completed' ? 'bg-green-500/20 text-green-400' :
                               ($project->status === 'under_construction' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-blue-500/20 text-blue-400') }}">
                            {{ ['planning'=>'Planlama','under_construction'=>'İnşaat','completed'=>'Tamamlandı'][$project->status] ?? $project->status }}
                        </span>
                    </div>
                    @if($project->developer)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Geliştirici</span>
                        <span class="text-white text-sm">{{ $project->developer }}</span>
                    </div>
                    @endif
                    @if($project->delivery_date ?? $project->estimated_completion)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Teslim Tarihi</span>
                        <span class="text-white text-sm">{{ \Carbon\Carbon::parse($project->delivery_date ?? $project->estimated_completion)->format('d.m.Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Konum</span>
                        <span class="text-white text-sm">{{ $project->city }}, {{ $project->district }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Aktif İlan</span>
                        <span class="text-white font-semibold">{{ $project->listings->count() }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.projects.edit', $project) }}" class="block w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-center">Düzenle</a>
                <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Bu projeyi silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
