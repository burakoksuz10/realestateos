@extends('layouts.admin')
@section('title', $user->name . ' - Kullanıcı Detayı')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
            <p class="text-dark-400 mt-1">{{ $user->title ?? $user->roles->first()?->name ?? 'Kullanıcı' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Kullanıcı Bilgileri</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">Ad Soyad</p><p class="text-white text-sm font-medium">{{ $user->name }}</p></div>
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">E-posta</p><p class="text-white text-sm">{{ $user->email }}</p></div>
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">Telefon</p><p class="text-white text-sm">{{ $user->phone ?? '-' }}</p></div>
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">Unvan</p><p class="text-white text-sm">{{ $user->title ?? '-' }}</p></div>
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">Ofis</p><p class="text-white text-sm">{{ $user->office?->name ?? '-' }}</p></div>
                    <div><p class="text-dark-400 text-xs uppercase tracking-wider mb-1">Takım</p><p class="text-white text-sm">{{ $user->team?->name ?? '-' }}</p></div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Performans</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $user->leads()->count() ?? 0 }}</p>
                        <p class="text-dark-400 text-xs mt-1">Lead</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $user->deals()->count() ?? 0 }}</p>
                        <p class="text-dark-400 text-xs mt-1">Fırsat</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-white">{{ $user->listings()->count() ?? 0 }}</p>
                        <p class="text-dark-400 text-xs mt-1">İlan</p>
                    </div>
                    <div class="bg-dark-800 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-primary-400">{{ $user->tasks()->count() ?? 0 }}</p>
                        <p class="text-dark-400 text-xs mt-1">Görev</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex flex-col items-center text-center mb-4">
                    <div class="w-16 h-16 bg-primary-600/20 rounded-full flex items-center justify-center mb-3">
                        <span class="text-2xl font-bold text-primary-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <h3 class="text-white font-semibold">{{ $user->name }}</h3>
                    <p class="text-dark-400 text-sm">{{ $user->email }}</p>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-dark-400">Durum</span>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">{{ $user->is_active ? 'Aktif' : 'Pasif' }}</span>
                    </div>
                    <div class="flex justify-between"><span class="text-dark-400">Roller</span><span class="text-white">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-400">Son Giriş</span><span class="text-white text-xs">{{ $user->last_login_at?->format('d.m.Y H:i') ?? 'Hiç' }}</span></div>
                    <div class="flex justify-between"><span class="text-dark-400">Kayıt</span><span class="text-white text-xs">{{ $user->created_at->format('d.m.Y') }}</span></div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.users.edit', $user) }}" class="block w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-center">Düzenle</a>
                <a href="{{ route('admin.users.activity', $user) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">Aktivite Geçmişi</a>
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 {{ $user->is_active ? 'bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400' : 'bg-green-600/20 hover:bg-green-600/30 text-green-400' }} font-medium rounded-xl transition-colors">
                        {{ $user->is_active ? 'Pasife Al' : 'Aktif Et' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
