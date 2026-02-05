@extends('layouts.admin')

@section('title', 'Satış Pipeline - Kanban')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Satış Pipeline</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $pipeline->name ?? 'Varsayılan Pipeline' }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <select class="px-4 py-2 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                @foreach($pipelines as $p)
                    <option value="{{ $p->id }}" {{ ($pipeline->id ?? 0) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.deals.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Fırsat
            </a>
        </div>
    </div>

    @if($pipeline && $pipeline->stages->count() > 0)
    <div class="flex space-x-4 overflow-x-auto pb-4">
        @foreach($pipeline->stages as $stage)
        <div class="flex-shrink-0 w-80">
            <div class="bg-gray-100 dark:bg-dark-800 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color ?? '#6366f1' }}"></div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $stage->name }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-gray-400 rounded-full">
                            {{ $stage->deals->count() }}
                        </span>
                    </div>
                </div>
                
                <div class="space-y-3" data-stage-id="{{ $stage->id }}">
                    @forelse($stage->deals as $deal)
                    <div class="bg-white dark:bg-dark-900 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-dark-700 cursor-move hover:shadow-md transition-shadow" data-deal-id="{{ $deal->id }}">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $deal->title }}</h4>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $deal->created_at->diffForHumans() }}</span>
                        </div>
                        @if($deal->contact)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $deal->contact->full_name }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-primary-600">₺{{ number_format($deal->value ?? 0, 0, ',', '.') }}</span>
                            @if($deal->assignedTo)
                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-dark-700 flex items-center justify-center" title="{{ $deal->assignedTo->name }}">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ strtoupper(substr($deal->assignedTo->name, 0, 1)) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
                        Bu aşamada fırsat yok
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 dark:text-dark-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Pipeline bulunamadı</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Önce bir pipeline oluşturmanız gerekiyor.</p>
        <a href="{{ route('admin.pipelines.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
            Pipeline Oluştur
        </a>
    </div>
    @endif
</div>
@endsection
