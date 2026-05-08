@extends('layouts.admin')

@section('title', 'Projeler')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projeler</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tüm gayrimenkul projelerini yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Proje
            </a>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
        <div class="bg-white dark:bg-dark-800 rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-dark-700 hover:shadow-lg transition-shadow">
            <div class="relative">
                @if($project->getFirstMediaUrl('photos'))
                    <img src="{{ $project->getFirstMediaUrl('photos', 'thumb') }}" alt="{{ $project->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                        <svg class="w-16 h-16 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                @endif
                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-lg
                        {{ $project->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $project->status === 'under_construction' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                        {{ $project->status === 'planning' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}">
                        {{ $project->status === 'completed' ? 'Tamamlandı' : ($project->status === 'under_construction' ? 'İnşaat Halinde' : 'Planlama') }}
                    </span>
                </div>
            </div>
            <div class="p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $project->name }}</h3>
                @if($project->developer)
                    <p class="text-sm text-primary-600 mb-2">{{ $project->developer }}</p>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $project->district }}, {{ $project->city }}
                </p>
                <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
                    @if($project->total_units)
                        <span>{{ $project->total_units }} Ünite</span>
                    @endif
                    @if($project->available_units)
                        <span>{{ $project->available_units }} Müsait</span>
                    @endif
                    <span>{{ $project->listings_count }} İlan</span>
                </div>
                @if($project->min_price && $project->max_price)
                <div class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    ₺{{ number_format($project->min_price, 0, ',', '.') }} - ₺{{ number_format($project->max_price, 0, ',', '.') }}
                </div>
                @endif
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-dark-700">
                    @if($project->delivery_date)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Teslim: {{ \Carbon\Carbon::parse($project->delivery_date)->format('M Y') }}
                        </span>
                    @else
                        <span></span>
                    @endif
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.projects.show', $project) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.projects.edit', $project) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-dark-800 rounded-2xl p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 dark:text-dark-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Henüz proje yok</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">İlk projenizi oluşturarak başlayın.</p>
                <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Yeni Proje Oluştur
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
    <div class="flex justify-center">
        {{ $projects->links() }}
    </div>
    @endif
</div>
@endsection
