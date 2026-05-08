@extends('layouts.admin')
@section('title', $user->name . ' - Aktivite Geçmişi')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Aktivite Geçmişi</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.show', $user) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-dark-700/50">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">İşlem Geçmişi</h2>
        </div>
        <div class="divide-y divide-dark-700/50">
            @forelse($activities ?? [] as $log)
            <div class="flex items-start gap-4 p-4">
                <div class="w-8 h-8 rounded-full bg-primary-600/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium">{{ $log->description ?? ($log->event . ' - ' . $log->subject_type) }}</p>
                    <p class="text-gray-500 dark:text-dark-400 text-xs mt-0.5">{{ $log->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz aktivite kaydı bulunmuyor.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
