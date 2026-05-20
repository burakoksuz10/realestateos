@extends('layouts.admin')

@section('title', $pipeline->name . ' - Pipeline Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pipeline->name }}</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $pipeline->type === 'deal' ? 'Satış Pipeline' : 'Talep Pipeline' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pipelines.edit', $pipeline) }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.pipelines.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Aşamalar</h2>
                <div class="space-y-3">
                    @forelse($pipeline->stages->sortBy('order') as $stage)
                    @php $actionCount = is_array($stage->auto_actions ?? null) ? count($stage->auto_actions) : 0; @endphp
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $stage->color ?? '#0ea5e9' }}"></div>
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ $stage->name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($stage->is_won_stage)
                                <span class="text-xs text-green-400">Kazanıldı aşaması</span>
                                @elseif($stage->is_lost_stage)
                                <span class="text-xs text-red-400">Kaybedildi aşaması</span>
                                @endif
                                @if($actionCount > 0)
                                <span class="text-xs px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded-full">⚡ {{ $actionCount }} aksiyon</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">%{{ $stage->probability }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">olasılık</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white font-semibold">{{ $stage->deals_count ?? ($stage->deals->count() ?? 0) }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">kayıt</p>
                        </div>
                        <a href="{{ route('admin.pipelines.stages.auto-actions.edit', [$pipeline, $stage]) }}"
                           class="px-3 py-1.5 bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 text-sm rounded-lg transition-colors whitespace-nowrap">
                            ⚡ Aksiyonlar
                        </a>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz aşama eklenmemiş.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Pipeline Bilgileri</h2>
                <div class="space-y-2">
                    @if($pipeline->description)
                    <p class="text-gray-600 dark:text-dark-300 text-sm mb-3">{{ $pipeline->description }}</p>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Tür</span>
                        <span class="text-white text-sm">{{ $pipeline->type === 'deal' ? 'Satış' : 'Talep' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Aşama Sayısı</span>
                        <span class="text-white font-semibold">{{ $pipeline->stages->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Varsayılan</span>
                        <span class="{{ $pipeline->is_default ? 'text-green-400' : 'text-gray-500 dark:text-dark-400' }} text-sm">{{ $pipeline->is_default ? 'Evet' : 'Hayır' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Durum</span>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $pipeline->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}">
                            {{ $pipeline->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.pipelines.edit', $pipeline) }}" class="block w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors text-center">Düzenle</a>
                <form action="{{ route('admin.pipelines.destroy', $pipeline) }}" method="POST" onsubmit="return confirm('Pipeline\'ı silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
