@extends('layouts.admin')
@section('title', 'Sayfa Yönetimi')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Sayfalar</h1>
            <p class="text-dark-400 mt-1">Web sitesi sayfalarını yönetin</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Sayfa
            </button>
            <a href="{{ route('admin.websites.show', $website ?? 'main') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-dark-700/50">
            <h2 class="text-lg font-semibold text-white">Tüm Sayfalar</h2>
        </div>
        <div class="divide-y divide-dark-700/50">
            @foreach([
                ['title' => 'Ana Sayfa', 'slug' => '/', 'status' => 'published', 'views' => 1240, 'updated' => '2 gün önce'],
                ['title' => 'Hakkımızda', 'slug' => '/hakkimizda', 'status' => 'published', 'views' => 456, 'updated' => '1 hafta önce'],
                ['title' => 'İlanlar', 'slug' => '/ilanlar', 'status' => 'published', 'views' => 890, 'updated' => '3 saat önce'],
                ['title' => 'İletişim', 'slug' => '/iletisim', 'status' => 'published', 'views' => 234, 'updated' => '5 gün önce'],
                ['title' => 'Blog', 'slug' => '/blog', 'status' => 'draft', 'views' => 0, 'updated' => 'bugün'],
                ['title' => 'Değerleme Talebi', 'slug' => '/degerleme', 'status' => 'published', 'views' => 312, 'updated' => '2 gün önce'],
            ] as $page)
            <div class="flex items-center justify-between px-5 py-4 hover:bg-dark-800/50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-dark-800 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium text-sm">{{ $page['title'] }}</p>
                        <p class="text-dark-500 text-xs">{{ $page['slug'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right hidden md:block">
                        <p class="text-white text-sm">{{ number_format($page['views']) }}</p>
                        <p class="text-dark-500 text-xs">görüntüleme</p>
                    </div>
                    <div class="text-right hidden md:block">
                        <p class="text-dark-400 text-xs">{{ $page['updated'] }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $page['status'] === 'published' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                        {{ $page['status'] === 'published' ? 'Yayında' : 'Taslak' }}
                    </span>
                    <div class="flex items-center gap-2">
                        <button class="p-1.5 text-dark-400 hover:text-white transition-colors" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button class="p-1.5 text-dark-400 hover:text-red-400 transition-colors" title="Sil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
