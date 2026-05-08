@extends('layouts.admin')

@section('title', 'Pipelines')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Pipelines</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Manage your sales pipelines and stages</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('pipelines.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Pipeline
            </a>
        </div>
    </div>

    <!-- Pipelines Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($pipelines ?? [] as $pipeline)
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 hover:border-primary-500/50 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('pipelines.edit', $pipeline) }}" class="p-2 text-gray-500 dark:text-dark-400 hover:text-white hover:bg-gray-200 dark:hover:bg-dark-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $pipeline->name }}</h3>
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-4">{{ $pipeline->description ?? 'No description' }}</p>

            <!-- Pipeline Stages -->
            <div class="space-y-2 mb-4">
                <p class="text-xs font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider">Stages</p>
                <div class="flex flex-wrap gap-1">
                    @foreach(($pipeline->stages ?? collect())->take(4) as $stage)
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 rounded-lg">
                        {{ $stage->name }}
                    </span>
                    @endforeach
                    @if(($pipeline->stages_count ?? 0) > 4)
                    <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400 rounded-lg">
                        +{{ ($pipeline->stages_count ?? 0) - 4 }} more
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-dark-700/50">
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $pipeline->deals_count ?? 0 }}</p>
                        <p class="text-xs text-gray-500 dark:text-dark-400">Deals</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-semibold text-emerald-400">₺{{ number_format(($pipeline->total_value ?? 0) / 1000000, 1) }}M</p>
                        <p class="text-xs text-gray-500 dark:text-dark-400">Value</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $pipeline->is_default ? 'bg-primary-500/20 text-primary-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400' }}">
                    {{ $pipeline->is_default ? 'Default' : 'Custom' }}
                </span>
            </div>
        </div>
        @empty
        <!-- Default Pipeline -->
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Sales Pipeline</h3>
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-4">Default sales pipeline</p>
            <div class="space-y-2 mb-4">
                <p class="text-xs font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider">Stages</p>
                <div class="flex flex-wrap gap-1">
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 rounded-lg">New</span>
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 rounded-lg">Qualified</span>
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 rounded-lg">Proposal</span>
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 rounded-lg">Negotiation</span>
                    <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400 rounded-lg">+2 more</span>
                </div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-dark-700/50">
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">0</p>
                        <p class="text-xs text-gray-500 dark:text-dark-400">Deals</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-semibold text-emerald-400">₺0</p>
                        <p class="text-xs text-gray-500 dark:text-dark-400">Value</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">Default</span>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
