@extends('layouts.admin')

@section('title', $deal->title . ' - Satış Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $deal->title }}</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Satış #{{ $deal->id }}
                <span class="mx-1">·</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $deal->status === 'won' ? 'bg-green-500/20 text-green-400' : ($deal->status === 'lost' ? 'bg-red-500/20 text-red-400' : 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400') }}">
                    {{ ['open'=>'Açık','won'=>'Kazanıldı','lost'=>'Kaybedildi'][$deal->status] ?? $deal->status }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($deal->status === 'open')
            <a href="{{ route('admin.deals.edit', $deal) }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            @endif
            <a href="{{ route('admin.deals.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Value Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">Satış Değeri</p>
            <p class="text-3xl font-bold text-white">₺{{ number_format($deal->value ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">Olasılık</p>
            <p class="text-3xl font-bold text-white">%{{ $deal->probability ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">Komisyon</p>
            <p class="text-3xl font-bold text-primary-400">₺{{ number_format($deal->commission_amount ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Activities Timeline -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivite Geçmişi</h2>
                @forelse($deal->activities ?? [] as $activity)
                <div class="flex gap-3 py-3 border-b border-gray-200 dark:border-dark-700/50 last:border-0">
                    <div class="w-8 h-8 bg-primary-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-white text-sm font-medium">{{ $activity->subject }}</p>
                        <p class="text-gray-500 dark:text-dark-400 text-xs mt-0.5">{{ $activity->user->name ?? '-' }} · {{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz aktivite kaydı yok.</p>
                @endforelse
            </div>

            <!-- Tasks -->
            @if(($deal->tasks ?? collect())->count() > 0)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Görevler</h2>
                @foreach($deal->tasks as $task)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-200 dark:border-dark-700/50 last:border-0">
                    <p class="text-white text-sm">{{ $task->title }}</p>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $task->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                        {{ $task->status === 'completed' ? 'Tamamlandı' : 'Bekliyor' }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Satış Bilgileri</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Pipeline</span>
                        <span class="text-white text-sm">{{ $deal->pipeline->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Aşama</span>
                        <span class="text-white text-sm">{{ $deal->stage->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Atanan</span>
                        <span class="text-white text-sm">{{ $deal->assignedTo->name ?? '-' }}</span>
                    </div>
                    @if($deal->expected_close_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Kapanış Tarihi</span>
                        <span class="text-white text-sm">{{ \Carbon\Carbon::parse($deal->expected_close_date)->format('d.m.Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Oluşturulma</span>
                        <span class="text-white text-sm">{{ $deal->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
            </div>

            @if($deal->contact)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Müşteri</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($deal->contact->first_name ?? 'M', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium text-sm">{{ $deal->contact->first_name }} {{ $deal->contact->last_name }}</p>
                        <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $deal->contact->phone ?? $deal->contact->email ?? '-' }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($deal->status === 'open')
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                <button onclick="document.getElementById('wonModal').classList.remove('hidden')" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">
                    Kazanıldı İşaretle
                </button>
                <button onclick="document.getElementById('lostModal').classList.remove('hidden')" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">
                    Kaybedildi İşaretle
                </button>
                <a href="{{ route('admin.deals.commission', $deal) }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors text-center">
                    Komisyon Detayı
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Won Modal -->
<div id="wonModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700 rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kazanıldı İşaretle</h3>
        <form action="{{ route('admin.deals.mark-won', $deal) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kazanma Sebebi</label>
                <textarea name="won_reason" rows="3" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Kısa bir açıklama..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                <button type="button" onclick="document.getElementById('wonModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">İptal</button>
            </div>
        </form>
    </div>
</div>

<!-- Lost Modal -->
<div id="lostModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700 rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kaybedildi İşaretle</h3>
        <form action="{{ route('admin.deals.mark-lost', $deal) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kayıp Sebebi *</label>
                <textarea name="lost_reason" required rows="3" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Sebep açıklayın..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">Kaydet</button>
                <button type="button" onclick="document.getElementById('lostModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">İptal</button>
            </div>
        </form>
    </div>

    @include('crm::partials.documents-card', ['documentableType' => 'deal', 'documentableId' => $deal->id])
</div>
@endsection
