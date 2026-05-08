@extends('layouts.admin')

@section('title', 'Aktivite Zaman Çizelgesi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Aktivite Zaman Çizelgesi</h1>
            @if(isset($contact))
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $contact->first_name }} {{ $contact->last_name }}</p>
            @elseif(isset($lead))
            <p class="text-gray-500 dark:text-dark-400 mt-1">Pot. Müşteri #{{ $lead->id }}</p>
            @elseif(isset($deal))
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $deal->title }}</p>
            @endif
        </div>
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
        @forelse($activities ?? [] as $activity)
        @php
        $typeColors = ['call' => 'blue', 'email' => 'green', 'meeting' => 'purple', 'showing' => 'orange', 'note' => 'gray', 'task_completed' => 'teal', 'whatsapp' => 'emerald'];
        $color = $typeColors[$activity->type] ?? 'primary';
        $typeLabels = ['call'=>'Arama','email'=>'E-posta','meeting'=>'Toplantı','showing'=>'Gösterim','note'=>'Not','task_completed'=>'Görev','whatsapp'=>'WhatsApp'];
        @endphp
        <div class="flex gap-4 mb-6 last:mb-0">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 bg-{{ $color }}-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-{{ $color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($activity->type === 'call')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        @elseif($activity->type === 'email')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        @endif
                    </svg>
                </div>
                @if(!$loop->last)
                <div class="w-px flex-1 bg-gray-200 dark:bg-dark-700/50 mt-2"></div>
                @endif
            </div>
            <div class="flex-1 pb-6 last:pb-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-white font-medium text-sm">{{ $activity->subject ?? $typeLabels[$activity->type] ?? $activity->type }}</span>
                    <span class="px-2 py-0.5 bg-{{ $color }}-500/20 text-{{ $color }}-400 text-xs rounded-full">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
                </div>
                @if($activity->description)
                <p class="text-gray-600 dark:text-dark-300 text-sm mb-2">{{ $activity->description }}</p>
                @endif
                @if($activity->outcome)
                <p class="text-gray-500 dark:text-dark-400 text-sm italic">Sonuç: {{ $activity->outcome }}</p>
                @endif
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-dark-500 text-xs">{{ $activity->user->name ?? '-' }}</span>
                    <span class="text-dark-600 text-xs">·</span>
                    <span class="text-dark-500 text-xs">{{ $activity->created_at->format('d.m.Y H:i') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-dark-400">Henüz aktivite kaydı bulunmuyor.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
