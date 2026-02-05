@extends('layouts.admin')

@section('title', 'Leadler')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leadler</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Potansiyel müşterilerinizi yönetin</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.leads.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Yeni Lead
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <form method="GET" action="{{ route('admin.leads.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durum</label>
                <select name="status" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tümü</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Yeni</option>
                    <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>İletişime Geçildi</option>
                    <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Nitelikli</option>
                    <option value="proposal" {{ request('status') == 'proposal' ? 'selected' : '' }}>Teklif</option>
                    <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>Müzakere</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Dönüştürüldü</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Kaybedildi</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Danışman</label>
                <select name="assigned_to" class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tümü</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Arama</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="İsim, email veya telefon..." class="w-full px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-600 transition-colors">
                    Filtrele
                </button>
            </div>
        </form>
    </div>

    <!-- Leads List -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kişi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Skor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kaynak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Danışman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tarih</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                        {{ $lead->contact ? strtoupper(substr($lead->contact->first_name, 0, 1) . substr($lead->contact->last_name, 0, 1)) : 'NA' }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $lead->contact ? $lead->contact->full_name : 'İsimsiz' }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $lead->contact->email ?? $lead->contact->phone ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'new' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'contacted' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'qualified' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'proposal' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                    'negotiation' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'converted' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'lost' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $statusLabels = [
                                    'new' => 'Yeni',
                                    'contacted' => 'İletişime Geçildi',
                                    'qualified' => 'Nitelikli',
                                    'proposal' => 'Teklif',
                                    'negotiation' => 'Müzakere',
                                    'converted' => 'Dönüştürüldü',
                                    'lost' => 'Kaybedildi',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-lg {{ $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$lead->status] ?? $lead->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 dark:bg-dark-600 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full {{ $lead->score >= 80 ? 'bg-green-500' : ($lead->score >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $lead->score }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $lead->score }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $lead->source_type ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($lead->assignedTo)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 dark:bg-dark-600 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                            {{ strtoupper(substr($lead->assignedTo->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $lead->assignedTo->name }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Atanmamış</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $lead->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.leads.edit', $lead) }}" class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Henüz lead yok</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">İlk leadinizi oluşturarak başlayın.</p>
                            <a href="{{ route('admin.leads.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Yeni Lead Oluştur
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($leads->hasPages())
    <div class="flex justify-center">
        {{ $leads->links() }}
    </div>
    @endif
</div>
@endsection
