<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'CampusCare') }} | University Maintenance</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="theme-page font-sans text-gray-900 antialiased dark:text-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-6 py-8">
            <div class="mb-5 flex w-full max-w-md justify-end"><x-theme-toggle /></div>
            <div>
                <a href="/" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#1f6f63] text-lg font-bold text-white">C</span>
                    <span class="text-lg font-bold text-[#18322d] dark:text-white">CampusCare</span>
                </a>
            </div>

            <div class="mt-6 w-full max-w-md overflow-hidden rounded-2xl border border-white/80 bg-white/90 px-6 py-6 shadow-xl shadow-[#18322d]/10 dark:border-white/10 dark:bg-[#142a24]/90">
                {{ $slot }}
            </div>
            <x-site-footer />
        </div>
    </body>
</html>
