@extends('layouts.admin')

@section('title', 'Portal Senkronizasyonu')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Portal Senkronizasyonu</h1>
        <p class="text-dark-400 mt-1">Sahibinden, Hepsiemlak, EmlakJet, Zingat entegrasyonları</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($portals as $key => $name)
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <p class="text-white font-semibold text-sm mb-1">{{ $name }}</p>
            <span class="px-2 py-0.5 text-xs rounded-full bg-dark-700 text-dark-400">Demo Mod</span>
        </div>
        @endforeach
    </div>

    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Aktif İlanlar</h2>
        </div>
        <div class="divide-y divide-dark-700/50">
            @forelse($listings as $listing)
            <div class="flex items-center justify-between py-3">
                <div>
                    <a href="{{ route('admin.listings.show', $listing) }}" class="text-white text-sm font-medium hover:text-primary-400">{{ $listing->title }}</a>
                    <p class="text-dark-400 text-xs">{{ $listing->city }}{{ $listing->district ? ', ' . $listing->district : '' }}</p>
                </div>
                <span class="text-white text-sm">₺{{ number_format($listing->price ?? 0, 0, ',', '.') }}</span>
            </div>
            @empty
            <p class="text-dark-400 text-sm py-4">Aktif ilan bulunamadı.</p>
            @endforelse
        </div>
        @if($listings->hasPages())
        <div class="mt-4">{{ $listings->links() }}</div>
        @endif
    </div>
</div>
@endsection
