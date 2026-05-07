@extends('layouts.admin')

@section('title', $role->name . ' - Rol Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $role->name }}</h1>
            <p class="text-dark-400 mt-1">Rol detayları ve yetkileri</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.roles.edit', $role) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Permissions -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Yetkiler</h2>
                @php
                    $grouped = $role->permissions->groupBy(function($p) {
                        return explode('.', $p->name)[0];
                    });
                    $groupLabels = [
                        'listings' => 'İlanlar', 'contacts' => 'Kişiler', 'leads' => 'Talepler',
                        'deals' => 'Satışlar', 'reports' => 'Raporlar', 'users' => 'Kullanıcılar',
                        'offices' => 'Ofisler', 'teams' => 'Takımlar', 'settings' => 'Ayarlar',
                    ];
                @endphp
                @forelse($grouped as $group => $permissions)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($permissions as $permission)
                        <span class="px-3 py-1 bg-primary-500/20 text-primary-400 rounded-lg text-sm">
                            {{ explode('.', $permission->name)[1] ?? $permission->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-dark-400">Bu role henüz yetki atanmamış.</p>
                @endforelse
            </div>

            <!-- Users with this role -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Bu Roldeki Kullanıcılar</h2>
                @php $users = $role->users ?? collect(); @endphp
                @forelse($users as $user)
                <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $user->name }}</p>
                            <p class="text-dark-400 text-xs">{{ $user->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $user) }}" class="text-primary-400 hover:text-primary-300 text-sm">Görüntüle</a>
                </div>
                @empty
                <p class="text-dark-400 text-sm">Bu role atanmış kullanıcı yok.</p>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Özet</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Toplam Yetki</span>
                        <span class="text-white font-semibold">{{ $role->permissions->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Kullanıcı Sayısı</span>
                        <span class="text-white font-semibold">{{ $role->users_count ?? ($role->users->count() ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Tür</span>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ in_array($role->name, ['super-admin','admin']) ? 'bg-purple-500/20 text-purple-400' : 'bg-blue-500/20 text-blue-400' }}">
                            {{ in_array($role->name, ['super-admin','admin']) ? 'Sistem' : 'Özel' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Oluşturulma</span>
                        <span class="text-white text-sm">{{ $role->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.roles.edit', $role) }}" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                    Düzenle
                </a>
                @if(!in_array($role->name, ['super-admin', 'admin']))
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Bu rolü silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">
                        Rolü Sil
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
