@extends('layouts.admin')

@section('title', 'Yeni Pipeline')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Yeni Pipeline</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Satış veya talep pipeline'ı oluşturun</p>
        </div>
        <a href="{{ route('admin.pipelines.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <form action="{{ route('admin.pipelines.store') }}" method="POST" class="space-y-6" x-data="{ stages: [{name: '', color: '#0ea5e9', probability: 20}, {name: '', color: '#8b5cf6', probability: 60}, {name: '', color: '#10b981', probability: 100}] }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Pipeline Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İsim *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                placeholder="Örn: Satış Pipeline">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Tür</label>
                            <select name="type" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="deal">Satış</option>
                                <option value="lead">Talep</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Stages -->
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aşamalar</h2>
                        <button type="button" @click="stages.push({name: '', color: '#0ea5e9', probability: 50})" class="px-3 py-1.5 bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 text-sm rounded-lg transition-colors">
                            + Aşama Ekle
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(stage, index) in stages" :key="index">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                                <input type="color" :name="'stages['+index+'][color]'" x-model="stage.color" class="w-8 h-8 rounded-lg cursor-pointer bg-transparent border-0">
                                <input type="text" :name="'stages['+index+'][name]'" x-model="stage.name" required placeholder="Aşama adı..."
                                    class="flex-1 px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                <div class="flex items-center gap-1">
                                    <input type="number" :name="'stages['+index+'][probability]'" x-model="stage.probability" min="0" max="100"
                                        class="w-16 px-2 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm text-center focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <span class="text-gray-500 dark:text-dark-400 text-sm">%</span>
                                </div>
                                <button type="button" @click="stages.splice(index, 1)" class="p-1.5 text-red-400 hover:text-red-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ayarlar</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-gray-600 dark:text-dark-300 text-sm">Varsayılan Pipeline</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-gray-600 dark:text-dark-300 text-sm">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                    <a href="{{ route('admin.pipelines.index') }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
