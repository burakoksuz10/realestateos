@extends('layouts.admin')
@section('title', 'Aramalar')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Arama Yönetimi</h1>
    <div class="bg-dark-900 border border-dark-700/50 rounded-2xl p-8 text-center">
        <p class="text-yellow-400 font-medium mb-2">Entegrasyon Gerekli</p>
        <p class="text-dark-400 text-sm">{{ $note }}</p>
    </div>
</div>
@endsection
