@extends('layouts.admin')

@section('title', ($lead->contact->first_name ?? '') . ' ' . ($lead->contact->last_name ?? '') . ' - Potansiyel Müşteri Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lead->contact->first_name ?? '-' }} {{ $lead->contact->last_name ?? '' }}</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Pot. Müşteri #{{ $lead->id }} · {{ ucfirst($lead->status) }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($lead->status !== 'converted' && $lead->status !== 'lost')
            <a href="{{ route('admin.leads.edit', $lead) }}" class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            @endif
            <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Score Bar -->
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-gray-500 dark:text-dark-400 text-sm">Müşteri Skoru</span>
            <div class="flex items-center gap-3">
                @if($lead->ai_score)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-mono">AI: {{ $lead->ai_score }}</span>
                @endif
                <span class="text-gray-900 dark:text-white font-bold text-lg">{{ $lead->score ?? 0 }}/100</span>
            </div>
        </div>
        <div class="w-full bg-gray-200 dark:bg-dark-700 rounded-full h-2.5">
            <div class="h-2.5 rounded-full {{ $lead->score >= 70 ? 'bg-green-500' : ($lead->score >= 40 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $lead->score ?? 0 }}%"></div>
        </div>
        <div class="flex items-center gap-4 mt-3">
            <span class="px-3 py-1 text-xs font-medium rounded-full
                {{ $lead->status === 'new' ? 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400' :
                   ($lead->status === 'qualified' ? 'bg-green-500/20 text-green-400' :
                   ($lead->status === 'lost' ? 'bg-red-500/20 text-red-400' :
                   ($lead->status === 'converted' ? 'bg-purple-500/20 text-purple-400' : 'bg-yellow-500/20 text-yellow-400'))) }}">
                {{ ['new'=>'Yeni','contacted'=>'İletişim','qualified'=>'Nitelikli','proposal'=>'Teklif','negotiation'=>'Müzakere','converted'=>'Dönüştürüldü','lost'=>'Kaybedildi'][$lead->status] ?? $lead->status }}
            </span>
            @if($lead->priority)
            <span class="px-3 py-1 text-xs font-medium rounded-full
                {{ $lead->priority === 'urgent' ? 'bg-red-500/20 text-red-400' :
                   ($lead->priority === 'high' ? 'bg-orange-500/20 text-orange-400' :
                   ($lead->priority === 'medium' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400')) }}">
                {{ ['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'][$lead->priority] ?? $lead->priority }}
            </span>
            @endif
        </div>
    </div>

    @include('crm::leads.partials.ai-analysis-card', ['lead' => $lead])

    @include('crm::leads.partials.call-transcribe-card', ['lead' => $lead])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Activities -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktiviteler</h2>
                @forelse($lead->activities as $activity)
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
                <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz aktivite yok.</p>
                @endforelse
            </div>

            <!-- Interested Listings -->
            @if($lead->interestedListings->count() > 0)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İlgilendiği İlanlar</h2>
                <div class="space-y-3">
                    @foreach($lead->interestedListings as $listing)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.listings.show', $listing) }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium truncate block">
                                {{ $listing->reference_no ? '#' . $listing->reference_no . ' · ' : '' }}{{ $listing->title }}
                            </a>
                            <p class="text-gray-500 dark:text-dark-400 text-xs mt-0.5">
                                {{ ucfirst($listing->type ?? '') }}
                                @if($listing->price)· ₺{{ number_format($listing->price, 0, ',', '.') }}@endif
                            </p>
                        </div>
                        <a href="{{ route('admin.listings.show', $listing) }}" class="ml-3 flex-shrink-0 text-gray-400 hover:text-primary-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tasks -->
            @if($lead->tasks->count() > 0)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bekleyen Görevler</h2>
                @foreach($lead->tasks as $task)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-200 dark:border-dark-700/50 last:border-0">
                    <div>
                        <p class="text-white text-sm">{{ $task->title }}</p>
                        <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d.m.Y') : '-' }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-500/20 text-yellow-400">{{ ucfirst($task->priority) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Deals -->
            @if($lead->deals->count() > 0)
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">İlişkili Fırsatlar</h2>
                @foreach($lead->deals as $deal)
                <div class="flex items-center justify-between py-2.5">
                    <div>
                        <a href="{{ route('admin.deals.show', $deal) }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium">{{ $deal->title }}</a>
                        <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $deal->status }}</p>
                    </div>
                    <span class="text-white font-semibold text-sm">₺{{ number_format($deal->value ?? 0, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact Info -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">İletişim Bilgileri</h2>
                @if($lead->contact)
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($lead->contact->first_name ?? 'L', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $lead->contact->first_name }} {{ $lead->contact->last_name }}</p>
                        @if($lead->contact->phone)
                        <p class="text-gray-500 dark:text-dark-400 text-sm">{{ $lead->contact->phone }}</p>
                        @endif
                    </div>
                </div>
                @if($lead->contact->email)
                <p class="text-gray-500 dark:text-dark-400 text-sm mb-1">{{ $lead->contact->email }}</p>
                @endif
                @endif
            </div>

            <!-- Lead Details -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Talep Detayları</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Kaynak</span>
                        <span class="text-white text-sm">{{ ucfirst($lead->source ?? '-') }}</span>
                    </div>
                    @if($lead->budget_min || $lead->budget_max)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Bütçe</span>
                        <span class="text-white text-sm">
                            {{ $lead->budget_min ? '₺' . number_format($lead->budget_min, 0, ',', '.') : '' }}
                            {{ $lead->budget_min && $lead->budget_max ? ' - ' : '' }}
                            {{ $lead->budget_max ? '₺' . number_format($lead->budget_max, 0, ',', '.') : '' }}
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Atanan</span>
                        <span class="text-white text-sm">{{ $lead->assignedTo->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-dark-400 text-sm">Oluşturulma</span>
                        <span class="text-white text-sm">{{ $lead->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                @if(!in_array($lead->status, ['converted', 'lost']))
                @if(!$lead->is_qualified)
                <form action="{{ route('admin.leads.qualify', $lead) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600/20 hover:bg-green-600/30 text-green-400 font-medium rounded-xl transition-colors">
                        Nitelikli İşaretle
                    </button>
                </form>
                @endif
                <button onclick="document.getElementById('convertModal').classList.remove('hidden')" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                    Fırsata Dönüştür
                </button>
                <button onclick="document.getElementById('lostModal').classList.remove('hidden')" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">
                    Kaybedildi İşaretle
                </button>
                @endif
                <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Bu lead\'i silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-600 dark:text-dark-300 font-medium rounded-xl transition-colors">
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Convert Modal -->
<div id="convertModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700 rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fırsata Dönüştür</h3>
        <form action="{{ route('admin.leads.convert', $lead) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Pipeline *</label>
                <select name="pipeline_id" required class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Seçiniz</option>
                    @foreach(\Modules\CRM\Models\Pipeline::all() as $pipeline)
                    <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Değer (₺)</label>
                <input type="number" name="value" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="0">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Dönüştür</button>
                <button type="button" onclick="document.getElementById('convertModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">İptal</button>
            </div>
        </form>
    </div>
</div>

<!-- Lost Modal -->
<div id="lostModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700 rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kaybedildi İşaretle</h3>
        <form action="{{ route('admin.leads.mark-lost', $lead) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Kayıp Sebebi *</label>
                <textarea name="lost_reason" required rows="3" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Sebep açıklayın..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">İşaretle</button>
                <button type="button" onclick="document.getElementById('lostModal').classList.add('hidden')" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">İptal</button>
            </div>
        </form>
    </div>
</div>
@endsection
