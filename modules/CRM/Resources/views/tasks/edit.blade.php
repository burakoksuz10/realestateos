@extends('layouts.admin')

@section('title', 'Görev Düzenle')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Görev Düzenle</h1>
            <p class="text-dark-400 mt-1">{{ $task->title }}</p>
        </div>
        <a href="{{ route('admin.tasks.show', $task) }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Geri
        </a>
    </div>

    <form action="{{ route('admin.tasks.update', $task) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-6">Görev Bilgileri</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Başlık *</label>
                            <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                                class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Açıklama</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $task->description) }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Tür</label>
                                <select name="type" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    @foreach(['call'=>'Arama','email'=>'E-posta','meeting'=>'Toplantı','showing'=>'Gösterim','follow_up'=>'Takip','document'=>'Belge','other'=>'Diğer'] as $val => $label)
                                    <option value="{{ $val }}" {{ $task->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-2">Vade Tarihi *</label>
                                <input type="date" name="due_date" value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}" required
                                    class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Durum & Atama</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Durum</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['pending'=>'Bekliyor','in_progress'=>'Devam Ediyor','completed'=>'Tamamlandı','cancelled'=>'İptal'] as $val => $label)
                                <option value="{{ $val }}" {{ $task->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Öncelik</label>
                            <select name="priority" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(['low'=>'Düşük','medium'=>'Orta','high'=>'Yüksek','urgent'=>'Acil'] as $val => $label)
                                <option value="{{ $val }}" {{ $task->priority === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-2">Atanan *</label>
                            <select name="assigned_to" required class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['agent','office-manager','admin']))->get() as $user)
                                <option value="{{ $user->id }}" {{ $task->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">Güncelle</button>
                    <a href="{{ route('admin.tasks.show', $task) }}" class="block w-full px-4 py-2.5 bg-dark-700 hover:bg-dark-600 text-white font-medium rounded-xl transition-colors text-center">İptal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
