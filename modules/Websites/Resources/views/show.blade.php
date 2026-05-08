@extends('layouts.admin')
@section('title', 'Web Sitesi Detayı')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Web Sitesi Yönetimi</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $website ?? 'main' }}</p>
        </div>
        <a href="{{ route('admin.websites.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Stats -->
        <div class="lg:col-span-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Toplam Ziyaret', 'value' => '2,441', 'change' => '+12%', 'up' => true],
                ['label' => 'Sayfa Görüntüleme', 'value' => '8,920', 'change' => '+8%', 'up' => true],
                ['label' => 'Form Gönderimleri', 'value' => '47', 'change' => '+23%', 'up' => true],
                ['label' => 'Hemen Çıkma', 'value' => '34%', 'change' => '-5%', 'up' => true],
            ] as $stat)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $stat['value'] }}</p>
                <span class="text-xs {{ $stat['up'] ? 'text-green-400' : 'text-red-400' }}">{{ $stat['change'] }} bu ay</span>
            </div>
            @endforeach
        </div>

        <!-- Quick Actions -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı Erişim</h2>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.websites.pages.index', $website ?? 'main') }}" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-dark-800 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Sayfalar</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">12 sayfa</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.websites.forms.index', $website ?? 'main') }}" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-dark-800 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Formlar</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">5 form</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-dark-800 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Tema</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">Modern Dark</p>
                        </div>
                    </a>
                    <a href="#" target="_blank" class="flex items-center gap-3 p-4 bg-gray-100 dark:bg-dark-800 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                        <div class="w-10 h-10 bg-orange-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Siteyi Gör</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">Yeni sekmede aç</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Form Submissions -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Son Form Gönderimleri</h2>
                <div class="space-y-3">
                    @foreach([
                        ['name' => 'Ahmet Yılmaz', 'form' => 'İletişim Formu', 'time' => '2 saat önce', 'phone' => '0532 xxx xxxx'],
                        ['name' => 'Fatma Kaya', 'form' => 'İlan Talebi', 'time' => '5 saat önce', 'phone' => '0543 xxx xxxx'],
                        ['name' => 'Mehmet Demir', 'form' => 'Değerleme Talebi', 'time' => '1 gün önce', 'phone' => '0555 xxx xxxx'],
                    ] as $submission)
                    <div class="flex items-center justify-between p-3 bg-gray-100 dark:bg-dark-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-600/20 rounded-full flex items-center justify-center">
                                <span class="text-primary-400 text-xs font-medium">{{ strtoupper(substr($submission['name'], 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium">{{ $submission['name'] }}</p>
                                <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $submission['form'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $submission['time'] }}</p>
                            <p class="text-dark-500 text-xs">{{ $submission['phone'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Site Bilgisi</h3>
                <div class="space-y-3 text-sm">
                    <div><span class="text-gray-500 dark:text-dark-400">Domain</span><p class="text-white mt-0.5">www.sirketiniz.com</p></div>
                    <div><span class="text-gray-500 dark:text-dark-400">SSL</span><p class="text-green-400 mt-0.5">Aktif ✓</p></div>
                    <div><span class="text-gray-500 dark:text-dark-400">Son Güncelleme</span><p class="text-white mt-0.5">{{ now()->format('d.m.Y') }}</p></div>
                    <div><span class="text-gray-500 dark:text-dark-400">Durum</span><p class="text-green-400 mt-0.5">Yayında</p></div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-3">Ayarlar</h3>
                <div class="space-y-2">
                    <button class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white text-left rounded-lg text-sm transition-colors">SEO Ayarları</button>
                    <button class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white text-left rounded-lg text-sm transition-colors">Analitik</button>
                    <button class="w-full px-3 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white text-left rounded-lg text-sm transition-colors">Domain Ayarları</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
