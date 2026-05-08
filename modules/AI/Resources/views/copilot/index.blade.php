@extends('layouts.admin')

@section('title', 'AI Copilot')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Copilot</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Yapay zeka destekli asistanınız</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Area -->
        <div class="lg:col-span-2 bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-dark-700 bg-gradient-to-r from-violet-600 to-primary-600">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">ReCRM AI Copilot</h3>
                        <p class="text-white/70 text-sm">Size nasıl yardımcı olabilirim?</p>
                    </div>
                </div>
            </div>
            
            <div class="h-96 overflow-y-auto p-4 space-y-4" id="chat-messages">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="bg-gray-100 dark:bg-dark-700 rounded-2xl rounded-tl-none p-4 max-w-[80%]">
                        <p class="text-gray-700 dark:text-gray-300">Merhaba! Ben ReCRM AI Copilot. Size şu konularda yardımcı olabilirim:</p>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <li>• Potansiyel müşteri ve müşteri yönetimi</li>
                            <li>• İlan açıklaması oluşturma</li>
                            <li>• Fiyat değerleme önerileri</li>
                            <li>• Görev ve hatırlatıcı oluşturma</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-100 dark:border-dark-700">
                <form id="chat-form" class="flex items-center space-x-3">
                    <input type="text" id="chat-input" placeholder="Bir şey sorun..." class="flex-1 px-4 py-3 bg-gray-100 dark:bg-dark-700 border-0 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <button type="submit" class="px-4 py-3 bg-gradient-to-r from-sky-400 to-blue-600 text-white rounded-xl hover:from-sky-500 hover:to-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hızlı İşlemler</h3>
                <div class="space-y-3">
                    <button class="w-full flex items-center space-x-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors text-left">
                        <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Bugünkü Görevler</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Görevlerimi listele</p>
                        </div>
                    </button>
                    <button class="w-full flex items-center space-x-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors text-left">
                        <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Yeni Potansiyel Müşteriler</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Son potansiyel müşterileri göster</p>
                        </div>
                    </button>
                    <button class="w-full flex items-center space-x-3 p-3 bg-gray-50 dark:bg-dark-700 rounded-xl hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors text-left">
                        <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Performans Raporu</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Aylık özet</p>
                        </div>
                    </button>
                </div>
            </div>

            <div class="bg-gradient-to-br from-violet-600 to-primary-600 rounded-2xl p-6 text-white">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold">AI Önerileri</h4>
                        <p class="text-sm text-white/70">Bugün için</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="flex items-center space-x-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span>3 potansiyel müşteri takip bekliyor</span>
                    </p>
                    <p class="flex items-center space-x-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span>2 görev bugün bitiyor</span>
                    </p>
                    <p class="flex items-center space-x-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span>1 fırsat kapanmaya yakın</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;
    
    const messagesDiv = document.getElementById('chat-messages');
    
    // Add user message
    messagesDiv.innerHTML += `
        <div class="flex items-start space-x-3 justify-end">
            <div class="bg-primary-600 rounded-2xl rounded-tr-none p-4 max-w-[80%]">
                <p class="text-white">${message}</p>
            </div>
        </div>
    `;
    
    input.value = '';
    
    // Add AI response (placeholder)
    setTimeout(() => {
        messagesDiv.innerHTML += `
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-900/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="bg-gray-100 dark:bg-dark-700 rounded-2xl rounded-tl-none p-4 max-w-[80%]">
                    <p class="text-gray-700 dark:text-gray-300">AI Copilot özelliği yakında aktif olacak. Şu anda geliştirme aşamasındadır.</p>
                </div>
            </div>
        `;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }, 500);
    
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
});
</script>
@endsection
