@extends('layouts.admin')

@section('title', $office->name . ' - Ofis Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $office->name }}</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $office->city }}{{ $office->district ? ', ' . $office->district : '' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.offices.edit', $office) }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.offices.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $office->users->count() }}</p>
                <p class="text-gray-500 dark:text-dark-400 text-sm">Danışman</p>
            </div>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $office->listings()->count() }}</p>
                <p class="text-gray-500 dark:text-dark-400 text-sm">Aktif İlan</p>
            </div>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $office->teams()->count() }}</p>
                <p class="text-gray-500 dark:text-dark-400 text-sm">Takım</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Agents -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Danışmanlar</h2>
                @forelse($office->users as $user)
                <div class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-dark-700/50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $user->name }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $user->title ?? $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}">
                            {{ $user->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                        <a href="{{ route('admin.users.show', $user) }}" class="text-primary-400 hover:text-primary-300 text-sm">Görüntüle</a>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-dark-400 text-sm">Bu ofiste henüz danışman yok.</p>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ofis Bilgileri</h2>
                <div class="space-y-3">
                    @if($office->code)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Kod</span>
                        <span class="text-white text-sm font-mono">{{ $office->code }}</span>
                    </div>
                    @endif
                    @if($office->phone)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Telefon</span>
                        <span class="text-white text-sm">{{ $office->phone }}</span>
                    </div>
                    @endif
                    @if($office->email)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">E-posta</span>
                        <span class="text-white text-sm">{{ $office->email }}</span>
                    </div>
                    @endif
                    @if($office->address)
                    <div>
                        <span class="text-gray-500 dark:text-dark-400 text-sm block mb-1">Adres</span>
                        <span class="text-white text-sm">{{ $office->address }}, {{ $office->city }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Durum</span>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $office->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}">
                            {{ $office->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                    @if($office->is_headquarters)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Tür</span>
                        <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Merkez</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($office->manager)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Ofis Yöneticisi</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($office->manager->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $office->manager->name }}</p>
                        <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $office->manager->email }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.offices.edit', $office) }}" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                    Düzenle
                </a>
                <form action="{{ route('admin.offices.destroy', $office) }}" method="POST" onsubmit="return confirm('Bu ofisi silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">
                        Ofisi Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
