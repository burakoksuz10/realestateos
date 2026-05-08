@php
$map = [
    'positive' => ['label' => '↑ Olumlu', 'class' => 'text-green-600 dark:text-green-400'],
    'negative' => ['label' => '↓ Olumsuz', 'class' => 'text-red-500 dark:text-red-400'],
    'neutral'  => ['label' => '→ Nötr',   'class' => 'text-gray-400 dark:text-gray-500'],
];
$s = $map[$article->sentiment ?? 'neutral'] ?? $map['neutral'];
@endphp
<span class="text-xs font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
