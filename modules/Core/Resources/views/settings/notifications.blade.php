@extends('layouts.admin')

@section('title', 'Bildirim Ayarları')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Bildirim Ayarları</h1>
            <p class="text-dark-400 mt-1">Bildirim tercihlerinizi yönetin</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/20 border border-green-500/30 rounded-xl text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Email Notifications -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">E-posta Bildirimleri</h2>
                    <div class="space-y-4">
                        @php
                        $items = [
                            ['key' => 'email_new_lead', 'label' => 'Yeni Talep', 'desc' => 'Yeni bir talep geldiğinde'],
                            ['key' => 'email_lead_assigned', 'label' => 'Talep Atandı', 'desc' => 'Size bir talep atandığında'],
                            ['key' => 'email_deal_won', 'label' => 'Satış Kazanıldı', 'desc' => 'Bir satış kazanıldığında'],
                            ['key' => 'email_task_reminder', 'label' => 'Görev Hatırlatıcı', 'desc' => 'Görev vadesi yaklaştığında'],
                            ['key' => 'email_daily_summary', 'label' => 'Günlük Özet', 'desc' => 'Her sabah günlük özet'],
                        ];
                        @endphp
                        @foreach($items as $item)
                        <div class="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl">
                            <div>
                                <p class="text-white font-medium text-sm">{{ $item['label'] }}</p>
                                <p class="text-dark-400 text-xs">{{ $item['desc'] }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[{{ $item['key'] }}]" value="1" checked class="sr-only peer">
                                <div class="w-10 h-6 bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- In-App Notifications -->
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Uygulama İçi Bildirimler</h2>
                    <div class="space-y-4">
                        @php
                        $appItems = [
                            ['key' => 'app_new_lead', 'label' => 'Yeni Talep', 'desc' => 'Panel içi anlık bildirim'],
                            ['key' => 'app_lead_activity', 'label' => 'Talep Aktivitesi', 'desc' => 'Talep güncellendiğinde'],
                            ['key' => 'app_deal_update', 'label' => 'Satış Güncellemesi', 'desc' => 'Satış aşaması değiştiğinde'],
                            ['key' => 'app_mention', 'label' => 'Etiketleme', 'desc' => 'Notta etiketlendiğinizde'],
                        ];
                        @endphp
                        @foreach($appItems as $item)
                        <div class="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl">
                            <div>
                                <p class="text-white font-medium text-sm">{{ $item['label'] }}</p>
                                <p class="text-dark-400 text-xs">{{ $item['desc'] }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notifications[{{ $item['key'] }}]" value="1" checked class="sr-only peer">
                                <div class="w-10 h-6 bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-sm font-medium text-dark-400 uppercase tracking-wider mb-4">Ayarlar Menüsü</h2>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Genel</a>
                        <a href="{{ route('admin.settings.notifications') }}" class="flex items-center px-3 py-2 text-sm text-white bg-dark-800 rounded-lg font-medium">Bildirimler</a>
                        <a href="{{ route('admin.settings.integrations') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Entegrasyonlar</a>
                        <a href="{{ route('admin.settings.billing') }}" class="flex items-center px-3 py-2 text-sm text-dark-300 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">Faturalama</a>
                    </nav>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                        Kaydet
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
