@extends('layouts.admin')

@section('title', 'Pipeline Düzenle')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Pipeline Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $pipeline->name }}</p>
        </div>
        <a href="{{ route('admin.pipelines.show', $pipeline) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <form action="{{ route('admin.pipelines.update', $pipeline) }}" method="POST" class="space-y-6"
        x-data="{ stages: {{ json_encode($pipeline->stages->sortBy('order')->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'color' => $s->color ?? '#0ea5e9', 'probability' => $s->probability])->values()) }} }">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Pipeline Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">İsim *</label>
                            <input type="text" name="name" value="{{ old('name', $pipeline->name) }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Tür</label>
                            <select name="type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="deal" {{ $pipeline->type === 'deal' ? 'selected' : '' }}>Satış</option>
                                <option value="lead" {{ $pipeline->type === 'lead' ? 'selected' : '' }}>Talep</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $pipeline->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-white">Aşamalar</h2>
                        <button type="button" @click="stages.push({id: null, name: '', color: '#0ea5e9', probability: 50})" class="px-3 py-1.5 bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 text-sm rounded-lg transition-colors">
                            + Aşama Ekle
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(stage, index) in stages" :key="index">
                            <div class="flex items-center gap-3 p-3 bg-dark-800/50 rounded-xl">
                                <input type="hidden" :name="'stages['+index+'][id]'" :value="stage.id">
                                <input type="color" :name="'stages['+index+'][color]'" x-model="stage.color" class="w-8 h-8 rounded-lg cursor-pointer bg-transparent border-0">
                                <input type="text" :name="'stages['+index+'][name]'" x-model="stage.name" required placeholder="Aşama adı..."
                                    class="flex-1 px-3 py-2 bg-dark-700 border border-dark-600 rounded-lg text-white text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                <div class="flex items-center gap-1">
                                    <input type="number" :name="'stages['+index+'][probability]'" x-model="stage.probability" min="0" max="100"
                                        class="w-16 px-2 py-2 bg-dark-700 border border-dark-600 rounded-lg text-white text-sm text-center focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <span class="text-dark-400 text-sm">%</span>
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
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Ayarlar</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" {{ $pipeline->is_default ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-dark-300 text-sm">Varsayılan Pipeline</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $pipeline->is_active ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-dark-300 text-sm">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.pipelines.show', $pipeline) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
