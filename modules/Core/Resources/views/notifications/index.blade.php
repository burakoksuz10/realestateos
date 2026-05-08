@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Stay updated with your latest activities</p>
        </div>
        <div class="flex items-center space-x-3">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-gray-100 dark:bg-dark-800 hover:bg-gray-200 dark:hover:bg-dark-700 text-gray-600 dark:text-dark-300 font-medium rounded-xl transition-colors border border-gray-200 dark:border-dark-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark All as Read
                </button>
            </form>
        </div>
    </div>

    <!-- Notification Filters -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('notifications.index') }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ !request('filter') ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            All
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('filter') === 'unread' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Unread
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'read']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('filter') === 'read' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Read
        </a>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="divide-y divide-dark-700/50">
            @forelse($notifications ?? [] as $notification)
            <div class="p-4 hover:bg-gray-50 dark:bg-dark-800/50 transition-colors {{ !$notification->read_at ? 'bg-primary-500/5' : '' }}">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        @switch($notification->data['type'] ?? 'info')
                            @case('lead')
                                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                @break
                            @case('deal')
                                <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                @break
                            @case('task')
                                <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                </div>
                                @break
                            @case('warning')
                                <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="w-10 h-10 bg-primary-500/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-white font-medium">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            <span class="text-gray-500 dark:text-dark-400 text-sm">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">{{ $notification->data['message'] ?? '' }}</p>
                        @if(isset($notification->data['action_url']))
                        <a href="{{ $notification->data['action_url'] }}" class="inline-flex items-center mt-2 text-primary-400 hover:text-primary-300 text-sm">
                            View Details
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @if(!$notification->read_at)
                    <div class="flex-shrink-0">
                        <span class="w-2 h-2 bg-primary-500 rounded-full block"></span>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-dark-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-gray-500 dark:text-dark-400">No notifications yet</p>
            </div>
            @endforelse
        </div>

        @if(isset($notifications) && $notifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-dark-700/50">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
