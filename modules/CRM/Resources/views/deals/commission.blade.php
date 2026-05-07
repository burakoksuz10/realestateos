@extends('layouts.admin')

@section('title', 'Komisyon Detayı - ' . $deal->title)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Komisyon Detayı</h1>
            <p class="text-dark-400 mt-1">{{ $deal->title }}</p>
        </div>
        <a href="{{ route('admin.deals.show', $deal) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Satışa Dön
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Commission Summary -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-6">Komisyon Özeti</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                        <span class="text-dark-400">Satış Değeri</span>
                        <span class="text-white font-semibold text-lg">₺{{ number_format($deal->value ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                        <span class="text-dark-400">Komisyon Oranı</span>
                        <span class="text-white">
                            @if($deal->commission_type === 'percentage')
                            %{{ $deal->commission_rate ?? 0 }}
                            @else
                            Sabit
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                        <span class="text-dark-400">Toplam Komisyon</span>
                        <span class="text-primary-400 font-bold text-xl">₺{{ number_format($deal->commission_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if($deal->is_partner_deal)
                    <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                        <span class="text-dark-400">Ortak Ofis Payı</span>
                        <span class="text-yellow-400">
                            @if($deal->partner_commission_split)
                            ₺{{ number_format(($deal->commission_amount ?? 0) * (($deal->partner_commission_split['partner'] ?? 50) / 100), 0, ',', '.') }}
                            @else -
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center py-3">
                        <span class="text-white font-medium">Net Komisyon (Ofis)</span>
                        <span class="text-green-400 font-bold text-2xl">
                            ₺{{ number_format($deal->commission_amount ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($deal->is_partner_deal)
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Ortak Satış Bilgileri</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Ortak Ofis</span>
                        <span class="text-white text-sm">{{ $deal->partnerOffice->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Ortak Danışman</span>
                        <span class="text-white text-sm">{{ $deal->partnerAgent->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Satış Durumu</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Durum</span>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $deal->status === 'won' ? 'bg-green-500/20 text-green-400' : ($deal->status === 'lost' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400') }}">
                            {{ ['open'=>'Açık','won'=>'Kazanıldı','lost'=>'Kaybedildi'][$deal->status] ?? $deal->status }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Danışman</span>
                        <span class="text-white text-sm">{{ $deal->assignedTo->name ?? '-' }}</span>
                    </div>
                    @if($deal->actual_close_date)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Kapanış Tarihi</span>
                        <span class="text-white text-sm">{{ \Carbon\Carbon::parse($deal->actual_close_date)->format('d.m.Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <a href="{{ route('admin.deals.show', $deal) }}" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors block text-center">
                    Satışa Git
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
