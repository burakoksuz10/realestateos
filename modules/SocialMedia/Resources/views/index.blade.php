@extends('layouts.admin')
@section('title', 'Sosyal Medya')
@section('content')
<div
    class="space-y-6"
    x-data="sosyalMedia()"
    x-init="init()"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sosyal Medya</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">Instagram ve Facebook gönderilerinizi planlayın ve yönetin</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showPlanModal = true"
                class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                AI İçerik Planı
            </button>
            <button @click="openCreateModal()"
                class="px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Yeni Gönderi
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Toplam Gönderi</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Yayınlanan</p>
            <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['published'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Planlanmış</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $stats['scheduled'] }}</p>
        </div>
        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Taslak</p>
            <p class="text-2xl font-bold text-yellow-400 mt-1">{{ $stats['draft'] }}</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-1 w-fit">
        @foreach(['all' => 'Tümü', 'planlandi' => 'Planlandı', 'draft' => 'Taslak', 'yayinlandi' => 'Yayınlandı', 'hata' => 'Hata'] as $key => $label)
        <button
            @click="filter = '{{ $key }}'"
            :class="filter === '{{ $key }}' ? 'bg-gray-200 dark:bg-dark-700 text-white' : 'text-gray-500 dark:text-dark-400 hover:text-white'"
            class="px-4 py-2 rounded-xl text-sm font-medium transition-colors">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="post in filteredPosts" :key="post.id">
            <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-5 flex flex-col gap-4">
                <!-- Post Header -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center"
                            :class="post.platform === 'instagram' ? 'bg-gradient-to-br from-purple-500 to-pink-500' : post.platform === 'facebook' ? 'bg-blue-600' : 'bg-gradient-to-br from-blue-500 to-purple-500'">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" x-show="post.platform === 'instagram'">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" x-show="post.platform === 'facebook'">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="post.platform === 'both'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium" x-text="platformLabel(post.platform)"></p>
                            <p class="text-dark-500 text-xs" x-text="contentTypeLabel(post.content_type)"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded-full"
                            :class="statusClass(post.status)"
                            x-text="statusLabel(post.status)">
                        </span>
                    </div>
                </div>

                <!-- Media Preview -->
                <div x-show="post.media_url" class="w-full h-40 bg-gray-100 dark:bg-dark-800 rounded-xl overflow-hidden">
                    <img :src="post.media_url" class="w-full h-full object-cover" :alt="post.caption">
                </div>
                <div x-show="!post.media_url" class="w-full h-32 bg-gray-100 dark:bg-dark-800 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>

                <!-- Caption -->
                <p class="text-gray-600 dark:text-dark-300 text-sm leading-relaxed line-clamp-3" x-text="post.caption || 'Altyazı yok'"></p>

                <!-- Scheduled date -->
                <div x-show="post.scheduled_at" class="flex items-center gap-2 text-xs text-gray-500 dark:text-dark-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="formatDate(post.scheduled_at)"></span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-1">
                    <button @click="openEditModal(post)"
                        class="flex-1 px-3 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Düzenle
                    </button>
                    <button @click="publishPost(post)" x-show="post.status !== 'yayinlandi'"
                        class="flex-1 px-3 py-2 bg-green-600/20 hover:bg-green-600/30 text-green-400 rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Yayınla
                    </button>
                    <button @click="deletePost(post)"
                        class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Empty state -->
        <div x-show="filteredPosts.length === 0" class="md:col-span-2 xl:col-span-3 py-16 text-center">
            <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="text-gray-500 dark:text-dark-400 font-medium">Bu filtrede gönderi bulunamadı</p>
            <button @click="openCreateModal()" class="mt-3 px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl text-sm transition-colors">İlk Gönderiyi Oluştur</button>
        </div>
    </div>

    <!-- Create/Edit Post Modal -->
    <div x-show="showPostModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" @click="showPostModal = false"></div>
        <div class="relative bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-dark-700/50 flex items-center justify-between sticky top-0 bg-white dark:bg-dark-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingPost ? 'Gönderi Düzenle' : 'Yeni Gönderi'"></h2>
                <button @click="showPostModal = false" class="text-gray-500 dark:text-dark-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <!-- Platform -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Platform</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['instagram' => 'Instagram', 'facebook' => 'Facebook', 'both' => 'Her İkisi'] as $pkey => $plabel)
                        <button
                            @click="form.platform = '{{ $pkey }}'"
                            :class="form.platform === '{{ $pkey }}' ? 'border-primary-500 bg-primary-600/20 text-primary-400' : 'border-gray-200 dark:border-dark-700 text-gray-500 dark:text-dark-400 hover:border-dark-600'"
                            class="px-3 py-2 border rounded-xl text-sm transition-colors">
                            {{ $plabel }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Content Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">İçerik Tipi</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['post' => 'Gönderi', 'story' => 'Hikaye', 'reel' => 'Reel'] as $ckey => $clabel)
                        <button
                            @click="form.content_type = '{{ $ckey }}'"
                            :class="form.content_type === '{{ $ckey }}' ? 'border-primary-500 bg-primary-600/20 text-primary-400' : 'border-gray-200 dark:border-dark-700 text-gray-500 dark:text-dark-400 hover:border-dark-600'"
                            class="px-3 py-2 border rounded-xl text-sm transition-colors">
                            {{ $clabel }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Caption -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-600 dark:text-dark-300">Altyazı</label>
                        <button @click="generateCaption()" :disabled="aiCaptionLoading"
                            class="text-xs px-3 py-1.5 bg-violet-600/20 hover:bg-violet-600/30 text-violet-400 rounded-lg transition-colors flex items-center gap-1.5 disabled:opacity-50">
                            <svg x-show="!aiCaptionLoading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <svg x-show="aiCaptionLoading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            AI Yaz
                        </button>
                    </div>
                    <textarea x-model="form.caption" rows="5"
                        placeholder="Gönderi altyazınızı yazın veya AI ile oluşturun..."
                        class="w-full px-4 py-3 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none text-sm"></textarea>
                    <p class="text-xs text-dark-500 mt-1" x-text="(form.caption?.length || 0) + '/2200 karakter'"></p>
                </div>

                <!-- Media URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Görsel URL (isteğe bağlı)</label>
                    <input type="url" x-model="form.media_url"
                        placeholder="https://..."
                        class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>

                <!-- Status & Scheduled Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Durum</label>
                        <select x-model="form.status"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                            <option value="draft">Taslak</option>
                            <option value="planlandi">Planla</option>
                        </select>
                    </div>
                    <div x-show="form.status === 'planlandi'">
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Planlanan Tarih</label>
                        <input type="datetime-local" x-model="form.scheduled_at"
                            class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                    </div>
                </div>

                <!-- Error -->
                <div x-show="formError" class="p-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400 text-sm" x-text="formError"></div>
            </div>
            <div class="p-6 border-t border-gray-200 dark:border-dark-700/50 flex items-center justify-end gap-3">
                <button @click="showPostModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors text-sm">İptal</button>
                <button @click="savePost()" :disabled="saving"
                    class="px-5 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white rounded-xl transition-colors text-sm flex items-center gap-2 disabled:opacity-50">
                    <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="editingPost ? 'Güncelle' : 'Kaydet'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- AI Content Plan Modal -->
    <div x-show="showPlanModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70" @click="showPlanModal = false"></div>
        <div class="relative bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 dark:border-dark-700/50 flex items-center justify-between sticky top-0 bg-white dark:bg-dark-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Aylık İçerik Planı</h2>
                <button @click="showPlanModal = false" class="text-gray-500 dark:text-dark-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Ton</label>
                        <select x-model="planForm.tone" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                            <option value="profesyonel">Profesyonel</option>
                            <option value="samimi">Samimi</option>
                            <option value="enerjik">Enerjik</option>
                            <option value="bilgilendirici">Bilgilendirici</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-dark-300 mb-2">Ay</label>
                        <input type="month" x-model="planForm.month" class="w-full px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                    </div>
                </div>

                <div x-show="!planResult && !planLoading" class="bg-gray-100 dark:bg-dark-800 rounded-xl p-4 text-center">
                    <svg class="w-10 h-10 text-dark-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    <p class="text-gray-500 dark:text-dark-400 text-sm">Planı oluşturmak için butona tıklayın. AI, aylık içerik takvimi hazırlayacak.</p>
                </div>

                <div x-show="planLoading" class="py-8 text-center">
                    <svg class="w-8 h-8 animate-spin text-primary-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="text-gray-500 dark:text-dark-400 text-sm">AI içerik planı hazırlanıyor...</p>
                </div>

                <div x-show="planResult" class="bg-gray-100 dark:bg-dark-800 rounded-xl p-4">
                    <pre class="text-gray-600 dark:text-dark-300 text-xs whitespace-pre-wrap leading-relaxed" x-text="planResult"></pre>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 dark:border-dark-700/50 flex items-center justify-end gap-3">
                <button @click="showPlanModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors text-sm">Kapat</button>
                <button @click="generatePlan()" :disabled="planLoading"
                    class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl transition-colors text-sm flex items-center gap-2 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Plan Oluştur
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function sosyalMedia() {
    return {
        filter: 'all',
        posts: @json($posts->flatten()->values()),
        showPostModal: false,
        showPlanModal: false,
        editingPost: null,
        saving: false,
        aiCaptionLoading: false,
        planLoading: false,
        planResult: null,
        formError: null,
        form: {
            platform: 'instagram',
            content_type: 'post',
            caption: '',
            media_url: '',
            status: 'draft',
            scheduled_at: '',
        },
        planForm: {
            tone: 'profesyonel',
            month: new Date().toISOString().slice(0, 7),
        },

        init() {},

        get filteredPosts() {
            if (this.filter === 'all') return this.posts;
            return this.posts.filter(p => p.status === this.filter);
        },

        platformLabel(platform) {
            const map = { instagram: 'Instagram', facebook: 'Facebook', both: 'Instagram & Facebook' };
            return map[platform] || platform;
        },

        contentTypeLabel(type) {
            const map = { post: 'Gönderi', story: 'Hikaye', reel: 'Reel' };
            return map[type] || type;
        },

        statusLabel(status) {
            const map = { draft: 'Taslak', planlandi: 'Planlandı', yayinlandi: 'Yayınlandı', hata: 'Hata' };
            return map[status] || status;
        },

        statusClass(status) {
            const map = {
                draft: 'bg-yellow-500/20 text-yellow-400',
                planlandi: 'bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400',
                yayinlandi: 'bg-green-500/20 text-green-400',
                hata: 'bg-red-500/20 text-red-400',
            };
            return map[status] || 'bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-400';
        },

        formatDate(value) {
            if (!value) return '';
            return new Date(value).toLocaleDateString('tr-TR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        },

        openCreateModal() {
            this.editingPost = null;
            this.formError = null;
            this.form = { platform: 'instagram', content_type: 'post', caption: '', media_url: '', status: 'draft', scheduled_at: '' };
            this.showPostModal = true;
        },

        openEditModal(post) {
            this.editingPost = post;
            this.formError = null;
            this.form = {
                platform: post.platform,
                content_type: post.content_type,
                caption: post.caption || '',
                media_url: post.media_url || '',
                status: post.status === 'yayinlandi' ? 'draft' : post.status,
                scheduled_at: post.scheduled_at ? post.scheduled_at.slice(0, 16) : '',
            };
            this.showPostModal = true;
        },

        async savePost() {
            this.saving = true;
            this.formError = null;
            const url = this.editingPost
                ? '/admin/social-media/posts/' + this.editingPost.id
                : '/admin/social-media/posts';
            const method = this.editingPost ? 'PUT' : 'POST';
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (!res.ok) { this.formError = data.message || 'Bir hata oluştu.'; return; }
                if (this.editingPost) {
                    const idx = this.posts.findIndex(p => p.id === this.editingPost.id);
                    if (idx !== -1) this.posts[idx] = data.post;
                } else {
                    this.posts.unshift(data.post);
                }
                this.showPostModal = false;
            } catch (e) {
                this.formError = 'Bağlantı hatası.';
            } finally {
                this.saving = false;
            }
        },

        async publishPost(post) {
            if (!confirm('Bu gönderiyi yayınlamak istediğinizden emin misiniz?')) return;
            const res = await fetch('/admin/social-media/posts/' + post.id + '/publish', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) {
                const idx = this.posts.findIndex(p => p.id === post.id);
                if (idx !== -1) this.posts[idx] = { ...this.posts[idx], status: 'yayinlandi' };
            }
        },

        async deletePost(post) {
            if (!confirm('Bu gönderiyi silmek istediğinizden emin misiniz?')) return;
            const res = await fetch('/admin/social-media/posts/' + post.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (res.ok) this.posts = this.posts.filter(p => p.id !== post.id);
        },

        async generateCaption() {
            this.aiCaptionLoading = true;
            try {
                const res = await fetch('/admin/social-media/ai/caption', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        platform: this.form.platform,
                        content_type: this.form.content_type,
                        topic: 'gayrimenkul',
                        tone: 'profesyonel',
                    }),
                });
                const data = await res.json();
                if (data.success) this.form.caption = data.caption;
            } finally {
                this.aiCaptionLoading = false;
            }
        },

        async generatePlan() {
            this.planLoading = true;
            this.planResult = null;
            try {
                const res = await fetch('/admin/social-media/ai/plan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.planForm),
                });
                const data = await res.json();
                if (data.success) this.planResult = data.plan;
            } finally {
                this.planLoading = false;
            }
        },
    };
}
</script>
@endsection
