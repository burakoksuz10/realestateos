@extends('layouts.admin')

@section('title', 'Otomasyon Kampanyaları')

@php
    $triggerLabels = [
        'lead_created' => 'Yeni Lead',
        'manual'       => 'Manuel',
        'cold_lead'    => 'Pasif Lead',
        'deal_stage'   => 'Anlaşma',
        'custom'       => 'Özel',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Otomasyon Kampanyaları</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Lead yaşam döngüsünü otomatikleştirin — e-posta, SMS, görev hatırlatma
            </p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.campaigns.tick') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm rounded-xl hover:bg-gray-200 dark:hover:bg-dark-600 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Şimdi tick et
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm rounded-xl border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm rounded-xl border border-red-200 dark:border-red-800">
            @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-5 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Toplam Kampanya</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-5 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktif</p>
            <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-800 rounded-2xl p-5 border border-gray-100 dark:border-dark-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktif Enrollment</p>
            <p class="mt-2 text-3xl font-bold text-sky-600 dark:text-sky-400">{{ $stats['active_enrollments'] }}</p>
        </div>
    </div>

    <!-- Campaigns table -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kampanya</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tetikleyici</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Adım</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Enrollment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                            {{ $campaign->name }}
                                        </a>
                                        @if($campaign->is_default)
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                                Varsayılan
                                            </span>
                                        @endif
                                    </div>
                                    @if($campaign->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $campaign->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300">
                                    {{ $triggerLabels[$campaign->trigger] ?? $campaign->trigger }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $campaign->steps_count }} adım
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $campaign->enrollments_count }}
                                <span class="text-xs text-gray-400">/ {{ $campaign->completed_count }} tamam</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($campaign->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400">
                                        Duraklatıldı
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.campaigns.toggle', $campaign) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs px-3 py-1.5 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-dark-600">
                                            {{ $campaign->is_active ? 'Duraklat' : 'Aktifleştir' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-xs px-3 py-1.5 bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 rounded-lg hover:bg-sky-100 dark:hover:bg-sky-900/40">
                                        İncele
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-dark-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Henüz kampanya yok</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Varsayılan onboarding kampanyasını kurmak için:
                                    <code class="text-[11px] bg-gray-100 dark:bg-dark-700 px-1 py-0.5 rounded">php artisan db:seed --class=OnboardingCampaignSeeder</code>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
