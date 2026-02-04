@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header')
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hoş Geldiniz, {{ auth()->user()->name }}! 👋</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">İşte bugünkü özet ve önemli metrikleriniz.</p>
    </div>
    <div class="mt-4 lg:mt-0 flex items-center space-x-3">
        <select class="px-4 py-2 text-sm bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option>Son 7 Gün</option>
            <option>Son 30 Gün</option>
            <option>Bu Ay</option>
            <option>Bu Yıl</option>
        </select>
        <button class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors shadow-lg shadow-primary-600/30">
            <svg class="w-4 h-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Rapor İndir
        </button>
    </div>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Leads Card -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam Lead</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_leads']) }}</p>
                <div class="flex items-center mt-2">
                    <span class="text-sm font-medium {{ $stats['new_leads_today'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                        +{{ $stats['new_leads_today'] }} bugün
                    </span>
                </div>
            </div>
            <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-red-500 font-medium">{{ $stats['hot_leads'] }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">sıcak lead</span>
        </div>
    </div>

    <!-- Deals Card -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Satışlar</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_deals']) }}</p>
                <div class="flex items-center mt-2">
                    <span class="text-sm font-medium text-green-600">
                        +{{ $stats['deals_this_month'] }} bu ay
                    </span>
                </div>
            </div>
            <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Dönüşüm Oranı</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $stats['conversion_rate'] }}%</span>
            </div>
            <div class="mt-2 w-full bg-gray-200 dark:bg-dark-700 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($stats['conversion_rate'], 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam Ciro</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">₺{{ number_format($stats['deals_value'], 0, ',', '.') }}</p>
                <div class="flex items-center mt-2">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-sm font-medium text-green-600">+12.5%</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">geçen aya göre</span>
                </div>
            </div>
            <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Listings Card -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Aktif İlanlar</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['active_listings']) }}</p>
                <div class="flex items-center mt-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ number_format($stats['listings_views']) }} görüntülenme
                    </span>
                </div>
            </div>
            <div class="w-14 h-14 bg-orange-100 dark:bg-orange-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-7 h-7 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-orange-500 font-medium">{{ $stats['pending_tasks'] }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">bekleyen görev</span>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Pipeline Chart -->
    <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Satış Pipeline</h2>
            <a href="{{ route('admin.deals.kanban') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                Kanban Görünümü →
            </a>
        </div>
        <div class="space-y-4">
            @foreach($pipelineData as $stage)
            <div class="flex items-center">
                <div class="w-32 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $stage['name'] }}</div>
                <div class="flex-1 mx-4">
                    <div class="w-full bg-gray-200 dark:bg-dark-700 rounded-full h-8 relative overflow-hidden">
                        <div class="h-8 rounded-full flex items-center justify-end pr-3 transition-all duration-500" 
                             style="width: {{ max(($stage['count'] / max(array_column($pipelineData, 'count'))) * 100, 10) }}%; background-color: {{ $stage['color'] ?? '#0ea5e9' }}">
                            <span class="text-xs font-medium text-white">{{ $stage['count'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="w-28 text-right text-sm font-medium text-gray-900 dark:text-white">
                    ₺{{ number_format($stage['value'], 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Yaklaşan Görevler</h2>
            <a href="{{ route('admin.tasks.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                Tümü →
            </a>
        </div>
        <div class="space-y-4">
            @forelse($upcomingTasks as $task)
            <div class="flex items-start space-x-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors cursor-pointer">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                    {{ $task->priority === 'urgent' ? 'bg-red-100 dark:bg-red-900/30' : 
                       ($task->priority === 'high' ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-gray-100 dark:bg-dark-700') }}">
                    <svg class="w-5 h-5 {{ $task->priority === 'urgent' ? 'text-red-600' : 
                                          ($task->priority === 'high' ? 'text-orange-600' : 'text-gray-600') }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $task->title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $task->due_date->format('d M, H:i') }}
                        @if($task->contact)
                            • {{ $task->contact->full_name }}
                        @endif
                    </p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-lg
                    {{ $task->is_overdue ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 
                       ($task->is_due_today ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                       'bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-400') }}">
                    {{ $task->is_overdue ? 'Gecikmiş' : ($task->is_due_today ? 'Bugün' : $task->due_date->diffForHumans()) }}
                </span>
            </div>
            @empty
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yaklaşan görev yok</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Activity & Hot Leads -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Activity -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Son Aktiviteler</h2>
        </div>
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                    {{ $activity->type_color === 'blue' ? 'bg-blue-100 dark:bg-blue-900/30' : 
                       ($activity->type_color === 'green' ? 'bg-green-100 dark:bg-green-900/30' : 
                       ($activity->type_color === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-gray-100 dark:bg-dark-700')) }}">
                    <svg class="w-4 h-4 {{ $activity->type_color === 'blue' ? 'text-blue-600' : 
                                          ($activity->type_color === 'green' ? 'text-green-600' : 
                                          ($activity->type_color === 'purple' ? 'text-purple-600' : 'text-gray-600')) }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if($activity->type === 'call')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        @elseif($activity->type === 'email')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        @elseif($activity->type === 'showing')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 dark:text-white">
                        <span class="font-medium">{{ $activity->user->name }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $activity->subject }}</span>
                    </p>
                    @if($activity->contact)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $activity->contact->full_name }}</p>
                    @endif
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Henüz aktivite yok</p>
            @endforelse
        </div>
    </div>

    <!-- AI Copilot Suggestions -->
    <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-6 shadow-lg text-white">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">AI Copilot</h2>
                    <p class="text-sm text-white/70">Bugünkü öneriler</p>
                </div>
            </div>
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
            </span>
        </div>

        <div class="space-y-4">
            <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium">3 sıcak lead takip bekliyor</p>
                        <p class="text-xs text-white/70 mt-1">Son 24 saatte yanıt verilmedi</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium">5 ilan fiyat güncellemesi önerisi</p>
                        <p class="text-xs text-white/70 mt-1">Piyasa değerinin üzerinde fiyatlandırılmış</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 backdrop-blur rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium">12 yeni eşleşme bulundu</p>
                        <p class="text-xs text-white/70 mt-1">Müşteri kriterleriyle uyumlu ilanlar</p>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.ai.copilot.index') }}" 
           class="mt-6 w-full inline-flex items-center justify-center px-4 py-3 bg-white text-primary-600 font-medium rounded-xl hover:bg-white/90 transition-colors">
            AI Copilot'u Aç
            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz'],
                datasets: [{
                    label: 'Ciro',
                    data: [12000000, 19000000, 15000000, 25000000, 22000000, 30000000],
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₺' + (value / 1000000) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
