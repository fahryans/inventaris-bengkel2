<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-white antialiased">
        <div class="min-h-screen flex items-center justify-center px-6 py-12 relative"
             style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-black/60 z-0"></div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP"
                     class="w-24 h-24 object-contain mb-6 drop-shadow-lg">

                <div class="w-full sm:max-w-md px-8 py-8 bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl shadow-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
