<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Şifre Belirle - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: { 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' } }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } }
        }
    </script>
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 font-sans antialiased flex items-center justify-center p-8">
    <div class="w-full max-w-md">
        <div class="flex items-center justify-center space-x-3 mb-8">
            <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <span class="text-2xl font-bold text-white">RE-OS</span>
        </div>

        <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/10">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white">Yeni Şifre Belirle</h2>
                <p class="text-slate-400 mt-2 text-sm">Hesabınız için yeni bir şifre oluşturun.</p>
            </div>

            @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                @foreach ($errors->all() as $error)
                <p class="text-sm text-red-400">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-posta Adresi</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="ornek@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Yeni Şifre</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="En az 8 karakter">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Şifre Tekrarı</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        placeholder="Şifrenizi tekrar girin">
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg shadow-primary-600/30">
                    Şifreyi Güncelle
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium">← Giriş sayfasına dön</a>
        </p>
    </div>
</body>
</html>
