<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giriş Yap - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 font-sans antialiased">
    <div class="min-h-full flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 to-purple-600/20"></div>
            <div class="absolute inset-0" style="background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1973&q=80'); background-size: cover; background-position: center; opacity: 0.3;"></div>
            
            <div class="relative z-10 flex flex-col justify-between p-12 text-white">
                <div>
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold">RE-OS</span>
                    </div>
                </div>

                <div class="space-y-6">
                    <h1 class="text-4xl font-bold leading-tight">
                        Gayrimenkul İşinizi<br>
                        <span class="text-primary-400">Yapay Zeka</span> ile Yönetin
                    </h1>
                    <p class="text-lg text-white/70 max-w-md">
                        Lead yönetimi, değerleme, pazarlama otomasyonu ve daha fazlası. 
                        Tek platformda tüm emlak operasyonlarınız.
                    </p>
                    
                    <div class="flex items-center space-x-8 pt-4">
                        <div>
                            <p class="text-3xl font-bold">500+</p>
                            <p class="text-sm text-white/60">Aktif Kullanıcı</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">10K+</p>
                            <p class="text-sm text-white/60">İlan</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold">₺2B+</p>
                            <p class="text-sm text-white/60">Satış Hacmi</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4 text-sm text-white/50">
                    <span>© 2024 RE-OS</span>
                    <span>•</span>
                    <a href="#" class="hover:text-white">Gizlilik</a>
                    <span>•</span>
                    <a href="#" class="hover:text-white">Kullanım Şartları</a>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white">RE-OS</span>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-white">Hoş Geldiniz</h2>
                        <p class="text-slate-400 mt-2">Hesabınıza giriş yapın</p>
                    </div>

                    @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                        <p class="text-sm text-red-400">{{ $errors->first() }}</p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">E-posta</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                   placeholder="ornek@email.com">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-sm font-medium text-slate-300">Şifre</label>
                                <a href="{{ route('password.request') }}" class="text-sm text-primary-400 hover:text-primary-300">Şifremi Unuttum</a>
                            </div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                   placeholder="••••••••">
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="remember" 
                                   name="remember"
                                   class="w-4 h-4 rounded border-white/20 bg-white/5 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                            <label for="remember" class="ml-2 text-sm text-slate-400">Beni hatırla</label>
                        </div>

                        <button type="submit" 
                                class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg shadow-primary-600/30 hover:shadow-primary-600/40">
                            Giriş Yap
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-white/10 text-center">
                        <p class="text-sm text-slate-400">
                            Demo hesabı: <span class="text-white">admin@reos.com</span> / <span class="text-white">password</span>
                        </p>
                    </div>
                </div>

                <p class="mt-8 text-center text-sm text-slate-500">
                    Hesabınız yok mu? 
                    <a href="#" class="text-primary-400 hover:text-primary-300 font-medium">İletişime geçin</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
