<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Selamat Datang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-white antialiased">
    <div class="min-h-screen relative overflow-hidden"
         style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">

        <div class="absolute inset-0 bg-black/60"></div>

        <a href="{{ route('login') }}"
           class="absolute top-6 left-6 z-10 bg-primary hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg shadow-lg transition">
            LOGIN
        </a>

        <div class="relative z-10 min-h-screen flex flex-col md:flex-row items-center md:justify-between px-8 md:px-16 lg:px-24 py-24 md:py-0">
            <div class="max-w-2xl text-center md:text-left">
                <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight"
                    style="text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">
                    Selamat Datang di Sistem Inventaris Bengkel
                </h1>

                <p class="text-xl text-white/90" style="text-shadow: 1px 1px 6px rgba(0,0,0,0.8);">
                    Sistem pengelolaan inventaris alat dan bahan bengkel yang memudahkan
                    pengawasan, pemeliharaan, peminjaman, dan pelaporan aset bengkel secara terpusat.
                </p>
            </div>

            <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP"
                 class="w-64 md:w-96 object-contain mt-12 md:mt-0 drop-shadow-2xl">
        </div>
    </div>
</body>
</html>
