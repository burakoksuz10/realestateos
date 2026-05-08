@extends('layouts.admin')

@section('title', 'Activities')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activities</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Track all your CRM activities</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Log Activity
            </a>
        </div>
    </div>

    <!-- Activity Filters -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('activities.index') }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ !request('type') ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            All
        </a>
        <a href="{{ route('activities.index', ['type' => 'call']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('type') === 'call' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Calls
        </a>
        <a href="{{ route('activities.index', ['type' => 'email']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('type') === 'email' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Emails
        </a>
        <a href="{{ route('activities.index', ['type' => 'meeting']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('type') === 'meeting' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Meetings
        </a>
        <a href="{{ route('activities.index', ['type' => 'viewing']) }}" class="px-4 py-2 text-sm font-medium rounded-xl {{ request('type') === 'viewing' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-dark-300 hover:bg-gray-200 dark:hover:bg-dark-700' }} transition-colors">
            Viewings
        </a>
    </div>

    <!-- Activities Timeline -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
        <div class="space-y-6">
            @forelse($activities ?? [] as $activity)
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    @switch($activity->type)
                        @case('call')
                            <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            @break
                        @case('email')
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            @break
                        @case('meeting')
                            <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            @break
                        @case('viewing')
                            <div class="w-10 h-10 bg-amber-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                            @break
                        @default
                            <div class="w-10 h-10 bg-gray-200 dark:bg-dark-700 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500 dark:text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                    @endswitch
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-white font-medium">{{ $activity->subject }}</p>
                        <span class="text-gray-500 dark:text-dark-400 text-sm">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-500 dark:text-dark-400 text-sm mt-1">{{ $activity->description }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        @if($activity->contact)
                        <a href="{{ route('contacts.show', $activity->contact) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                            {{ $activity->contact->name }}
                        </a>
                        @endif
                        @if($activity->deal)
                        <a href="{{ route('deals.show', $activity->deal) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                            {{ $activity->deal->title }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-dark-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-dark-400 mb-4">No activities recorded yet</p>
                <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Log Your First Activity
                </a>
            </div>
            @endforelse
        </div>

        @if(isset($activities) && $activities->hasPages())
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-dark-700/50">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
