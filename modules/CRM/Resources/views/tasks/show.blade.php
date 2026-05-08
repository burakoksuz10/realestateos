@extends('layouts.admin')

@section('title', $task->title . ' - Görev Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $task->title }}</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Görev #{{ $task->id }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tasks.edit', $task) }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Görev Detayları</h2>
                @if($task->description)
                <p class="text-gray-600 dark:text-dark-300 text-sm mb-4">{{ $task->description }}</p>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-xs mb-1">Tür</p>
                        <p class="text-white text-sm font-medium">{{ ['call'=>'Arama','email'=>'E-posta','meeting'=>'Toplantı','showing'=>'Gösterim','follow_up'=>'Takip','document'=>'Belge','other'=>'Diğer'][$task->type] ?? ucfirst($task->type) }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-xs mb-1">Öncelik</p>
                        <p class="text-sm font-medium {{ $task->priority === 'urgent' ? 'text-red-400' : ($task->priority === 'high' ? 'text-orange-400' : ($task->priority === 'medium' ? 'text-yellow-400' : 'text-gray-600 dark:text-dark-300')) }}">
                            {{ ['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'][$task->priority] ?? ucfirst($task->priority) }}
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-xs mb-1">Vade Tarihi</p>
                        <p class="text-white text-sm font-medium">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d.m.Y') : '-' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                        <p class="text-gray-500 dark:text-dark-400 text-xs mb-1">Atanan</p>
                        <p class="text-white text-sm font-medium">{{ $task->assignedTo->name ?? '-' }}</p>
                    </div>
                </div>
                @if($task->result)
                <div class="mt-4 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                    <p class="text-green-400 text-xs mb-1">Sonuç</p>
                    <p class="text-white text-sm">{{ $task->result_notes ?? $task->result }}</p>
                </div>
                @endif
            </div>

            <!-- Related -->
            @if($task->contact || $task->lead || $task->deal)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İlişkili Kayıtlar</h2>
                <div class="space-y-3">
                    @if($task->contact)
                    <a href="{{ route('admin.contacts.show', $task->contact) }}" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl hover:bg-gray-100 dark:bg-dark-800 transition-colors">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $task->contact->first_name }} {{ $task->contact->last_name }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">Kişi</p>
                        </div>
                    </a>
                    @endif
                    @if($task->lead)
                    <a href="{{ route('admin.leads.show', $task->lead) }}" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl hover:bg-gray-100 dark:bg-dark-800 transition-colors">
                        <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">Pot. Müşteri #{{ $task->lead->id }}</p>
                            <p class="text-gray-500 dark:text-dark-400 text-xs">Talep</p>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <p class="text-gray-500 dark:text-dark-400 text-sm mb-2">Durum</p>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    {{ $task->status === 'completed' ? 'bg-green-500/20 text-green-400' :
                       ($task->status === 'in_progress' ? 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400' :
                       ($task->status === 'cancelled' ? 'bg-red-500/20 text-red-400' : 'bg-yellow-500/20 text-yellow-400')) }}">
                    {{ ['pending'=>'Bekliyor','in_progress'=>'Devam Ediyor','completed'=>'Tamamlandı','cancelled'=>'İptal'][$task->status] ?? $task->status }}
                </span>
            </div>

            @if($task->status !== 'completed' && $task->status !== 'cancelled')
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                <form action="{{ route('admin.tasks.complete', $task) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">
                        Tamamlandı İşaretle
                    </button>
                </form>
                <a href="{{ route('admin.tasks.edit', $task) }}" class="block w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors text-center">Düzenle</a>
                <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Görevi silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-600 dark:text-dark-300 font-medium rounded-xl transition-colors">Sil</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
