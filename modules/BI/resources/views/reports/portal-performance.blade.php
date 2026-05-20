@extends('layouts.admin')

@section('title', 'Portal Performansı')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Portal Performansı</h1>
            <p class="text-gray-500 dark:text-dark-400 mt-1">
                {{ $filters['date_from']->format('d.m.Y') }} — {{ $filters['date_to']->format('d.m.Y') }}
            </p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-dark-700 hover:bg-gray-200 dark:hover:bg-dark-600 text-gray-700 dark:text-white rounded-xl transition-colors">← Raporlar</a>
    </div>

    <div class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-dark-700/50 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-dark-800/50 text-gray-500 dark:text-dark-400 uppercase text-xs">
                <tr>
                    <th class="text-left p-3">Portal</th>
                    <th class="text-right p-3">Lead</th>
                    <th class="text-right p-3">Dönüşen</th>
                    <th class="text-right p-3">Oran</th>
                    <th class="text-right p-3">Gelir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($portals as $p)
                    <tr class="border-t border-gray-100 dark:border-dark-800">
                        <td class="p-3 text-white font-medium">{{ ucfirst($p['portal']) }}</td>
                        <td class="p-3 text-right text-gray-300">{{ number_format($p['leads']) }}</td>
                        <td class="p-3 text-right text-emerald-400">{{ number_format($p['converted']) }}</td>
                        <td class="p-3 text-right text-white">%{{ $p['conversion_rate'] }}</td>
                        <td class="p-3 text-right text-primary-400">₺{{ number_format((float) $p['revenue'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500 dark:text-dark-400">Veri yok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
