<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'ReCRM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        /* Smooth transitions */
        .sidebar-transition { transition: width 0.3s ease, transform 0.3s ease; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Glass effect */
        .glass { backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.8); }
        .dark .glass { background: rgba(15, 23, 42, 0.8); }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-dark-900 font-sans antialiased" 
      x-data="{ 
          sidebarOpen: true, 
          sidebarMobileOpen: false,
          darkMode: localStorage.getItem('darkMode') === 'true',
          notifications: [],
          showNotifications: false,
          showUserMenu: false,
          showSearch: false
      }"
      x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) }); 
              if(darkMode) document.documentElement.classList.add('dark')"
      :class="{ 'dark': darkMode }">

    <div class="flex h-full">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0" :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-20': !sidebarOpen }">
            <!-- Top Navigation -->
            @include('layouts.partials.topnav')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                    <nav class="mb-4 text-sm">
                        @yield('breadcrumb')
                    </nav>
                @endif

                <!-- Page Header -->
                @hasSection('header')
                    <div class="mb-6">
                        @yield('header')
                    </div>
                @endif

                <!-- Main Content -->
                <div class="fade-in">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="notifications.length > 0" 
         x-cloak
         class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="(notification, index) in notifications" :key="index">
            <div x-show="notification.show"
                 x-transition:enter="transform ease-out duration-300"
                 x-transition:enter-start="translate-y-2 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transform ease-in duration-200"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-2 opacity-0"
                 class="max-w-sm w-full bg-white dark:bg-dark-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <template x-if="notification.type === 'success'">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="notification.type === 'error'">
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="notification.show = false" class="rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Global Search Modal -->
    <div x-show="showSearch" 
         x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showSearch = false">
        <div class="min-h-screen px-4 text-center">
            <div class="fixed inset-0 bg-gray-500 dark:bg-dark-900 bg-opacity-75 dark:bg-opacity-75" @click="showSearch = false"></div>
            
            <div class="inline-block w-full max-w-2xl my-8 text-left align-middle transition-all transform bg-white dark:bg-dark-800 shadow-xl rounded-2xl">
                <div class="relative">
                    <svg class="absolute left-4 top-4 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" 
                           class="w-full pl-12 pr-4 py-4 text-lg bg-transparent border-0 border-b border-gray-200 dark:border-dark-700 focus:ring-0 focus:border-primary-500 dark:text-white"
                           placeholder="İlan, müşteri veya potansiyel müşteri ara..."
                           x-ref="searchInput"
                           @keydown.enter="performSearch()">
                </div>
                <div class="p-4 max-h-96 overflow-y-auto">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aramak için yazmaya başlayın...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating AI Copilot --}}
    @auth
        @include('components.ai-copilot-widget')
    @endauth

    @stack('modals')
    @stack('scripts')

    <script>
        // Global notification function
        function showNotification(type, title, message) {
            const notification = { type, title, message, show: true };
            Alpine.store('notifications').push(notification);
            setTimeout(() => { notification.show = false; }, 5000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + K for search
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                Alpine.evaluate(document.body, 'showSearch = true; $nextTick(() => $refs.searchInput.focus())');
            }
        });
    </script>
</body>
</html>
