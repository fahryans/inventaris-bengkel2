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
    <div class="min-h-screen flex flex-col items-center justify-center text-center px-6 relative"
         style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-black/50"></div>

        <div class="relative">
            <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP" class="w-28 h-28 object-contain mx-auto mb-6">

            <h1 class="text-4xl font-bold mb-4">Selamat Datang di Sistem Inventaris Bengkel</h1>

            <p class="max-w-2xl mx-auto text-lg mb-8">
                Sistem pengelolaan inventaris alat dan bahan bengkel yang memudahkan
                pengawasan, pemeliharaan, peminjaman, dan pelaporan aset bengkel secara terpusat.
            </p>

            <a href="{{ route('login') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition">
                Masuk
            </a>
        </div>
    </div>
</body>
</html>
