@extends('layouts.admin')
@section('title', 'Rol Düzenle')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Rol Düzenle</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $role->name }}</p>
        </div>
        <a href="{{ route('admin.roles.show', $role) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Rol Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Rol Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        @if(isset($role->description))
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $role->description) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Yetkiler</h2>
                    @if(isset($groupedPermissions))
                    <div class="space-y-6">
                        @foreach($groupedPermissions as $group => $permissions)
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3 capitalize">{{ ucfirst(str_replace('_', ' ', $group)) }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($permissions as $permission)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                        class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded focus:ring-primary-500">
                                    <span class="text-gray-600 dark:text-dark-300 text-sm">{{ $permission->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Yetki listesi yüklenemedi.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Rol Özeti</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Kullanıcı Sayısı</span><span class="text-white font-semibold">{{ $role->users()->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-dark-400">Yetki Sayısı</span><span class="text-white font-semibold">{{ $role->permissions()->count() }}</span></div>
                    </div>
                </div>
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.roles.show', $role) }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
