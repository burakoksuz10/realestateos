@extends('layouts.admin')

@section('title', 'Talep Pipeline - Kanban')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Talep Pipeline</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $pipeline->name ?? 'Varsayılan Pipeline' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($pipelines->count() > 1)
            <select onchange="window.location.href='?pipeline='+this.value" class="px-4 py-2 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                @foreach($pipelines as $p)
                <option value="{{ $p->id }}" {{ ($pipeline->id ?? 0) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            @endif
            <a href="{{ route('admin.leads.create') }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Potansiyel Müşteri
            </a>
            <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors">
                Liste Görünümü
            </a>
        </div>
    </div>

    @if($pipeline && $pipeline->stages->count() > 0)
    <div class="flex space-x-4 overflow-x-auto pb-4">
        @foreach($pipeline->stages as $stage)
        <div class="flex-shrink-0 w-72">
            <div class="bg-gray-100 dark:bg-dark-800 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color ?? '#0ea5e9' }}"></div>
                        <h3 class="font-semibold text-white text-sm">{{ $stage->name }}</h3>
                        <span class="px-2 py-0.5 text-xs bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400 rounded-full">{{ $stage->leads->count() }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($stage->leads as $lead)
                    <a href="{{ route('admin.leads.show', $lead) }}" class="block bg-white dark:bg-dark-900 rounded-xl p-4 border border-gray-200 dark:border-dark-700/50 hover:border-primary-500/50 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <p class="text-white text-sm font-medium truncate">{{ $lead->contact->first_name ?? '-' }} {{ $lead->contact->last_name ?? '' }}</p>
                            <span class="text-xs text-gray-500 dark:text-dark-400 ml-2 flex-shrink-0">{{ $lead->created_at->diffForHumans() }}</span>
                        </div>
                        @if($lead->contact->phone ?? false)
                        <p class="text-gray-500 dark:text-dark-400 text-xs mb-2">{{ $lead->contact->phone }}</p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $lead->priority === 'urgent' ? 'bg-red-500/20 text-red-400' :
                                   ($lead->priority === 'high' ? 'bg-orange-500/20 text-orange-400' :
                                   ($lead->priority === 'medium' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400')) }}">
                                {{ ['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'][$lead->priority ?? 'medium'] ?? 'Orta' }}
                            </span>
                            @if($lead->assignedTo)
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center" title="{{ $lead->assignedTo->name }}">
                                <span class="text-xs text-white font-medium">{{ strtoupper(substr($lead->assignedTo->name, 0, 1)) }}</span>
                            </div>
                            @endif
                        </div>
                        @if($lead->score)
                        <div class="mt-2 w-full bg-gray-200 dark:bg-dark-700 rounded-full h-1">
                            <div class="h-1 rounded-full {{ $lead->score >= 70 ? 'bg-green-500' : ($lead->score >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $lead->score }}%"></div>
                        </div>
                        @endif
                    </a>
                    @empty
                    <div class="text-center py-6">
                        <p class="text-dark-500 text-xs">Potansiyel müşteri yok</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-12 text-center">
        <p class="text-gray-500 dark:text-dark-400">Henüz pipeline tanımlanmamış.</p>
        <a href="{{ route('admin.pipelines.create') }}" class="mt-4 inline-block px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors">
            Pipeline Oluştur
        </a>
    </div>
    @endif
</div>
@endsection
