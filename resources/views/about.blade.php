<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About CampusCare | University Maintenance</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-page min-h-screen text-[#18322d] antialiased dark:text-[#e7f2ee]">
    <div class="relative isolate overflow-hidden">
        <x-particles />
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_rgba(244,183,64,0.24),_transparent_34%),radial-gradient(circle_at_10%_20%,_rgba(46,125,111,0.18),_transparent_32%)]"></div>
        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="CampusCare home">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1f6f63] text-lg font-bold text-white shadow-lg shadow-[#1f6f63]/20">C</span>
                <span><span class="block text-lg font-bold tracking-tight">CampusCare</span><span class="block text-xs font-medium uppercase tracking-[0.2em] text-[#6b817b]">Facilities desk</span></span>
            </a>
            <nav class="flex items-center gap-3 text-sm font-semibold">
                <a href="{{ url('/') }}" class="rounded-full px-4 py-2.5 text-[#31534b] transition hover:bg-white/70">Home</a>
                <a href="{{ route('about') }}" aria-current="page" class="rounded-full bg-white/70 px-4 py-2.5 text-[#18322d]">About</a>
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-[#18322d] px-5 py-2.5 text-white transition hover:bg-[#285248]">Open workspace</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-[#18322d] px-5 py-2.5 text-white transition hover:bg-[#285248]">Sign in</a>
                @endauth
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-6 pb-20 pt-12 lg:px-8 lg:pt-20">
            <section class="max-w-3xl">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#b9d6ce] bg-white/70 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-[#39766b]">About CampusCare</p>
                <h1 class="text-5xl font-bold leading-tight tracking-[-0.04em] text-[#18322d] sm:text-6xl">Better campus spaces start with clear communication.</h1>
                <p class="mt-7 text-lg leading-8 text-[#56706a]">CampusCare is a university maintenance service desk that connects students and staff with the facilities teams who keep campus spaces safe, welcoming, and working.</p>
            </section>

            <section class="mt-16 grid gap-6 md:grid-cols-3" aria-label="How CampusCare works">
                <article class="rounded-2xl border border-white/80 bg-white/70 p-6 shadow-lg shadow-[#18322d]/5 dark:border-white/10 dark:bg-[#142a24]/70">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">01 · Report</p>
                    <h2 class="mt-3 text-xl font-bold">Share the details</h2>
                    <p class="mt-3 leading-7 text-[#56706a]">Submit a maintenance issue with its location, category, priority, and a description so the team has the context to act.</p>
                </article>
                <article class="rounded-2xl border border-white/80 bg-white/70 p-6 shadow-lg shadow-[#18322d]/5 dark:border-white/10 dark:bg-[#142a24]/70">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">02 · Follow</p>
                    <h2 class="mt-3 text-xl font-bold">Track progress</h2>
                    <p class="mt-3 leading-7 text-[#56706a]">Check request updates as work moves from pending and assignment through to completion.</p>
                </article>
                <article class="rounded-2xl border border-white/80 bg-white/70 p-6 shadow-lg shadow-[#18322d]/5 dark:border-white/10 dark:bg-[#142a24]/70">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">03 · Resolve</p>
                    <h2 class="mt-3 text-xl font-bold">Work as one team</h2>
                    <p class="mt-3 leading-7 text-[#56706a]">Facilities officers and administrators coordinate assignments and status updates in a shared service queue.</p>
                </article>
            </section>

            <section class="mt-16 flex flex-col items-start justify-between gap-6 rounded-3xl bg-[#18322d] p-8 text-white sm:flex-row sm:items-center sm:p-10">
                <div>
                    <h2 class="text-2xl font-bold">Need to report a campus issue?</h2>
                    <p class="mt-2 text-[#b9d6ce]">Sign in to the CampusCare portal to submit and follow a service request.</p>
                </div>
                <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-[#e5a72f] px-6 py-3.5 font-bold text-[#18322d] transition hover:bg-[#f0ba4b]">Go to sign in <span aria-hidden="true">-&gt;</span></a>
            </section>
        </main>
        <x-site-footer />
    </div>
</body>
</html>
