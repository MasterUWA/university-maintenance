<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare | University Maintenance</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-page min-h-screen text-[#18322d] antialiased dark:text-[#e7f2ee]">
    <div class="relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_rgba(244,183,64,0.24),_transparent_34%),radial-gradient(circle_at_10%_20%,_rgba(46,125,111,0.18),_transparent_32%)]"></div>
        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="CampusCare home">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1f6f63] text-lg font-bold text-white shadow-lg shadow-[#1f6f63]/20">C</span>
                <span><span class="block text-lg font-bold tracking-tight">CampusCare</span><span class="block text-xs font-medium uppercase tracking-[0.2em] text-[#6b817b]">Facilities desk</span></span>
            </a>
            <nav class="flex items-center gap-3 text-sm font-semibold">
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-[#18322d] px-5 py-2.5 text-white transition hover:bg-[#285248]">Open workspace</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-4 py-2.5 text-[#31534b] transition hover:bg-white/70">Sign in</a>
                    @if (Route::has('register'))<a href="{{ route('register') }}" class="rounded-full bg-[#18322d] px-5 py-2.5 text-white transition hover:bg-[#285248]">Create account</a>@endif
                @endauth
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-6 pb-20 pt-12 lg:px-8 lg:pt-20">
            <section class="grid items-center gap-14 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="max-w-2xl">
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#b9d6ce] bg-white/70 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-[#39766b]"><span class="h-2 w-2 rounded-full bg-[#e5a72f]"></span>University operations, in one place</p>
                    <h1 class="max-w-xl text-5xl font-bold leading-[0.98] tracking-[-0.04em] text-[#18322d] sm:text-6xl">Keep every campus space moving.</h1>
                    <p class="mt-7 max-w-xl text-lg leading-8 text-[#56706a]">Report a fault, follow its progress, and give facilities teams the context they need to resolve it quickly.</p>
                    <div class="mt-9 flex flex-wrap items-center gap-4">
                        @auth
                            @role('STUDENT_STAFF|ADMIN')
                            <a href="{{ route('requests.create') }}" class="rounded-full bg-[#e5a72f] px-6 py-3.5 font-bold text-[#18322d] shadow-lg shadow-[#e5a72f]/20 transition hover:-translate-y-0.5 hover:bg-[#f0ba4b]">Report an issue <span aria-hidden="true">-&gt;</span></a>
                            @endrole
                            <a href="{{ route('requests.index') }}" class="font-bold text-[#315f56] underline decoration-[#a9c9c0] underline-offset-4 hover:text-[#18322d]">View active requests</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full bg-[#e5a72f] px-6 py-3.5 font-bold text-[#18322d] shadow-lg shadow-[#e5a72f]/20 transition hover:-translate-y-0.5 hover:bg-[#f0ba4b]">Enter the portal <span aria-hidden="true">-&gt;</span></a>
                            <span class="text-sm text-[#6b817b]">For students, staff, officers, and administrators</span>
                        @endauth
                    </div>
                </div>
                <div class="relative rounded-[2rem] border border-white/80 bg-[#18322d] p-5 shadow-2xl shadow-[#18322d]/20">
                    <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full border-[12px] border-[#e5a72f]/80"></div>
                    <div class="relative rounded-[1.4rem] bg-[#23483f] p-6 text-white">
                        <div class="flex items-center justify-between border-b border-white/10 pb-5"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-[#b9d6ce]">Request pulse</p><p class="mt-2 text-2xl font-bold">A clearer service day</p></div><span class="rounded-full bg-[#e5a72f] px-3 py-1 text-xs font-bold text-[#18322d]">Live</span></div>
                        <div class="mt-6 space-y-3">
                            <div class="rounded-xl bg-white/10 p-4"><div class="flex items-center justify-between text-sm"><span>Science block lighting</span><span class="text-[#f0ba4b]">In progress</span></div><div class="mt-3 h-1.5 rounded-full bg-white/10"><div class="h-1.5 w-3/4 rounded-full bg-[#e5a72f]"></div></div></div>
                            <div class="rounded-xl bg-white/10 p-4"><div class="flex items-center justify-between text-sm"><span>Library network point</span><span class="text-[#b9d6ce]">Assigned</span></div><div class="mt-3 h-1.5 rounded-full bg-white/10"><div class="h-1.5 w-2/5 rounded-full bg-[#8bc4b5]"></div></div></div>
                            <div class="flex items-center gap-3 rounded-xl border border-dashed border-white/20 p-4 text-sm text-[#b9d6ce]"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#e5a72f] font-bold text-[#18322d]">+</span>New requests go straight to the facilities queue.</div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="mt-24 grid gap-5 border-t border-[#cfe0db] pt-8 sm:grid-cols-3">
                <div><p class="text-3xl font-bold">01</p><h2 class="mt-2 font-bold">Report clearly</h2><p class="mt-2 text-sm leading-6 text-[#6b817b]">Add the location, category, priority, and details in one guided request.</p></div>
                <div><p class="text-3xl font-bold">02</p><h2 class="mt-2 font-bold">Track progress</h2><p class="mt-2 text-sm leading-6 text-[#6b817b]">See when a request is pending, assigned, in progress, or completed.</p></div>
                <div><p class="text-3xl font-bold">03</p><h2 class="mt-2 font-bold">Resolve together</h2><p class="mt-2 text-sm leading-6 text-[#6b817b]">Officers and administrators work from the same live service record.</p></div>
            </section>
        </main>
        <x-site-footer />
    </div>
</body>
</html>
