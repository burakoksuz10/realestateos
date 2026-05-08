@extends('layouts.admin')
@section('title', 'Ofis Düzenle')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ofis Düzenle</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">{{ $office->name }}</p>
        </div>
        <a href="{{ route('admin.offices.show', $office) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
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
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Ofis Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Ofis Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $office->name) }}" required
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        @php $locationCities = config('locations.cities', []); @endphp
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İl *</label>
                                <select name="city" id="citySelect" required onchange="updateDistricts(this.value)"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    @foreach(array_keys($locationCities) as $c)
                                        <option value="{{ $c }}" {{ old('city', $office->city) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İlçe</label>
                                <select name="district" id="districtSelect"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Seçiniz</option>
                                    @php $selCity = old('city', $office->city); @endphp
                                    @if($selCity && isset($locationCities[$selCity]))
                                        @foreach($locationCities[$selCity] as $d)
                                            <option value="{{ $d }}" {{ old('district', $office->district) === $d ? 'selected' : '' }}>{{ $d }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <script>
                        var locationData = @json($locationCities);
                        function updateDistricts(city) {
                            var sel = document.getElementById('districtSelect');
                            sel.innerHTML = '<option value="">Seçiniz</option>';
                            (locationData[city] || []).forEach(function(d) {
                                sel.innerHTML += '<option value="' + d + '">' + d + '</option>';
                            });
                        }
                        </script>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Adres</label>
                            <input type="text" name="address" value="{{ old('address', $office->address) }}"
                                class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Telefon</label>
                                <input type="tel" name="phone" value="{{ old('phone', $office->phone) }}"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">E-posta</label>
                                <input type="email" name="email" value="{{ old('email', $office->email) }}"
                                    class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-dark-400 uppercase tracking-wider mb-4">Durum</h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $office->is_active ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                            <span class="text-gray-600 dark:text-dark-300 text-sm">Aktif</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_headquarters" value="1" {{ $office->is_headquarters ? 'checked' : '' }} class="w-4 h-4 text-primary-600 bg-gray-100 dark:bg-dark-800 border-gray-200 dark:border-dark-700 rounded">
                            <span class="text-gray-600 dark:text-dark-300 text-sm">Merkez Ofis</span>
                        </label>
                    </div>
                </div>
                <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.offices.show', $office) }}" class="block w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
