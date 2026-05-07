@extends('layouts.admin')

@section('title', 'Aktivite Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $activity->subject ?? 'Aktivite Detayı' }}</h1>
            <p class="text-dark-400 mt-1">{{ ['call'=>'Arama','email'=>'E-posta','meeting'=>'Toplantı','showing'=>'Gösterim','note'=>'Not','task_completed'=>'Görev Tamamlandı'][$activity->type] ?? ucfirst($activity->type) }} · {{ $activity->created_at->format('d.m.Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Aktivite Bilgileri</h2>
                <div class="space-y-4">
                    @if($activity->description)
                    <div>
                        <p class="text-dark-400 text-xs mb-1">Açıklama</p>
                        <p class="text-white text-sm">{{ $activity->description }}</p>
                    </div>
                    @endif
                    @if($activity->outcome)
                    <div>
                        <p class="text-dark-400 text-xs mb-1">Sonuç</p>
                        <p class="text-white text-sm">{{ $activity->outcome }}</p>
                    </div>
                    @endif
                    @if($activity->type === 'call' && $activity->call_duration)
                    <div class="flex gap-4">
                        <div>
                            <p class="text-dark-400 text-xs mb-1">Süre</p>
                            <p class="text-white text-sm">{{ $activity->call_duration }} dk</p>
                        </div>
                        @if($activity->call_sentiment)
                        <div>
                            <p class="text-dark-400 text-xs mb-1">Duygu</p>
                            <p class="text-sm {{ $activity->call_sentiment === 'positive' ? 'text-green-400' : ($activity->call_sentiment === 'negative' ? 'text-red-400' : 'text-yellow-400') }}">
                                {{ ucfirst($activity->call_sentiment) }}
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif
                    @if($activity->location)
                    <div>
                        <p class="text-dark-400 text-xs mb-1">Konum</p>
                        <p class="text-white text-sm">{{ $activity->location }}</p>
                    </div>
                    @endif
                    @if($activity->ai_summary)
                    <div class="p-4 bg-purple-500/10 border border-purple-500/20 rounded-xl">
                        <p class="text-purple-400 text-xs font-medium mb-2">AI Özeti</p>
                        <p class="text-white text-sm">{{ $activity->ai_summary }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Detaylar</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Kullanıcı</span>
                        <span class="text-white text-sm">{{ $activity->user->name ?? '-' }}</span>
                    </div>
                    @if($activity->contact)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Kişi</span>
                        <a href="{{ route('admin.contacts.show', $activity->contact) }}" class="text-primary-400 hover:text-primary-300 text-sm">{{ $activity->contact->first_name }} {{ $activity->contact->last_name }}</a>
                    </div>
                    @endif
                    @if($activity->lead)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Lead</span>
                        <a href="{{ route('admin.leads.show', $activity->lead) }}" class="text-primary-400 hover:text-primary-300 text-sm">#{{ $activity->lead->id }}</a>
                    </div>
                    @endif
                    @if($activity->deal)
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Satış</span>
                        <a href="{{ route('admin.deals.show', $activity->deal) }}" class="text-primary-400 hover:text-primary-300 text-sm">{{ $activity->deal->title }}</a>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Tarih</span>
                        <span class="text-white text-sm">{{ $activity->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
