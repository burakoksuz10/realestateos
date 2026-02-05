@extends('layouts.admin')

@section('title', 'Offices')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Offices</h1>
            <p class="text-dark-400 mt-1">Manage your office locations</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.offices.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Office
            </a>
        </div>
    </div>

    <!-- Office Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($offices ?? [] as $office)
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 hover:border-primary-500/50 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.offices.edit', $office) }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-white mb-2">{{ $office->name }}</h3>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center text-sm text-dark-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $office->address ?? 'No address' }}
                </div>
                <div class="flex items-center text-sm text-dark-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    {{ $office->phone ?? 'No phone' }}
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-dark-700/50">
                <div class="flex items-center space-x-4">
                    <div class="text-center">
                        <p class="text-lg font-semibold text-white">{{ $office->users_count ?? 0 }}</p>
                        <p class="text-xs text-dark-400">Agents</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-semibold text-white">{{ $office->listings_count ?? 0 }}</p>
                        <p class="text-xs text-dark-400">Listings</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $office->is_active ? 'bg-green-500/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                    {{ $office->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-12 text-center">
                <svg class="w-12 h-12 text-dark-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p class="text-dark-400 mb-4">No offices found</p>
                <a href="{{ route('admin.offices.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Your First Office
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if(isset($offices) && $offices->hasPages())
    <div class="flex justify-center">
        {{ $offices->links() }}
    </div>
    @endif
</div>
@endsection
