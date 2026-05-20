@extends('layouts.admin')

@section('title', 'Otomatik Aksiyonlar')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Otomatik Aksiyonlar</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">
                <span class="inline-block w-3 h-3 rounded-full align-middle" style="background-color: {{ $stage->color ?? '#0ea5e9' }}"></span>
                <span class="align-middle ml-1">{{ $pipeline->name }} → <b>{{ $stage->name }}</b></span>
            </p>
        </div>
        <a href="{{ route('admin.pipelines.show', $pipeline) }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    @if (session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-blue-500/10 border border-blue-500/30 text-blue-300 px-4 py-3 rounded-xl text-sm">
        Bir deal bu aşamaya girdiğinde aşağıdaki aksiyonlar sırayla otomatik çalışır.
        Mesajlarda <code class="bg-blue-900/40 px-1 rounded">{{ '{{contact.first_name}}' }}</code>, <code class="bg-blue-900/40 px-1 rounded">{{ '{{deal.title}}' }}</code>, <code class="bg-blue-900/40 px-1 rounded">{{ '{{stage.name}}' }}</code>, <code class="bg-blue-900/40 px-1 rounded">{{ '{{agent.name}}' }}</code>, <code class="bg-blue-900/40 px-1 rounded">{{ '{{listing.reference}}' }}</code> gibi değişkenleri kullanabilirsin.
    </div>

    <form action="{{ route('admin.pipelines.stages.auto-actions.update', [$pipeline, $stage]) }}"
          method="POST"
          class="space-y-6"
          x-data='{
              actions: @json($actions ?: []),
              add(type) {
                  const tpl = {
                      create_task:        { type, title: "Müşteriyi ara", description: "", priority: "medium", due_in_hours: 24 },
                      notify_agent:       { type, message: "" },
                      notify_office:      { type, message: "" },
                      set_field:          { type, field: "notes", value: "" },
                      enroll_campaign:    { type, campaign_slug: "" },
                      update_probability: { type, probability: 50 },
                  };
                  this.actions.push(tpl[type] || { type });
              },
              remove(i) { this.actions.splice(i, 1); },
              move(i, delta) {
                  const j = i + delta;
                  if (j < 0 || j >= this.actions.length) return;
                  const tmp = this.actions[i];
                  this.actions.splice(i, 1);
                  this.actions.splice(j, 0, tmp);
              },
          }'>
        @csrf @method('PUT')

        <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aksiyonlar</h2>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="add('create_task')" class="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-sm rounded-lg transition-colors">+ Görev Oluştur</button>
                    <button type="button" @click="add('notify_agent')" class="px-3 py-1.5 bg-sky-500/20 hover:bg-sky-500/30 text-sky-300 text-sm rounded-lg transition-colors">+ Danışmana Telegram</button>
                    <button type="button" @click="add('notify_office')" class="px-3 py-1.5 bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 text-sm rounded-lg transition-colors">+ Ofise Telegram</button>
                    <button type="button" @click="add('set_field')" class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-sm rounded-lg transition-colors">+ Alan Güncelle</button>
                    <button type="button" @click="add('enroll_campaign')" class="px-3 py-1.5 bg-pink-500/20 hover:bg-pink-500/30 text-pink-300 text-sm rounded-lg transition-colors">+ Kampanyaya Al</button>
                    <button type="button" @click="add('update_probability')" class="px-3 py-1.5 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 text-sm rounded-lg transition-colors">+ Olasılık</button>
                </div>
            </div>

            <template x-if="actions.length === 0">
                <div class="text-center py-10 text-gray-500 dark:text-dark-400">
                    Bu aşama için henüz aksiyon yok. Üstten bir aksiyon türü seç.
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="(action, index) in actions" :key="index">
                    <div class="bg-gray-50 dark:bg-dark-800/50 rounded-xl p-4 border border-gray-200 dark:border-dark-700/50">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-1 rounded-full font-medium"
                                      :class="{
                                          'bg-emerald-500/20 text-emerald-300': action.type === 'create_task',
                                          'bg-sky-500/20 text-sky-300': action.type === 'notify_agent',
                                          'bg-purple-500/20 text-purple-300': action.type === 'notify_office',
                                          'bg-amber-500/20 text-amber-300': action.type === 'set_field',
                                          'bg-pink-500/20 text-pink-300': action.type === 'enroll_campaign',
                                          'bg-indigo-500/20 text-indigo-300': action.type === 'update_probability',
                                      }"
                                      x-text="{
                                          create_task: 'Görev Oluştur',
                                          notify_agent: 'Danışmana Telegram',
                                          notify_office: 'Ofise Telegram',
                                          set_field: 'Alan Güncelle',
                                          enroll_campaign: 'Kampanyaya Al',
                                          update_probability: 'Olasılık Güncelle',
                                      }[action.type] || action.type"></span>
                                <span class="text-gray-400 dark:text-dark-500 text-xs" x-text="'#' + (index + 1)"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="move(index, -1)" :disabled="index === 0" class="p-1 text-gray-400 hover:text-white disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" @click="move(index, 1)" :disabled="index === actions.length - 1" class="p-1 text-gray-400 hover:text-white disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button type="button" @click="remove(index)" class="p-1 text-red-400 hover:text-red-300 ml-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" :name="`actions[${index}][type]`" :value="action.type">

                        {{-- create_task --}}
                        <template x-if="action.type === 'create_task'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Başlık</label>
                                    <input type="text" :name="`actions[${index}][title]`" x-model="action.title"
                                           class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Açıklama</label>
                                    <textarea :name="`actions[${index}][description]`" x-model="action.description" rows="2"
                                              class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Öncelik</label>
                                    <select :name="`actions[${index}][priority]`" x-model="action.priority"
                                            class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                        <option value="low">Düşük</option>
                                        <option value="medium">Orta</option>
                                        <option value="high">Yüksek</option>
                                        <option value="urgent">Acil</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Vade (saat)</label>
                                    <input type="number" min="0" :name="`actions[${index}][due_in_hours]`" x-model.number="action.due_in_hours"
                                           class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                </div>
                            </div>
                        </template>

                        {{-- notify_agent / notify_office --}}
                        <template x-if="action.type === 'notify_agent' || action.type === 'notify_office'">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Mesaj (HTML destekli)</label>
                                <textarea :name="`actions[${index}][message]`" x-model="action.message" rows="3"
                                          placeholder="Örn: 🎯 {{ '{{deal.title}}' }} <b>{{ '{{stage.name}}' }}</b> aşamasına geçti. Müşteri: {{ '{{contact.full_name}}' }}"
                                          class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm"></textarea>
                            </div>
                        </template>

                        {{-- set_field --}}
                        <template x-if="action.type === 'set_field'">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Alan</label>
                                    <select :name="`actions[${index}][field]`" x-model="action.field"
                                            class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                        <option value="probability">Olasılık (%)</option>
                                        <option value="expected_close_date">Beklenen Kapanış</option>
                                        <option value="notes">Not</option>
                                        <option value="won_reason">Kazanma Sebebi</option>
                                        <option value="lost_reason">Kaybetme Sebebi</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Değer</label>
                                    <input type="text" :name="`actions[${index}][value]`" x-model="action.value"
                                           class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                </div>
                            </div>
                        </template>

                        {{-- enroll_campaign --}}
                        <template x-if="action.type === 'enroll_campaign'">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Kampanya Slug</label>
                                <input type="text" :name="`actions[${index}][campaign_slug]`" x-model="action.campaign_slug"
                                       placeholder="Örn: onboarding"
                                       class="w-full px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                                <p class="text-xs text-gray-500 dark:text-dark-400 mt-1">Deal'a bağlı lead, bu kampanyaya enroll edilir.</p>
                            </div>
                        </template>

                        {{-- update_probability --}}
                        <template x-if="action.type === 'update_probability'">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-dark-400 mb-1">Olasılık (%)</label>
                                <input type="number" min="0" max="100" :name="`actions[${index}][probability]`" x-model.number="action.probability"
                                       class="w-32 px-3 py-2 bg-gray-200 dark:bg-dark-700 border border-dark-600 rounded-lg text-white text-sm">
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.pipelines.show', $pipeline) }}" class="px-4 py-2.5 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white font-medium rounded-xl transition-colors">İptal</a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">Aksiyonları Kaydet</button>
        </div>
    </form>
</div>
@endsection
