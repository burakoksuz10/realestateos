@extends('layouts.admin')

@section('title', 'AI Ayarları')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Ayarları & Kullanım</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">OpenAI bağlantısı, kredi limitleri ve aylık kullanım istatistikleri.</p>
        </div>
        <a href="{{ route('admin.ai.copilot.index') }}" class="px-4 py-2 bg-gradient-to-r from-violet-600 to-primary-600 text-white rounded-xl text-sm font-medium shadow-sm">
            Copilot'a Git
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-5">
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Aylık Kredi</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $credit?->remaining() ?? 0 }} / {{ ($credit?->monthly_quota ?? 0) + ($credit?->extra_credits ?? 0) }}</p>
            <div class="mt-3 h-2 rounded-full bg-gray-100 dark:bg-dark-700 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-violet-500 to-primary-500" style="width: {{ $credit?->progressPercent() ?? 0 }}%"></div>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ $credit?->progressPercent() ?? 0 }}% kullanıldı</p>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-5">
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Bu Ay Çağrı</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($monthlyUsage->total_calls ?? 0) }}</p>
            <p class="mt-2 text-xs text-gray-500">Tüm AI çağrıları</p>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-5">
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Toplam Token</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format(($monthlyUsage->prompt ?? 0) + ($monthlyUsage->completion ?? 0)) }}</p>
            <p class="mt-2 text-xs text-gray-500">prompt + completion</p>
        </div>

        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-5">
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Maliyet (USD)</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($monthlyUsage->cost ?? 0, 4) }}</p>
            <p class="mt-2 text-xs text-gray-500">tahmini bu ay</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- OpenAI ayarları --}}
        <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">OpenAI Bağlantısı</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Office'iniz için özel anahtar tanımlayın; boş bırakırsanız .env'deki sistem anahtarı kullanılır.
                        @if($isEnvKey)
                            <span class="text-emerald-600 font-medium">Sistem anahtarı yapılandırılmış ✓</span>
                        @else
                            <span class="text-amber-600 font-medium">Sistem anahtarı yapılandırılmamış</span>
                        @endif
                    </p>
                </div>
                <button onclick="testConnection()" type="button" class="px-3 py-2 bg-gray-100 dark:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-dark-600">
                    Bağlantıyı Test Et
                </button>
            </div>

            <form method="POST" action="{{ route('admin.ai.settings.update') }}" class="p-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">OpenAI API Anahtarı</label>
                    <input type="password" name="openai_key" autocomplete="new-password"
                        placeholder="{{ $setting?->openai_key ? $setting->maskedKey() : 'sk-...' }}"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @if($setting?->openai_key)
                        <p class="mt-1 text-xs text-gray-500">Şu an: <code>{{ $setting->maskedKey() }}</code> — boş bırakırsanız mevcut anahtar değişmez.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Organizasyon (opsiyonel)</label>
                        <input type="text" name="openai_organization" value="{{ $setting?->openai_organization }}"
                            placeholder="org-..."
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tercih Edilen Model</label>
                        <select name="preferred_model" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white">
                            @foreach(['gpt-4o' => 'GPT-4o (önerilen)', 'gpt-4o-mini' => 'GPT-4o mini (hızlı/ucuz)', 'gpt-4-turbo' => 'GPT-4 Turbo'] as $val => $label)
                                <option value="{{ $val }}" @selected(($setting?->preferred_model ?? 'gpt-4o') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Açık Özellikler</label>
                    @php $fe = $setting?->features_enabled ?? ['copilot'=>true,'valuation'=>true,'content'=>true,'matching'=>true,'lead_analysis'=>true]; @endphp
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach(['copilot'=>'AI Copilot Chat','valuation'=>'İlan Değerleme','content'=>'İçerik Üretici','matching'=>'Lead↔İlan Eşleştirme','lead_analysis'=>'Lead AI Skor','call_analysis'=>'Çağrı Analizi'] as $k => $label)
                            <label class="flex items-center space-x-2 px-3 py-2 bg-gray-50 dark:bg-dark-700 rounded-xl cursor-pointer">
                                <input type="checkbox" name="features_enabled[{{ $k }}]" value="1" @checked($fe[$k] ?? true) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Aylık AI Kredisi</label>
                    <input type="number" name="monthly_quota" value="{{ $credit?->monthly_quota ?? 500 }}" min="0"
                        class="w-full md:w-48 px-4 py-2.5 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-xl text-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500">1 kredi = 1 AI çağrısı. Her ay otomatik sıfırlanır.</p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-dark-700">
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-700 text-white rounded-xl text-sm font-medium hover:opacity-90 transition">
                        Kaydet
                    </button>
                    @if($setting?->openai_key)
                        <a href="{{ route('admin.ai.settings.clear-key') }}" onclick="return confirm('Office anahtarı silinsin mi? Sistem .env anahtarına geri dönecek.')" class="text-sm text-red-600 hover:underline">Anahtarı sil</a>
                    @endif
                </div>
            </form>

            <div id="test-result" class="hidden mx-6 mb-6 p-4 rounded-xl text-sm"></div>
        </div>

        {{-- Sağ sütun: özellik bazlı kullanım --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 p-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Özellik Bazlı Kullanım</h3>
                @if($byFeature->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Henüz bu ay AI kullanımı yok.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($byFeature as $row)
                            <li>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $row->feature ?? '—' }}</span>
                                    <span class="text-xs text-gray-500">{{ number_format($row->calls) }} çağrı</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ number_format($row->tokens) }} token</span>
                                    <span>${{ number_format($row->cost, 4) }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.ai.settings.grant-bonus') }}" class="bg-gradient-to-br from-violet-600 to-primary-600 rounded-2xl p-5 text-white">
                @csrf
                <h3 class="text-base font-semibold">Bonus Kredi Ekle</h3>
                <p class="text-sm text-white/70 mt-1">Acil ihtiyaç için ek kredi tanımlayabilirsiniz.</p>
                <div class="mt-3 flex space-x-2">
                    <input type="number" name="amount" value="100" min="1" class="flex-1 px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60">
                    <button type="submit" class="px-3 py-2 bg-white text-violet-700 rounded-lg text-sm font-medium">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function testConnection() {
    const result = document.getElementById('test-result');
    result.className = 'mx-6 mb-6 p-4 rounded-xl text-sm bg-gray-50 dark:bg-dark-700 text-gray-700 dark:text-gray-300';
    result.classList.remove('hidden');
    result.textContent = 'Bağlantı test ediliyor…';

    try {
        const res = await fetch('{{ route("admin.ai.settings.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        result.className = 'mx-6 mb-6 p-4 rounded-xl text-sm ' + (data.ok
            ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300'
            : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300');
        result.textContent = data.message;
    } catch (e) {
        result.className = 'mx-6 mb-6 p-4 rounded-xl text-sm bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300';
        result.textContent = 'Test başarısız: ' + e.message;
    }
}
</script>
@endsection
