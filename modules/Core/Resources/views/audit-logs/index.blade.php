@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Audit Logs</h1>
            <p class="text-dark-400 mt-1">Track all system activities and changes</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('audit-logs.export', request()->query()) }}" class="inline-flex items-center px-4 py-2.5 bg-dark-800 hover:bg-dark-700 text-dark-300 font-medium rounded-xl transition-colors border border-dark-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-4">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in changes..." class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select name="action" class="px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Actions</option>
                @foreach($actions ?? [] as $action)
                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                @endforeach
            </select>
            <select name="model_type" class="px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Models</option>
                @foreach($modelTypes ?? [] as $type)
                <option value="{{ $type }}" {{ request('model_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-dark-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-medium text-dark-400 uppercase tracking-wider border-b border-dark-700/50">
                        <th class="px-6 py-4">Timestamp</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Model</th>
                        <th class="px-6 py-4">IP Address</th>
                        <th class="px-6 py-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($auditLogs ?? [] as $log)
                    <tr class="hover:bg-dark-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-white text-sm">{{ $log->created_at->format('M d, Y') }}</p>
                                <p class="text-dark-400 text-xs">{{ $log->created_at->format('H:i:s') }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <span class="text-dark-300">{{ $log->user?->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                @if($log->action === 'created') bg-green-500/20 text-green-400
                                @elseif($log->action === 'updated') bg-blue-500/20 text-blue-400
                                @elseif($log->action === 'deleted') bg-red-500/20 text-red-400
                                @else bg-dark-700 text-dark-300 @endif">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-white text-sm">{{ class_basename($log->auditable_type) }}</p>
                                <p class="text-dark-400 text-xs">ID: {{ $log->auditable_id }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-dark-400 text-sm">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('audit-logs.show', $log) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                                View Changes
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-dark-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-dark-400">No audit logs found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($auditLogs) && $auditLogs->hasPages())
        <div class="px-6 py-4 border-t border-dark-700/50">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
