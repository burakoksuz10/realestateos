<div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-100 to-purple-100 dark:from-pink-900/30 dark:to-purple-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m-4 0h8m-12-9V8a4 4 0 118 0v3"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Çağrı Özetleme (AI)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Sesli kayıt yükle → otomatik transkript + özet + duygu + alım sinyalleri
                </p>
            </div>
        </div>
        @php
            $sttProvider = config('reos.ai.transcription_provider', 'elevenlabs');
        @endphp
        <span class="text-[10px] px-2 py-1 rounded-full bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400">
            STT: {{ $sttProvider === 'elevenlabs' ? 'ElevenLabs' : 'Whisper' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.calls.transcribe') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <input type="hidden" name="lead_id" value="{{ $lead->id }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ses dosyası (mp3/wav/m4a/ogg)</label>
                <input type="file" name="audio" accept="audio/*"
                       class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-purple-50 dark:file:bg-purple-900/30 file:text-purple-700 dark:file:text-purple-300 hover:file:bg-purple-100 dark:hover:file:bg-purple-900/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">veya kayıt URL'si</label>
                <input type="url" name="recording_url" placeholder="https://..."
                       class="block w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <p class="text-[11px] text-gray-400">
                Max 50MB · GPT modeli ile özet + sentiment + intent + buying signals çıkar
            </p>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl hover:from-purple-600 hover:to-pink-700 text-sm font-medium shadow-sm shadow-purple-500/20">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Özetle
            </button>
        </div>
    </form>
</div>
