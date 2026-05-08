@php
$colors = [
    'piyasa'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'yatirim'   => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'konut'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'ticari'    => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    'mevzuat'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'teknoloji' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
    'genel'     => 'bg-gray-100 text-gray-600 dark:bg-dark-700 dark:text-gray-400',
];
$labels = [
    'piyasa' => 'Piyasa', 'yatirim' => 'Yatırım', 'konut' => 'Konut',
    'ticari' => 'Ticari', 'mevzuat' => 'Mevzuat', 'teknoloji' => 'Teknoloji', 'genel' => 'Genel',
];
$cat = $article->category ?? 'genel';
@endphp
<span class="px-2 py-0.5 text-xs font-medium rounded-lg {{ $colors[$cat] ?? $colors['genel'] }}">
    {{ $labels[$cat] ?? 'Genel' }}
</span>
