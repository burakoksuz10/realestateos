@extends('layouts.admin')
@section('title', 'Web Siteleri')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Web Siteleri</h1>
            <p class="text-dark-400 mt-1">Gayrimenkul web sitelerinizi yönetin</p>
        </div>
        <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Yeni Site
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <!-- Demo website card -->
        <a href="{{ route('admin.websites.show', 'main') }}" class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 hover:border-dark-600 transition-colors group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-primary-600/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </div>
                <span class="text-xs px-2 py-1 bg-green-500/20 text-green-400 rounded-full">Aktif</span>
            </div>
            <h3 class="text-white font-semibold mb-1 group-hover:text-primary-400 transition-colors">Ana Web Sitesi</h3>
            <p class="text-dark-400 text-sm mb-4">www.sirketiniz.com</p>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-dark-800 rounded-lg p-2">
                    <p class="text-white font-semibold text-sm">12</p>
                    <p class="text-dark-500 text-xs">Sayfa</p>
                </div>
                <div class="bg-dark-800 rounded-lg p-2">
                    <p class="text-white font-semibold text-sm">5</p>
                    <p class="text-dark-500 text-xs">Form</p>
                </div>
                <div class="bg-dark-800 rounded-lg p-2">
                    <p class="text-white font-semibold text-sm">2.4K</p>
                    <p class="text-dark-500 text-xs">Ziyaret</p>
                </div>
            </div>
        </a>

        <!-- Add new site placeholder -->
        <button class="bg-dark-900 border border-dashed border-dark-600 rounded-2xl p-6 hover:border-dark-500 transition-colors flex flex-col items-center justify-center min-h-[200px] text-dark-500 hover:text-dark-400">
            <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path></svg>
            <p class="font-medium">Yeni Site Ekle</p>
            <p class="text-xs mt-1">Yeni bir web sitesi oluşturun</p>
        </button>
    </div>

    <!-- Features -->
    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Web Sitesi Özellikleri</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Sayfa Editörü', 'desc' => 'Sürükle & bırak ile sayfa tasarımı'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Form Yönetimi', 'desc' => 'İletişim formları ve lead toplama'],
                ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'title' => 'İlan Entegrasyonu', 'desc' => 'Portföyünüz otomatik güncellenir'],
                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Analitik', 'desc' => 'Ziyaret ve dönüşüm takibi'],
            ] as $feature)
            <div class="bg-dark-800 rounded-xl p-4">
                <div class="w-8 h-8 bg-primary-600/20 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path></svg>
                </div>
                <p class="text-white text-sm font-medium">{{ $feature['title'] }}</p>
                <p class="text-dark-400 text-xs mt-1">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
