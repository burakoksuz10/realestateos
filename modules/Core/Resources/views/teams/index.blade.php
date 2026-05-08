@extends('layouts.admin')

@section('title', 'Teams')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Teams</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Organize your agents into teams</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Team
            </a>
        </div>
    </div>

    <!-- Team Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($teams ?? [] as $team)
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 hover:border-primary-500/50 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($team->name, 0, 2)) }}
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('teams.edit', $team) }}" class="p-2 text-gray-500 dark:text-dark-400 hover:text-white hover:bg-gray-200 dark:hover:bg-dark-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $team->name }}</h3>
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-4">{{ $team->description ?? 'No description' }}</p>

            <!-- Team Leader -->
            @if($team->leader)
            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl mb-4">
                <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-white text-sm font-medium">{{ $team->leader->name }}</p>
                    <p class="text-gray-500 dark:text-dark-400 text-xs">Team Leader</p>
                </div>
            </div>
            @endif

            <!-- Team Members -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-dark-700/50">
                <div class="flex -space-x-2">
                    @foreach(($team->members ?? collect())->take(4) as $member)
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-semibold border-2 border-dark-900">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    @endforeach
                    @if(($team->members_count ?? 0) > 4)
                    <div class="w-8 h-8 bg-gray-200 dark:bg-dark-700 rounded-full flex items-center justify-center text-gray-600 dark:text-dark-300 text-xs font-semibold border-2 border-dark-900">
                        +{{ ($team->members_count ?? 0) - 4 }}
                    </div>
                    @endif
                </div>
                <span class="text-gray-500 dark:text-dark-400 text-sm">{{ $team->members_count ?? 0 }} members</span>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-12 text-center">
                <svg class="w-12 h-12 text-dark-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-gray-500 dark:text-dark-400 mb-4">No teams found</p>
                <a href="{{ route('teams.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Your First Team
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if(isset($teams) && $teams->hasPages())
    <div class="flex justify-center">
        {{ $teams->links() }}
    </div>
    @endif
</div>
@endsection
