@extends('layouts.admin')
@section('title', 'Form Yönetimi')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Formlar</h1>
            <p class="text-dark-400 mt-1">Web sitesi formlarını ve gönderimleri yönetin</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Form
            </button>
            <a href="{{ route('admin.websites.show', $website ?? 'main') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([
            ['name' => 'İletişim Formu', 'submissions' => 47, 'last' => '2 saat önce', 'active' => true, 'fields' => ['Ad Soyad', 'E-posta', 'Telefon', 'Mesaj']],
            ['name' => 'İlan Talebi', 'submissions' => 23, 'last' => '5 saat önce', 'active' => true, 'fields' => ['Ad Soyad', 'Telefon', 'İlan Tipi', 'Bütçe']],
            ['name' => 'Değerleme Talebi', 'submissions' => 15, 'last' => '1 gün önce', 'active' => true, 'fields' => ['Ad Soyad', 'E-posta', 'Adres', 'Metrekare']],
            ['name' => 'Bülten Kaydı', 'submissions' => 89, 'last' => '3 dakika önce', 'active' => true, 'fields' => ['E-posta']],
            ['name' => 'Randevu Talebi', 'submissions' => 12, 'last' => '3 gün önce', 'active' => false, 'fields' => ['Ad', 'Telefon', 'Tarih']],
        ] as $form)
        <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-white font-semibold">{{ $form['name'] }}</h3>
                    <p class="text-dark-400 text-xs mt-0.5">{{ count($form['fields']) }} alan</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full {{ $form['active'] ? 'bg-green-500/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                    {{ $form['active'] ? 'Aktif' : 'Pasif' }}
                </span>
            </div>

            <div class="flex flex-wrap gap-1.5 mb-4">
                @foreach($form['fields'] as $field)
                <span class="text-xs px-2 py-0.5 bg-dark-800 text-dark-300 rounded-full">{{ $field }}</span>
                @endforeach
            </div>

            <div class="flex items-center justify-between text-sm mb-4">
                <div>
                    <p class="text-2xl font-bold text-white">{{ $form['submissions'] }}</p>
                    <p class="text-dark-400 text-xs">toplam gönderim</p>
                </div>
                <div class="text-right">
                    <p class="text-dark-400 text-xs">Son gönderim</p>
                    <p class="text-white text-xs">{{ $form['last'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="flex-1 px-3 py-2 bg-dark-700 hover:bg-dark-600 text-white text-center rounded-lg text-xs transition-colors">Gönderimleri Gör</button>
                <button class="p-2 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-colors" title="Düzenle">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Submissions -->
    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-dark-700/50">
            <h2 class="text-lg font-semibold text-white">Son Gönderimleri</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700/50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-dark-400 uppercase tracking-wider">Ad Soyad</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-dark-400 uppercase tracking-wider">Form</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-dark-400 uppercase tracking-wider">Telefon</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-dark-400 uppercase tracking-wider">Tarih</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-dark-400 uppercase tracking-wider">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @foreach([
                        ['name' => 'Ahmet Yılmaz', 'form' => 'İletişim Formu', 'phone' => '0532 xxx xxxx', 'date' => '2 saat önce', 'status' => 'new'],
                        ['name' => 'Fatma Kaya', 'form' => 'İlan Talebi', 'phone' => '0543 xxx xxxx', 'date' => '5 saat önce', 'status' => 'contacted'],
                        ['name' => 'Mehmet Demir', 'form' => 'Değerleme Talebi', 'phone' => '0555 xxx xxxx', 'date' => '1 gün önce', 'status' => 'done'],
                        ['name' => 'Ayşe Şahin', 'form' => 'Bülten Kaydı', 'phone' => '-', 'date' => '1 gün önce', 'status' => 'done'],
                        ['name' => 'Ali Çelik', 'form' => 'İletişim Formu', 'phone' => '0542 xxx xxxx', 'date' => '2 gün önce', 'status' => 'contacted'],
                    ] as $sub)
                    <tr class="hover:bg-dark-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-primary-600/20 rounded-full flex items-center justify-center">
                                    <span class="text-primary-400 text-xs">{{ strtoupper(substr($sub['name'], 0, 1)) }}</span>
                                </div>
                                <span class="text-white text-sm">{{ $sub['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-dark-300 text-sm">{{ $sub['form'] }}</td>
                        <td class="px-5 py-4 text-dark-300 text-sm">{{ $sub['phone'] }}</td>
                        <td class="px-5 py-4 text-dark-400 text-sm">{{ $sub['date'] }}</td>
                        <td class="px-5 py-4">
                            @php $statusMap = ['new' => ['bg-blue-500/20 text-blue-400', 'Yeni'], 'contacted' => ['bg-yellow-500/20 text-yellow-400', 'Arandı'], 'done' => ['bg-green-500/20 text-green-400', 'Tamamlandı']]; @endphp
                            <span class="text-xs px-2 py-1 rounded-full {{ $statusMap[$sub['status']][0] }}">{{ $statusMap[$sub['status']][1] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
