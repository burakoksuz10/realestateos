<!-- Mobile Sidebar Backdrop -->
<div x-show="sidebarMobileOpen" 
     x-cloak
     class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
     @click="sidebarMobileOpen = false"></div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-dark-800 border-r border-gray-200 dark:border-dark-700 sidebar-transition"
       :class="{
           'w-64': sidebarOpen,
           'w-20': !sidebarOpen,
           'translate-x-0': sidebarMobileOpen,
           '-translate-x-full lg:translate-x-0': !sidebarMobileOpen
       }">
    
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-dark-700">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <span x-show="sidebarOpen" x-cloak class="text-xl font-bold text-gray-900 dark:text-white">ReCRM</span>
        </a>
        <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-700">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Dashboard</span>
        </a>

        <!-- Divider -->
        <div class="pt-4 pb-2" x-show="sidebarOpen" x-cloak>
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Portföy</p>
        </div>

        <!-- Listings -->
        <a href="{{ route('admin.listings.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.listings.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">İlanlar</span>
            <span x-show="sidebarOpen" x-cloak class="ml-auto bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 text-xs font-medium px-2 py-0.5 rounded-full">{{ \Modules\RealEstate\Models\Listing::active()->count() }}</span>
        </a>

        <!-- Projects -->
        <a href="{{ route('admin.projects.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.projects.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Projeler</span>
        </a>

        <!-- Divider -->
        <div class="pt-4 pb-2" x-show="sidebarOpen" x-cloak>
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">CRM</p>
        </div>

        <!-- Leads -->
        <a href="{{ route('admin.leads.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.leads.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Potansiyel Müşteriler</span>
            <span x-show="sidebarOpen" x-cloak class="ml-auto bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-medium px-2 py-0.5 rounded-full">{{ \Modules\CRM\Models\Lead::new()->count() }}</span>
        </a>

        <!-- Contacts -->
        <a href="{{ route('admin.contacts.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.contacts.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Müşteriler</span>
        </a>

        <!-- Deals -->
        <a href="{{ route('admin.deals.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.deals.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Satışlar</span>
        </a>

        <!-- Tasks -->
        <a href="{{ route('admin.tasks.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.tasks.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Görevler</span>
            <span x-show="sidebarOpen" x-cloak class="ml-auto bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-medium px-2 py-0.5 rounded-full">{{ \Modules\CRM\Models\Task::pending()->where('assigned_to', auth()->id())->count() }}</span>
        </a>

        <!-- Divider -->
        <div class="pt-4 pb-2" x-show="sidebarOpen" x-cloak>
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">AI & Araçlar</p>
        </div>

        <!-- AI Copilot -->
        <a href="{{ route('admin.ai.copilot.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.ai.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">AI Copilot</span>
            <span x-show="sidebarOpen" x-cloak class="ml-auto">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
            </span>
        </a>

        <!-- Valuation -->
        <a href="{{ route('admin.ai.valuation.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Değerleme</span>
        </a>

        <!-- News -->
        <a href="{{ route('admin.ai.news.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.ai.news.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Emlak Haberleri</span>
        </a>

        <!-- Telegram -->
        <a href="{{ route('admin.telegram.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.telegram.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Telegram</span>
        </a>

        <!-- AI Settings -->
        <a href="{{ route('admin.ai.settings.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.ai.settings.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">AI Ayarları</span>
        </a>

        <!-- Divider -->
        <div class="pt-4 pb-2" x-show="sidebarOpen" x-cloak>
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Raporlar</p>
        </div>

        <!-- Reports -->
        <a href="{{ route('admin.reports.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.reports.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Raporlar</span>
        </a>

        <!-- Divider -->
        <div class="pt-4 pb-2" x-show="sidebarOpen" x-cloak>
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yönetim</p>
        </div>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.users.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Kullanıcılar</span>
        </a>

        <!-- Offices -->
        <a href="{{ route('admin.offices.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.offices.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Ofisler</span>
        </a>

        <!-- Social Media -->
        <a href="{{ route('admin.social-media.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.social-media.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Sosyal Medya</span>
        </a>

        <!-- Advertising -->
        <a href="{{ route('admin.advertising.index') }}"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.advertising.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Reklamlar</span>
        </a>

        <!-- Integrations -->
        <a href="{{ route('admin.integrations.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.integrations.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Entegrasyonlar</span>
        </a>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" 
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200
                  {{ request()->routeIs('admin.settings.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span x-show="sidebarOpen" x-cloak class="ml-3">Ayarlar</span>
        </a>
    </nav>

    <!-- User Info -->
    <div class="p-4 border-t border-gray-200 dark:border-dark-700">
        <div class="flex items-center" :class="{ 'justify-center': !sidebarOpen }">
            <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-dark-700" 
                 src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=0ea5e9&color=fff' }}" 
                 alt="{{ auth()->user()->name }}">
            <div x-show="sidebarOpen" x-cloak class="ml-3 min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
