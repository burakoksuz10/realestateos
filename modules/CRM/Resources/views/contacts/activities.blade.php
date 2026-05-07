@extends('layouts.admin')
@section('title', $contact->full_name . ' - Aktiviteler')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Aktivite Geçmişi</h1>
            <p class="text-dark-400 mt-1">{{ $contact->full_name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.activities.create', ['contact_id' => $contact->id]) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Aktivite Ekle
            </a>
            <a href="{{ route('admin.contacts.show', $contact) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-dark-700/50 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">Tüm Aktiviteler</h2>
                    <span class="text-sm text-dark-400">{{ $activities->total() }} kayıt</span>
                </div>
                <div class="divide-y divide-dark-700/50">
                    @forelse($activities as $activity)
                    @php
                        $typeColors = [
                            'call' => 'bg-blue-500/20 text-blue-400',
                            'email' => 'bg-purple-500/20 text-purple-400',
                            'meeting' => 'bg-green-500/20 text-green-400',
                            'note' => 'bg-yellow-500/20 text-yellow-400',
                            'task' => 'bg-orange-500/20 text-orange-400',
                        ];
                        $typeLabels = [
                            'call' => 'Arama',
                            'email' => 'E-posta',
                            'meeting' => 'Toplantı',
                            'note' => 'Not',
                            'task' => 'Görev',
                        ];
                        $typeIcons = [
                            'call' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                            'email' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                            'meeting' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                            'note' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                            'task' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                        ];
                        $colorClass = $typeColors[$activity->type] ?? 'bg-dark-700 text-dark-300';
                        $icon = $typeIcons[$activity->type] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                    @endphp
                    <div class="flex items-start gap-4 p-5">
                        <div class="w-9 h-9 rounded-full {{ $colorClass }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-white font-medium">{{ $activity->title }}</p>
                                    @if($activity->description)
                                    <p class="text-dark-400 text-sm mt-1">{{ $activity->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $colorClass }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
                                        @if($activity->assignedTo)
                                        <span class="text-xs text-dark-500">{{ $activity->assignedTo->name }}</span>
                                        @endif
                                        @if($activity->due_date)
                                        <span class="text-xs text-dark-500">{{ $activity->due_date->format('d.m.Y H:i') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs text-dark-500">{{ $activity->created_at->format('d.m.Y') }}</p>
                                    <p class="text-xs text-dark-600">{{ $activity->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center">
                        <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-dark-400 text-sm">Henüz aktivite kaydı bulunmuyor.</p>
                    </div>
                    @endforelse
                </div>
                @if($activities->hasPages())
                <div class="p-4 border-t border-dark-700/50">
                    {{ $activities->links() }}
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Özet</h3>
                <div class="space-y-3">
                    @foreach(['call' => 'Arama', 'email' => 'E-posta', 'meeting' => 'Toplantı', 'note' => 'Not', 'task' => 'Görev'] as $type => $label)
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400 text-sm">{{ $label }}</span>
                        <span class="text-white font-semibold text-sm">{{ $activities->where('type', $type)->count() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
                <h3 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Hızlı İşlemler</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.contacts.show', $contact) }}" class="flex items-center gap-2 text-sm text-dark-300 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Kişi Detayı
                    </a>
                    <a href="{{ route('admin.contacts.edit', $contact) }}" class="flex items-center gap-2 text-sm text-dark-300 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Kişiyi Düzenle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
