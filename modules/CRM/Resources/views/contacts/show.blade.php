@extends('layouts.admin')

@section('title', $contact->first_name . ' ' . $contact->last_name . ' - Kişi Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $contact->first_name }} {{ $contact->last_name }}</h1>
            <p class="text-dark-400 mt-1">{{ $contact->company_name ?? $contact->email ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.contacts.edit', $contact) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Leads -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Talepler</h2>
                    <a href="{{ route('admin.leads.create') }}" class="text-primary-400 hover:text-primary-300 text-sm">+ Yeni Talep</a>
                </div>
                @forelse($contact->leads ?? [] as $lead)
                <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                    <div>
                        <a href="{{ route('admin.leads.show', $lead) }}" class="text-white text-sm font-medium hover:text-primary-400">
                            {{ $lead->title ?? ($lead->contact->first_name . ' ' . $lead->contact->last_name) }}
                        </a>
                        <p class="text-dark-400 text-xs">{{ $lead->created_at->format('d.m.Y') }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-500/20 text-blue-400">{{ ucfirst($lead->status) }}</span>
                </div>
                @empty
                <p class="text-dark-400 text-sm">Henüz talep kaydı yok.</p>
                @endforelse
            </div>

            <!-- Deals -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fırsatlar</h2>
                @forelse($contact->deals ?? [] as $deal)
                <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                    <div>
                        <a href="{{ route('admin.deals.show', $deal) }}" class="text-white text-sm font-medium hover:text-primary-400">{{ $deal->title }}</a>
                        <p class="text-dark-400 text-xs">₺{{ number_format($deal->value ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $deal->status === 'won' ? 'bg-green-500/20 text-green-400' : ($deal->status === 'lost' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400') }}">
                        {{ ['open'=>'Açık','won'=>'Kazanıldı','lost'=>'Kaybedildi'][$deal->status] ?? $deal->status }}
                    </span>
                </div>
                @empty
                <p class="text-dark-400 text-sm">Henüz fırsat kaydı yok.</p>
                @endforelse
            </div>

            <!-- Activities -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Aktiviteler</h2>
                @forelse($contact->activities ?? [] as $activity)
                <div class="flex gap-3 py-3 border-b border-dark-700/50 last:border-0">
                    <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <div>
                        <p class="text-white text-sm">{{ $activity->subject }}</p>
                        <p class="text-dark-400 text-xs">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-dark-400 text-sm">Henüz aktivite yok.</p>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($contact->first_name ?? 'K', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-white font-semibold">{{ $contact->first_name }} {{ $contact->last_name }}</p>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $contact->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                            {{ $contact->status === 'active' ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                </div>
                <div class="space-y-2">
                    @if($contact->phone)
                    <div class="flex items-center gap-2 text-dark-300 text-sm">
                        <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $contact->phone }}
                    </div>
                    @endif
                    @if($contact->email)
                    <div class="flex items-center gap-2 text-dark-300 text-sm">
                        <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        {{ $contact->email }}
                    </div>
                    @endif
                    @if($contact->city)
                    <div class="flex items-center gap-2 text-dark-300 text-sm">
                        <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        {{ $contact->city }}{{ $contact->district ? ', ' . $contact->district : '' }}
                    </div>
                    @endif
                    <div class="flex justify-between pt-2">
                        <span class="text-dark-400 text-sm">Kaynak</span>
                        <span class="text-white text-sm">{{ ucfirst($contact->source ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Atanan</span>
                        <span class="text-white text-sm">{{ $contact->assignedTo->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Kayıt Tarihi</span>
                        <span class="text-white text-sm">{{ $contact->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.contacts.edit', $contact) }}" class="block w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors text-center">Düzenle</a>
                <form action="{{ route('admin.contacts.toggle-status', $contact) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors">
                        {{ $contact->status === 'active' ? 'Pasife Al' : 'Aktife Al' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
