@extends('layouts.admin')

@section('title', 'Yeni Rol Oluştur')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Rol Oluştur</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Yeni bir kullanıcı rolü oluşturun</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Geri Dön
        </a>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Role Info -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Rol Bilgileri</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Rol Adı *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Örn: Satış Yöneticisi">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="3"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Rol hakkında açıklama...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Yetkiler</h2>
                    
                    <div class="space-y-6">
                        <!-- Listings -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">İlanlar</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="listings.view" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Görüntüle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="listings.create" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Oluştur</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="listings.edit" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Düzenle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="listings.delete" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Sil</span>
                                </label>
                            </div>
                        </div>

                        <!-- Contacts -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Kişiler</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="contacts.view" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Görüntüle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="contacts.create" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Oluştur</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="contacts.edit" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Düzenle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="contacts.delete" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Sil</span>
                                </label>
                            </div>
                        </div>

                        <!-- Deals -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Satışlar</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="deals.view" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Görüntüle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="deals.create" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Oluştur</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="deals.edit" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Düzenle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="deals.delete" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Sil</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reports -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Raporlar</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="reports.view" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Görüntüle</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="reports.export" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                                    <span class="ml-2 text-gray-600 dark:text-dark-300 text-sm">Dışa Aktar</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <div class="space-y-3">
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Rolü Kaydet
                        </button>
                        <button type="button" onclick="history.back()" class="w-full px-6 py-3 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">
                            İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
