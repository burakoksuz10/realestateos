{{--
  Reusable documents card.
  Variables:
    $documentableType : 'lead' | 'deal' | 'contact' | 'listing'
    $documentableId   : int
--}}
<div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6"
     x-data="documentsCard({
         type: '{{ $documentableType }}',
         id: {{ $documentableId }},
         csrf: '{{ csrf_token() }}'
     })"
     x-init="load()">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dokümanlar</h2>
            <span class="text-xs text-gray-500 dark:text-dark-400" x-text="documents.length + ' adet'"></span>
        </div>
        <button @click="$refs.fileInput.click()" :disabled="uploading"
                class="px-3 py-1.5 bg-primary-600/20 hover:bg-primary-600/30 text-primary-400 text-sm rounded-lg transition-colors disabled:opacity-50 flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span x-text="uploading ? 'Yükleniyor...' : 'Dosya Ekle'"></span>
        </button>
        <input type="file" x-ref="fileInput" @change="upload($event)" class="hidden">
    </div>

    {{-- Upload meta (category + notes) — hidden until file picked --}}
    <div x-show="pendingFile" x-transition class="mb-4 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl" x-cloak>
        <p class="text-xs text-gray-500 dark:text-dark-400 mb-2">
            Yüklenecek: <b class="text-white" x-text="pendingFile?.name"></b>
        </p>
        <div class="grid grid-cols-2 gap-2">
            <select x-model="pendingCategory" class="px-2 py-1.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                <option value="other">Diğer</option>
                <option value="contract">Sözleşme</option>
                <option value="identity">Kimlik</option>
                <option value="deed">Tapu</option>
                <option value="valuation">Ekspertiz</option>
                <option value="photo">Foto</option>
            </select>
            <input type="text" x-model="pendingTitle" placeholder="Başlık (opsiyonel)"
                   class="px-2 py-1.5 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
        </div>
        <div class="flex items-center justify-between mt-2">
            <label class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-dark-300">
                <input type="checkbox" x-model="pendingConfidential" class="rounded">
                Gizli (KVKK)
            </label>
            <div class="flex gap-2">
                <button @click="cancelUpload()" class="px-3 py-1 bg-gray-200 dark:bg-dark-700 text-gray-600 dark:text-dark-300 text-xs rounded-lg">İptal</button>
                <button @click="confirmUpload()" :disabled="uploading" class="px-3 py-1 bg-emerald-500/30 text-emerald-300 text-xs rounded-lg disabled:opacity-50" x-text="uploading ? 'Yükleniyor' : 'Yükle'"></button>
            </div>
        </div>
    </div>

    <div x-show="error" x-cloak class="mb-3 p-2 bg-rose-500/20 border border-rose-500/30 rounded-lg text-rose-300 text-xs" x-text="error"></div>

    <template x-if="documents.length === 0 && !pendingFile">
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-dark-400 text-sm">Henüz doküman eklenmemiş.</p>
        </div>
    </template>

    <div class="space-y-2" x-show="documents.length > 0">
        <template x-for="doc in documents" :key="doc.id">
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-dark-800/50 rounded-xl">
                <div class="w-9 h-9 rounded-lg bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-white text-sm font-medium truncate" x-text="doc.title"></p>
                        <span class="text-xs px-1.5 py-0.5 bg-gray-200 dark:bg-dark-700 text-gray-500 dark:text-dark-300 rounded-full" x-text="doc.category_label"></span>
                        <span x-show="doc.is_confidential" class="text-xs text-amber-400" title="Gizli">🔒</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-dark-400 truncate" x-text="`${doc.original_name} · ${doc.size} · ${doc.uploaded_by ?? 'Sistem'} · ${doc.uploaded_at}`"></p>
                </div>
                <a :href="doc.download_url" class="px-2 py-1 bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 text-xs rounded-lg">İndir</a>
                <button @click="remove(doc.id)" class="p-1 text-rose-400 hover:text-rose-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>
</div>

<script>
function documentsCard(opts) {
    return {
        documents: [],
        pendingFile: null,
        pendingCategory: 'other',
        pendingTitle: '',
        pendingConfidential: true,
        uploading: false,
        error: '',

        async load() {
            try {
                const res = await fetch(`/admin/documents/${opts.type}/${opts.id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.documents = data.documents || [];
            } catch (e) {
                this.error = 'Dokümanlar yüklenemedi.';
            }
        },

        upload(event) {
            this.pendingFile = event.target.files[0] || null;
            this.pendingTitle = this.pendingFile?.name || '';
            this.error = '';
        },

        cancelUpload() {
            this.pendingFile = null;
            this.$refs.fileInput.value = '';
        },

        async confirmUpload() {
            if (!this.pendingFile) return;
            this.uploading = true;
            this.error = '';

            const fd = new FormData();
            fd.append('file', this.pendingFile);
            fd.append('title', this.pendingTitle);
            fd.append('category', this.pendingCategory);
            fd.append('is_confidential', this.pendingConfidential ? '1' : '0');

            try {
                const res = await fetch(`/admin/documents/${opts.type}/${opts.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': opts.csrf, 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    this.cancelUpload();
                    await this.load();
                } else {
                    this.error = data.message || 'Yükleme başarısız.';
                }
            } catch (e) {
                this.error = 'Yükleme sırasında hata oluştu.';
            } finally {
                this.uploading = false;
            }
        },

        async remove(docId) {
            if (!confirm('Bu dokümanı silmek istediğine emin misin? (Yedeği audit log\'da saklanır.)')) return;
            try {
                const res = await fetch(`/admin/documents/${docId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': opts.csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) await this.load();
            } catch (e) {
                this.error = 'Silme başarısız.';
            }
        },
    }
}
</script>
