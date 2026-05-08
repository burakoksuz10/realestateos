@extends('layouts.admin')

@section('title', 'Deals')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Satışlar</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Satış süreçlerinizi yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.deals.kanban') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-100 dark:bg-dark-800 hover:bg-gray-200 dark:hover:bg-dark-700 text-gray-600 dark:text-dark-300 font-medium rounded-xl transition-colors border border-gray-200 dark:border-dark-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                </svg>
                Kanban Görünümü
            </a>
            <a href="{{ route('admin.deals.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni Satış
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-4">
        <form action="{{ route('admin.deals.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Satış ara..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="pipeline_id" class="px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-600 dark:text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tüm Pipeline'lar</option>
                @foreach($pipelines ?? [] as $pipeline)
                <option value="{{ $pipeline->id }}" {{ request('pipeline_id') == $pipeline->id ? 'selected' : '' }}>{{ $pipeline->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-600 dark:text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tüm Durumlar</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Açık</option>
                <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>Kazanıldı</option>
                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Kaybedildi</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                Filtrele
            </button>
        </form>
    </div>

    <!-- Deals Table -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider border-b border-gray-200 dark:border-dark-700/50">
                        <th class="px-6 py-4">Satış</th>
                        <th class="px-6 py-4">Müşteri</th>
                        <th class="px-6 py-4">Değer</th>
                        <th class="px-6 py-4">Aşama</th>
                        <th class="px-6 py-4">Durum</th>
                        <th class="px-6 py-4">Tarih</th>
                        <th class="px-6 py-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($deals ?? [] as $deal)
                    <tr class="hover:bg-gray-50 dark:bg-dark-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-white font-medium">{{ $deal->title }}</p>
                                <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $deal->pipeline?->name }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($deal->contact?->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="text-gray-600 dark:text-dark-300">{{ $deal->contact?->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-emerald-400 font-semibold">₺{{ number_format($deal->value, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400">
                                {{ $deal->stage?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($deal->status === 'won')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">Kazanıldı</span>
                            @elseif($deal->status === 'lost')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-400">Kaybedildi</span>
                            @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-400">Açık</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 dark:text-dark-400 text-sm">
                            {{ $deal->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.deals.show', $deal) }}" class="p-2 text-gray-500 dark:text-dark-400 hover:text-white hover:bg-gray-200 dark:hover:bg-dark-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.deals.edit', $deal) }}" class="p-2 text-gray-500 dark:text-dark-400 hover:text-white hover:bg-gray-200 dark:hover:bg-dark-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-dark-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-dark-400 mb-2">Henüz satış kaydı yok</p>
                                <a href="{{ route('admin.deals.create') }}" class="text-primary-400 hover:text-primary-300">İlk satışınızı ekleyin</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($deals) && $deals->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700/50">
            {{ $deals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
