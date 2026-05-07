@extends('layouts.admin')

@section('title', $team->name . ' - Takım Detayı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
            <p class="text-dark-400 mt-1">{{ $team->office->name ?? 'Ofis belirtilmemiş' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.teams.edit', $team) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Düzenle
            </a>
            <a href="{{ route('admin.teams.index') }}" class="px-4 py-2 bg-dark-700 hover:bg-dark-600 text-white rounded-xl transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Geri
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Members -->
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">Takım Üyeleri</h2>
                    <span class="text-dark-400 text-sm">{{ $team->members->count() }} üye</span>
                </div>

                <!-- Add Member Form -->
                <form action="{{ route('admin.teams.add-member', $team) }}" method="POST" class="flex gap-3 mb-6">
                    @csrf
                    <select name="user_id" required class="flex-1 px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Üye ekle...</option>
                        @php $memberIds = $team->members->pluck('id'); @endphp
                        @foreach(\App\Models\User::where('office_id', $team->office_id)->whereNotIn('id', $memberIds)->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-colors whitespace-nowrap">
                        Ekle
                    </button>
                </form>

                @forelse($team->members as $member)
                <div class="flex items-center justify-between py-3 border-b border-dark-700/50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-white text-sm font-medium">{{ $member->name }}</p>
                                @if($team->leader_id === $member->id)
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Lider</span>
                                @endif
                            </div>
                            <p class="text-dark-400 text-xs">{{ $member->title ?? $member->email }}</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.teams.remove-member', [$team, $member]) }}" method="POST" onsubmit="return confirm('Üyeyi takımdan çıkarmak istediğinize emin misiniz?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Çıkar</button>
                    </form>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-dark-400">Henüz üye eklenmemiş.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Takım Bilgileri</h2>
                <div class="space-y-3">
                    @if($team->description)
                    <p class="text-dark-400 text-sm">{{ $team->description }}</p>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Ofis</span>
                        <span class="text-white text-sm">{{ $team->office->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Lider</span>
                        <span class="text-white text-sm">{{ $team->leader->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Üye Sayısı</span>
                        <span class="text-white font-semibold">{{ $team->members->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Durum</span>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $team->is_active ? 'bg-green-500/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                            {{ $team->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-6 space-y-3">
                <a href="{{ route('admin.teams.edit', $team) }}" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors flex items-center justify-center">
                    Düzenle
                </a>
                <form action="{{ route('admin.teams.destroy', $team) }}" method="POST" onsubmit="return confirm('Bu takımı silmek istediğinize emin misiniz?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-xl transition-colors">
                        Takımı Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
