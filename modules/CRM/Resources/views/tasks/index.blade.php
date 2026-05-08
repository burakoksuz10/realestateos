@extends('layouts.admin')

@section('title', 'Görevler')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Görevler</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tüm görevlerinizi yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.tasks.calendar') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Takvim
            </a>
            <a href="{{ route('admin.tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Görev
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Görev</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Öncelik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bitiş Tarihi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Atanan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $task->priority === 'urgent' ? 'bg-red-100 dark:bg-red-900/30' : 
                                       ($task->priority === 'high' ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-gray-100 dark:bg-dark-700') }}">
                                    <svg class="w-5 h-5 {{ $task->priority === 'urgent' ? 'text-red-600' : 
                                                          ($task->priority === 'high' ? 'text-orange-600' : 'text-gray-600') }}" 
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $task->title }}</div>
                                    @if($task->contact)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->contact->full_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $priorityColors = [
                                    'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'normal' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                                ];
                                $priorityLabels = [
                                    'urgent' => 'Acil',
                                    'high' => 'Yüksek',
                                    'normal' => 'Normal',
                                    'low' => 'Düşük',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-lg {{ $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $priorityLabels[$task->priority] ?? $task->priority }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
                                ];
                                $statusLabels = [
                                    'pending' => 'Bekliyor',
                                    'in_progress' => 'Devam Ediyor',
                                    'completed' => 'Tamamlandı',
                                    'cancelled' => 'İptal',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-lg {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $task->due_date ? $task->due_date->format('d.m.Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($task->assignedTo)
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-dark-600 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ strtoupper(substr($task->assignedTo->name, 0, 2)) }}</span>
                                </div>
                                <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $task->assignedTo->name }}</span>
                            </div>
                            @else
                            <span class="text-sm text-gray-400">Atanmamış</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @if($task->status !== 'completed')
                                <form action="{{ route('admin.tasks.complete', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 text-green-600 hover:text-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Tamamla">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('admin.tasks.edit', $task) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Henüz görev yok</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">İlk görevinizi oluşturarak başlayın.</p>
                            <a href="{{ route('admin.tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                                Yeni Görev Oluştur
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tasks->hasPages())
    <div class="flex justify-center">
        {{ $tasks->links() }}
    </div>
    @endif
</div>
@endsection
