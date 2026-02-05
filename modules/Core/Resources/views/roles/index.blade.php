@extends('layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Roles & Permissions</h1>
            <p class="text-dark-400 mt-1">Manage user roles and access permissions</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Role
            </a>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles ?? [] as $role)
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 hover:border-primary-500/50 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center
                    @if($role->name === 'admin') bg-purple-500/20
                    @elseif($role->name === 'manager') bg-blue-500/20
                    @else bg-green-500/20 @endif">
                    <svg class="w-6 h-6 
                        @if($role->name === 'admin') text-purple-400
                        @elseif($role->name === 'manager') text-blue-400
                        @else text-green-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                @if(!in_array($role->name, ['admin', 'manager', 'agent']))
                <div class="flex items-center space-x-2">
                    <a href="{{ route('roles.edit', $role) }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                </div>
                @endif
            </div>

            <h3 class="text-lg font-semibold text-white mb-1 capitalize">{{ $role->name }}</h3>
            <p class="text-dark-400 text-sm mb-4">{{ $role->description ?? 'No description' }}</p>

            <!-- Permissions Preview -->
            <div class="space-y-2 mb-4">
                <p class="text-xs font-medium text-dark-400 uppercase tracking-wider">Permissions</p>
                <div class="flex flex-wrap gap-1">
                    @foreach(($role->permissions ?? collect())->take(5) as $permission)
                    <span class="px-2 py-1 text-xs bg-dark-800 text-dark-300 rounded-lg">
                        {{ $permission->name }}
                    </span>
                    @endforeach
                    @if(($role->permissions_count ?? 0) > 5)
                    <span class="px-2 py-1 text-xs bg-dark-700 text-dark-400 rounded-lg">
                        +{{ ($role->permissions_count ?? 0) - 5 }} more
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-dark-700/50">
                <span class="text-dark-400 text-sm">{{ $role->users_count ?? 0 }} users</span>
                @if(in_array($role->name, ['admin', 'manager', 'agent']))
                <span class="px-2 py-1 text-xs bg-dark-700 text-dark-400 rounded-lg">System Role</span>
                @else
                <span class="px-2 py-1 text-xs bg-primary-500/20 text-primary-400 rounded-lg">Custom Role</span>
                @endif
            </div>
        </div>
        @empty
        <!-- Default Roles -->
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-white mb-1">Admin</h3>
            <p class="text-dark-400 text-sm mb-4">Full system access</p>
            <div class="flex items-center justify-between pt-4 border-t border-dark-700/50">
                <span class="text-dark-400 text-sm">1 user</span>
                <span class="px-2 py-1 text-xs bg-dark-700 text-dark-400 rounded-lg">System Role</span>
            </div>
        </div>

        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-white mb-1">Manager</h3>
            <p class="text-dark-400 text-sm mb-4">Team and office management</p>
            <div class="flex items-center justify-between pt-4 border-t border-dark-700/50">
                <span class="text-dark-400 text-sm">0 users</span>
                <span class="px-2 py-1 text-xs bg-dark-700 text-dark-400 rounded-lg">System Role</span>
            </div>
        </div>

        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-white mb-1">Agent</h3>
            <p class="text-dark-400 text-sm mb-4">Standard agent access</p>
            <div class="flex items-center justify-between pt-4 border-t border-dark-700/50">
                <span class="text-dark-400 text-sm">0 users</span>
                <span class="px-2 py-1 text-xs bg-dark-700 text-dark-400 rounded-lg">System Role</span>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
