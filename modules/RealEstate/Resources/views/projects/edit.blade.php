@extends('layouts.admin')

@section('title', 'Proje Düzenle')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Proje Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $project->name }}</p>
        </div>
        <a href="{{ route('admin.projects.show', $project) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.projects.update', $project) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Proje Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Proje Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="4" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $project->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Geliştirici / İnşaat Firması</label>
                            <input type="text" name="developer" value="{{ old('developer', $project->developer) }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Konum</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İl *</label>
                                <input type="text" name="city" value="{{ old('city', $project->city) }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">İlçe *</label>
                                <input type="text" name="district" value="{{ old('district', $project->district) }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Adres</label>
                            <input type="text" name="address" value="{{ old('address', $project->address) }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Proje Detayları</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Toplam Daire</label>
                                <input type="number" name="total_units" value="{{ old('total_units', $project->total_units) }}" min="1"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Mevcut Daire</label>
                                <input type="number" name="available_units" value="{{ old('available_units', $project->available_units) }}" min="0"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Min. Fiyat (₺)</label>
                                <input type="number" name="min_price" value="{{ old('min_price', $project->min_price) }}" min="0"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Maks. Fiyat (₺)</label>
                                <input type="number" name="max_price" value="{{ old('max_price', $project->max_price) }}" min="0"
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Teslim Tarihi</label>
                            <input type="date" name="delivery_date" value="{{ old('delivery_date', $project->delivery_date ?? ($project->estimated_completion ? \Carbon\Carbon::parse($project->estimated_completion)->format('Y-m-d') : '')) }}"
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Durum</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">İnşaat Durumu *</label>
                            <select name="status" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planlama</option>
                                <option value="under_construction" {{ $project->status === 'under_construction' ? 'selected' : '' }}>İnşaat Aşamasında</option>
                                <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                            </select>
                        </div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $project->is_active ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-dark-300 text-sm">Aktif</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" value="1" {{ $project->is_featured ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-dark-800 border-dark-700 rounded focus:ring-primary-500">
                            <span class="text-dark-300 text-sm">Öne Çıkar</span>
                        </label>
                    </div>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.projects.show', $project) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
