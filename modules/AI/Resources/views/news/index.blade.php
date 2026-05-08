@extends('layouts.admin')

@section('title', 'Emlak Haberleri')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Emlak Haberleri</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">AI destekli güncel gayrimenkul haberleri · Her 3 saatte otomatik güncellenir</p>
        </div>
        <form action="{{ route('admin.ai.news.fetch') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Şimdi Güncelle
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-500/30 rounded-xl text-green-700 dark:text-green-400 text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.ai.news.index') }}" class="flex items-center gap-3 flex-wrap">
        <div class="relative flex-1 min-w-48">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Haber ara..."
                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <select name="category" onchange="this.form.submit()"
            class="px-4 py-2.5 bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">Tüm Kategoriler</option>
            @foreach(['piyasa'=>'Piyasa','yatirim'=>'Yatırım','konut'=>'Konut','ticari'=>'Ticari','mevzuat'=>'Mevzuat','teknoloji'=>'Teknoloji','genel'=>'Genel'] as $value => $label)
            <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @if(request('q') || request('category'))
        <a href="{{ route('admin.ai.news.index') }}"
            class="px-4 py-2.5 bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
            Temizle
        </a>
        @endif
    </form>

    @if($articles->isEmpty())
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-16 text-center border border-gray-100 dark:border-dark-700">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900/20 dark:to-blue-900/20 flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Henüz haber yok</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">İlk haberleri çekmek için "Şimdi Güncelle" butonuna tıklayın.</p>
        <form action="{{ route('admin.ai.news.fetch') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-400 to-blue-600 hover:from-sky-500 hover:to-blue-700 text-white font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Haberleri Çek
            </button>
        </form>
    </div>
    @else

    @php
    $catColors = [
        'piyasa'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'yatirim'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'konut'=>'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'ticari'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'mevzuat'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'teknoloji'=>'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
        'genel'=>'bg-gray-100 text-gray-600 dark:bg-dark-700 dark:text-gray-400',
    ];
    $catLabels = ['piyasa'=>'Piyasa','yatirim'=>'Yatırım','konut'=>'Konut','ticari'=>'Ticari','mevzuat'=>'Mevzuat','teknoloji'=>'Teknoloji','genel'=>'Genel'];
    $sentimentMap = ['positive'=>['↑','text-green-600 dark:text-green-400'],'negative'=>['↓','text-red-500 dark:text-red-400'],'neutral'=>['→','text-gray-400']];
    @endphp

    <!-- Article list -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-100 dark:border-dark-700 divide-y divide-gray-100 dark:divide-dark-700">
        @foreach($articles as $article)
        @php
            $cat = $article->category ?? 'genel';
            $sent = $sentimentMap[$article->sentiment ?? 'neutral'] ?? $sentimentMap['neutral'];
        @endphp
        <div class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-dark-700/40 transition-colors group">

            <!-- Index number -->
            <span class="text-xs font-mono text-gray-300 dark:text-dark-600 mt-1 w-5 flex-shrink-0 text-right">{{ $loop->iteration + ($articles->currentPage() - 1) * $articles->perPage() }}</span>

            <!-- Body -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2 py-0.5 text-xs font-medium rounded-md {{ $catColors[$cat] ?? $catColors['genel'] }}">
                        {{ $catLabels[$cat] ?? 'Genel' }}
                    </span>
                    <span class="text-xs font-medium {{ $sent[1] }}">{{ $sent[0] }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $article->source }}</span>
                    <span class="text-xs text-gray-300 dark:text-dark-600">·</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $article->published_at?->diffForHumans() }}</span>
                </div>

                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                    class="text-sm font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors leading-snug">
                    {{ $article->title }}
                </a>

                @if($article->ai_summary)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $article->ai_summary }}</p>
                @endif

                @if($article->tags && count($article->tags))
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach(array_slice($article->tags, 0, 5) as $tag)
                    <span class="px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-dark-700 text-gray-500 dark:text-gray-400 rounded">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="{{ $article->url }}" target="_blank" rel="noopener"
                    class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-700 transition-colors"
                    title="Habere git">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <form action="{{ route('admin.ai.news.destroy', $article) }}" method="POST" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Sil">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>

        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($articles->hasPages())
    <div class="flex justify-center">
        {{ $articles->links() }}
    </div>
    @endif

    <!-- Status bar -->
    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 px-1">
        <span>Toplam {{ $articles->total() }} haber</span>
        <div class="flex items-center gap-1.5">
            <span class="flex h-1.5 w-1.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
            </span>
            Her 3 saatte otomatik güncellenir
        </div>
    </div>
    @endif

</div>
@endsection
