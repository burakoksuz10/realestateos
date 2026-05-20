@php
    $ai = is_array($lead->ai_analysis) ? $lead->ai_analysis : [];
    $suggestions = is_array($lead->ai_suggestions) ? $lead->ai_suggestions : [];
    $tempMap = [
        'hot'  => ['Sıcak', 'bg-red-500/20 text-red-400 border-red-500/30'],
        'warm' => ['Ilık',  'bg-orange-500/20 text-orange-400 border-orange-500/30'],
        'cold' => ['Soğuk', 'bg-sky-500/20 text-sky-400 border-sky-500/30'],
    ];
    $temp = $ai['temperature'] ?? null;
    $tempBadge = $tempMap[$temp] ?? null;
    $intentMap = ['buy'=>'Satın Alma','rent'=>'Kiralama','sell'=>'Satış','invest'=>'Yatırım','info'=>'Bilgi'];
    $urgencyMap = ['immediate'=>'Acil','soon'=>'Yakın','exploring'=>'Araştırıyor'];
    $actionMeta = [
        'call'     => ['Telefon',    'M3 5a2 2 0 012-2h2.28a2 2 0 011.94 1.515l.79 3.16a2 2 0 01-1.06 2.32l-1.93.967a11.042 11.042 0 005.516 5.516l.967-1.93a2 2 0 012.32-1.06l3.16.79A2 2 0 0121 18.72V21a2 2 0 01-2 2h-1C9.716 23 1 14.284 1 4V3z'],
        'whatsapp' => ['WhatsApp',   'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        'email'    => ['E-posta',    'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        'meeting'  => ['Toplantı',   'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
    ];
@endphp

<div id="ai-analysis-card" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Analizi</h2>
                <p class="text-xs text-gray-500 dark:text-dark-400">OpenAI tarafından oluşturuldu</p>
            </div>
        </div>
        <button type="button"
                onclick="reanalyzeLead({{ $lead->id }}, this)"
                class="text-xs text-gray-500 dark:text-dark-400 hover:text-purple-400 flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span data-reanalyze-label>Yeniden Analiz Et</span>
        </button>
    </div>

    @if(empty($ai))
        <div class="text-center py-8 px-4 border-2 border-dashed border-gray-200 dark:border-dark-700 rounded-xl">
            <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <p class="text-sm text-gray-600 dark:text-dark-300 mb-1">AI Analizi henüz hazır değil.</p>
            <p class="text-xs text-gray-500 dark:text-dark-400 mb-4">Yeni lead'ler için arka planda otomatik çalışır. Mevcut lead için manuel başlatabilirsiniz.</p>
            <button type="button"
                    onclick="reanalyzeLead({{ $lead->id }}, this)"
                    class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-medium rounded-xl transition-colors">
                Şimdi Analiz Et
            </button>
        </div>
    @else
        @if(!empty($ai['summary_tr']))
            <p class="text-gray-700 dark:text-dark-200 leading-relaxed mb-4">{{ $ai['summary_tr'] }}</p>
        @endif

        <div class="flex flex-wrap gap-2 mb-5">
            @if($tempBadge)
                <span class="px-3 py-1 rounded-full text-xs font-medium border {{ $tempBadge[1] }}">
                    {{ $tempBadge[0] }} Müşteri
                </span>
            @endif
            @if(!empty($ai['intent']))
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                    Niyet: {{ $intentMap[$ai['intent']] ?? $ai['intent'] }}
                </span>
            @endif
            @if(!empty($ai['urgency']))
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    Aciliyet: {{ $urgencyMap[$ai['urgency']] ?? $ai['urgency'] }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @if(!empty($ai['strengths']))
                <div>
                    <h3 class="text-sm font-semibold text-green-400 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Güçlü Yönler
                    </h3>
                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-dark-300">
                        @foreach($ai['strengths'] as $s)
                            <li class="flex items-start gap-2"><span class="text-green-500 mt-0.5">•</span><span>{{ $s }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($ai['risks']))
                <div>
                    <h3 class="text-sm font-semibold text-red-400 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Riskler
                    </h3>
                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-dark-300">
                        @foreach($ai['risks'] as $r)
                            <li class="flex items-start gap-2"><span class="text-red-500 mt-0.5">•</span><span>{{ $r }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($ai['intent_signals']))
                <div class="md:col-span-2">
                    <h3 class="text-sm font-semibold text-purple-400 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Niyet Sinyalleri
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ai['intent_signals'] as $sig)
                            <span class="px-2.5 py-1 rounded-md bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs">{{ $sig }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if(!empty($suggestions))
            <div class="mt-5 pt-5 border-t border-gray-200 dark:border-dark-700/50">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Önerilen Sonraki Adımlar
                </h3>
                <div class="space-y-2">
                    @foreach($suggestions as $action)
                        @php
                            $key = strtolower($action['action'] ?? 'call');
                            $meta = $actionMeta[$key] ?? ['Aksiyon', 'M5 13l4 4L19 7'];
                            $prio = $action['priority'] ?? 'medium';
                            $prioClass = match($prio) {
                                'high'   => 'bg-red-500/20 text-red-400',
                                'medium' => 'bg-yellow-500/20 text-yellow-400',
                                default  => 'bg-gray-500/20 text-gray-400',
                            };
                        @endphp
                        <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                            <span class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta[1] }}"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                    <span class="text-gray-900 dark:text-white text-sm font-medium">{{ $action['title'] ?? $meta[0] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] uppercase tracking-wide {{ $prioClass }}">{{ $prio }}</span>
                                    <span class="text-[10px] text-gray-500 dark:text-dark-400 uppercase">· {{ $meta[0] }}</span>
                                </div>
                                @if(!empty($action['reason']))
                                    <p class="text-gray-500 dark:text-dark-400 text-xs">{{ $action['reason'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

<script>
function reanalyzeLead(leadId, btn) {
    const label = btn.querySelector('[data-reanalyze-label]');
    const originalLabel = label ? label.textContent : btn.textContent;
    const setLabel = (txt) => { if (label) label.textContent = txt; else btn.textContent = txt; };

    btn.disabled = true;
    setLabel('Analiz ediliyor...');

    fetch(`/admin/leads/${leadId}/reanalyze`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json().then(data => ({ok: r.ok, data})))
    .then(({ok, data}) => {
        if (!ok) {
            alert(data.message || 'AI analizi başlatılamadı.');
            btn.disabled = false;
            setLabel(originalLabel);
            return;
        }
        setLabel('Sayfa 4 saniyede yenilenecek...');
        setTimeout(() => window.location.reload(), 4000);
    })
    .catch((e) => {
        alert('Bir hata oluştu: ' + e.message);
        btn.disabled = false;
        setLabel(originalLabel);
    });
}
</script>
