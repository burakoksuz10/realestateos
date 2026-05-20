@extends('layouts.admin')

@section('title', 'Telegram Bağlantısı')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Telegram Bağlantısı</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sıcak lead bildirimleri, sabah brifingi ve hızlı komutlar için RE-OS hesabınızı Telegram'a bağlayın.</p>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {!! session('success') !!}
        </div>
    @endif
    @if(session('warning'))
        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-4 py-3 text-sm text-amber-700 dark:text-amber-300">
            {!! session('warning') !!}
        </div>
    @endif

    @if(!$configured)
        <div class="rounded-2xl border-2 border-dashed border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-6">
            <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-300">Bot henüz yapılandırılmamış</h3>
            <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                <code class="bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded">.env</code> dosyasına <code>TELEGRAM_BOT_TOKEN</code> ve <code>TELEGRAM_BOT_USERNAME</code> ekleyin.
                Bot token'ı için Telegram'da <a href="https://t.me/BotFather" target="_blank" class="underline font-medium">@BotFather</a>'a gidip yeni bot oluşturun.
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hesabımı Bağla</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @if($configured)
                    1) "Eşleştirme kodu oluştur" butonuna tıklayın.<br>
                    2) Telegram'da botu açın: <a href="https://t.me/{{ $botUsername }}" target="_blank" class="text-primary-600 underline">@{{ $botUsername ?: 'bot_kullanici_adi' }}</a><br>
                    3) Bot'a şunu yazın: <code>/start KOD</code>
                @else
                    Önce yöneticinizden Telegram bot'u yapılandırmasını isteyin.
                @endif
            </p>

            <form method="POST" action="{{ route('admin.telegram.pair') }}" class="mt-4">
                @csrf
                <button type="submit" {{ !$configured ? 'disabled' : '' }}
                        class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl text-sm font-medium hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Eşleştirme Kodu Oluştur
                </button>
            </form>
        </div>

        <div class="bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl p-6 text-white">
            <h3 class="text-lg font-semibold">Telegram Hub Hakkında</h3>
            <ul class="mt-3 space-y-2 text-sm text-white/90">
                <li>• Yeni lead bildirimi (instant)</li>
                <li>• Hot lead alarmı (skor &gt;80)</li>
                <li>• Sabah 08:30 brifingi</li>
                <li>• /komutlar ile hızlı sorgu</li>
                <li>• Foto/ses → CRM aktivitesi</li>
            </ul>
            <p class="mt-4 text-xs text-white/70">Faz 2'de canlı.</p>
        </div>
    </div>

    {{-- Mevcut bağlantılar --}}
    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700">
        <div class="p-6 border-b border-gray-100 dark:border-dark-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bağlantılarım</h3>
        </div>
        @if($links->isEmpty())
            <div class="p-6 text-sm text-gray-500 dark:text-gray-400">Henüz bağlı hesap yok.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-dark-700">
                @foreach($links as $link)
                    <li class="p-4 flex items-center justify-between">
                        <div>
                            @if($link->is_active)
                                <p class="font-medium text-gray-900 dark:text-white">@ {{ $link->telegram_username ?: ($link->first_name . ' ' . $link->last_name) }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Chat ID: {{ $link->telegram_chat_id }} · Bağlandı: {{ $link->linked_at?->diffForHumans() }}</p>
                            @else
                                <p class="font-medium text-amber-600">Bekleyen eşleştirme</p>
                                <p class="text-xs text-gray-500 mt-0.5">Kod: <code>{{ $link->pairing_code }}</code> · Geçerlilik: {{ $link->pairing_expires_at?->diffForHumans() }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.telegram.unlink', $link->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:underline">Kaldır</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if($configured && $webhook)
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Webhook Durumu</h3>
            <pre class="mt-3 text-xs bg-gray-50 dark:bg-dark-900 p-3 rounded-lg overflow-x-auto">{{ json_encode($webhook, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

            <form method="POST" action="{{ route('admin.telegram.webhook.set') }}" class="mt-4 flex items-center gap-2">
                @csrf
                <input type="url" name="url" required placeholder="https://siteniz.com/api/telegram/webhook"
                       class="flex-1 px-4 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white">
                <button class="px-4 py-2 bg-primary-600 text-white rounded-xl text-sm font-medium">Webhook'u Ayarla</button>
            </form>
            <p class="mt-2 text-xs text-gray-500">Public URL şart (Telegram lokali çağıramaz). Geliştirme için ngrok kullanabilirsiniz.</p>
        </div>
    @endif
</div>
@endsection
