@extends('layouts.admin')
@section('title', 'Takım Düzenle')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Takım Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $team->name }}</p>
        </div>
        <a href="{{ route('admin.teams.show', $team) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.teams.update', $team) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Takım Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Takım Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $team->name) }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $team->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Lider</label>
                            <select name="leader_id" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Lider Seçin</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}" {{ old('leader_id', $team->leader_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Ofis</label>
                            <select name="office_id" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">Ofis Seçin</option>
                                @foreach(\Modules\Core\Models\Office::orderBy('name')->get() as $office)
                                <option value="{{ $office->id }}" {{ old('office_id', $team->office_id) == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $team->is_active ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded">
                            <span class="text-dark-300 text-sm">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Takım Özeti</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-dark-400">Üye Sayısı</span><span class="text-white font-semibold">{{ $team->users()->count() }}</span></div>
                    </div>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.teams.show', $team) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
