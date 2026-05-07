@extends('layouts.admin')
@section('title', 'Ofis Düzenle')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Ofis Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $office->name }}</p>
        </div>
        <a href="{{ route('admin.offices.show', $office) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.offices.update', $office) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Ofis Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Ofis Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $office->name) }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İl *</label>
                                <input type="text" name="city" value="{{ old('city', $office->city) }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlçe</label>
                                <input type="text" name="district" value="{{ old('district', $office->district) }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Adres</label>
                            <input type="text" name="address" value="{{ old('address', $office->address) }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Telefon</label>
                                <input type="tel" name="phone" value="{{ old('phone', $office->phone) }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">E-posta</label>
                                <input type="email" name="email" value="{{ old('email', $office->email) }}"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Durum</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $office->is_active ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded">
                            <span class="text-dark-300 text-sm">Aktif</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_headquarters" value="1" {{ $office->is_headquarters ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded">
                            <span class="text-dark-300 text-sm">Merkez Ofis</span>
                        </label>
                    </div>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.offices.show', $office) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
