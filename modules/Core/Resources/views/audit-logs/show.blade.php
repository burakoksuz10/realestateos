@extends('layouts.admin')

@section('title', 'Audit Log Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('audit-logs.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Audit Log Details</h1>
                <p class="text-dark-400 mt-1">View detailed information about this activity</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Summary -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Activity Summary</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-dark-400 text-sm mb-1">Action</p>
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($auditLog->action === 'created') bg-green-500/20 text-green-400
                            @elseif($auditLog->action === 'updated') bg-blue-500/20 text-blue-400
                            @elseif($auditLog->action === 'deleted') bg-red-500/20 text-red-400
                            @else bg-dark-700 text-dark-300 @endif">
                            {{ ucfirst($auditLog->action) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm mb-1">Model</p>
                        <p class="text-white font-medium">{{ class_basename($auditLog->auditable_type) }}</p>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm mb-1">Model ID</p>
                        <p class="text-white font-medium">{{ $auditLog->auditable_id }}</p>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm mb-1">Timestamp</p>
                        <p class="text-white font-medium">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Changes -->
            @if($auditLog->action === 'updated' && $auditLog->old_values && $auditLog->new_values)
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Changes Made</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-dark-400 uppercase tracking-wider border-b border-dark-700/50">
                                <th class="px-4 py-3">Field</th>
                                <th class="px-4 py-3">Old Value</th>
                                <th class="px-4 py-3">New Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700/50">
                            @php
                                $oldValues = is_array($auditLog->old_values) ? $auditLog->old_values : json_decode($auditLog->old_values, true) ?? [];
                                $newValues = is_array($auditLog->new_values) ? $auditLog->new_values : json_decode($auditLog->new_values, true) ?? [];
                                $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                            @endphp
                            @foreach($allKeys as $key)
                            <tr>
                                <td class="px-4 py-3 text-white font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                <td class="px-4 py-3 text-red-400">{{ $oldValues[$key] ?? '-' }}</td>
                                <td class="px-4 py-3 text-green-400">{{ $newValues[$key] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Created Data -->
            @if($auditLog->action === 'created' && $auditLog->new_values)
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Created Data</h2>
                <div class="bg-dark-800/50 rounded-xl p-4">
                    <pre class="text-dark-300 text-sm overflow-x-auto">{{ json_encode(is_array($auditLog->new_values) ? $auditLog->new_values : json_decode($auditLog->new_values, true), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            <!-- Deleted Data -->
            @if($auditLog->action === 'deleted' && $auditLog->old_values)
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Deleted Data</h2>
                <div class="bg-dark-800/50 rounded-xl p-4">
                    <pre class="text-dark-300 text-sm overflow-x-auto">{{ json_encode(is_array($auditLog->old_values) ? $auditLog->old_values : json_decode($auditLog->old_values, true), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Performed By</h2>
                @if($auditLog->user)
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($auditLog->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $auditLog->user->name }}</p>
                        <p class="text-dark-400 text-sm">{{ $auditLog->user->email }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-dark-700 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">System</p>
                        <p class="text-dark-400 text-sm">Automated action</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Technical Details -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Technical Details</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-dark-400 text-sm mb-1">IP Address</p>
                        <p class="text-white font-mono text-sm">{{ $auditLog->ip_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-dark-400 text-sm mb-1">User Agent</p>
                        <p class="text-white text-sm break-all">{{ Str::limit($auditLog->user_agent ?? 'N/A', 100) }}</p>
                    </div>
                    @if($auditLog->tenant)
                    <div>
                        <p class="text-dark-400 text-sm mb-1">Tenant</p>
                        <p class="text-white font-medium">{{ $auditLog->tenant->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
