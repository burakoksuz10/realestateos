@extends('layouts.admin')

@section('title', 'Lead Düzenle')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Lead Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $lead->contact->first_name ?? '' }} {{ $lead->contact->last_name ?? '' }}</p>
        </div>
        <a href="{{ route('admin.leads.show', $lead) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.leads.update', $lead) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact Info (read-only) -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">İletişim Bilgileri</h2>
                    <div class="flex items-center gap-3 p-3 bg-dark-800/50 rounded-xl">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr($lead->contact->first_name ?? 'L', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $lead->contact->first_name ?? '' }} {{ $lead->contact->last_name ?? '' }}</p>
                            <p class="text-dark-400 text-sm">{{ $lead->contact->phone ?? $lead->contact->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Lead Details -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Talep Detayları</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlgi Alanı</label>
                                <select name="interest_type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="buy" {{ $lead->interest_type === 'buy' ? 'selected' : '' }}>Satın Alma</option>
                                    <option value="rent" {{ $lead->interest_type === 'rent' ? 'selected' : '' }}>Kiralama</option>
                                    <option value="sell" {{ $lead->interest_type === 'sell' ? 'selected' : '' }}>Satış</option>
                                    <option value="invest" {{ $lead->interest_type === 'invest' ? 'selected' : '' }}>Yatırım</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Gayrimenkul Tipi</label>
                                <select name="property_type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    <option value="apartment" {{ $lead->property_type === 'apartment' ? 'selected' : '' }}>Daire</option>
                                    <option value="house" {{ $lead->property_type === 'house' ? 'selected' : '' }}>Müstakil Ev</option>
                                    <option value="villa" {{ $lead->property_type === 'villa' ? 'selected' : '' }}>Villa</option>
                                    <option value="land" {{ $lead->property_type === 'land' ? 'selected' : '' }}>Arsa</option>
                                    <option value="commercial" {{ $lead->property_type === 'commercial' ? 'selected' : '' }}>Ticari</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Bütçe (Min) ₺</label>
                                <input type="number" name="budget_min" value="{{ old('budget_min', $lead->budget_min) }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Bütçe (Max) ₺</label>
                                <input type="number" name="budget_max" value="{{ old('budget_max', $lead->budget_max) }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Skor (0-100)</label>
                            <input type="number" name="score" value="{{ old('score', $lead->score) }}" min="0" max="100"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Notlar</label>
                            <textarea name="notes" rows="4" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white placeholder-dark-400 focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('notes', $lead->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Durum & Atama</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Durum *</label>
                            <select name="status" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['new'=>'Yeni','contacted'=>'İletişime Geçildi','qualified'=>'Nitelikli','proposal'=>'Teklif','negotiation'=>'Müzakere'] as $val => $label)
                                <option value="{{ $val }}" {{ $lead->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Öncelik</label>
                            <select name="priority" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'] as $val => $label)
                                <option value="{{ $val }}" {{ $lead->priority === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Atanan Danışman *</label>
                            <select name="assigned_to" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                        Güncelle
                    </button>
                    <a href="{{ route('admin.leads.show', $lead) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">
                        İptal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
