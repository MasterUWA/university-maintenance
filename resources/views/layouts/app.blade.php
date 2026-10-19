<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Workspace' }} | CampusCare</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-page min-h-screen text-[#18322d] antialiased dark:text-[#e7f2ee]">
    <x-particles />
    <div class="min-h-screen lg:flex">
        <aside class="border-b border-[#d8e6e1] bg-[#18322d] text-white lg:flex lg:w-72 lg:flex-col lg:border-b-0 lg:border-r lg:border-white/10">
            <div class="flex items-center justify-between px-6 py-5 lg:block lg:px-7 lg:py-7"><a href="{{ route('dashboard') }}" class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#e5a72f] font-bold text-[#18322d]">C</span><span><span class="block font-bold">CampusCare</span><span class="block text-xs text-[#9fc2b7]">Facilities workspace</span></span></a><span class="hidden rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-[#b9d6ce] lg:mt-8 lg:inline-block">{{ auth()->user()->getRoleNames()->first() ?? 'Member' }}</span></div>
            <nav class="flex gap-2 overflow-x-auto px-5 pb-5 text-sm lg:block lg:space-y-2 lg:px-5">
                <a href="{{ route('dashboard') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-white text-[#18322d]' : 'text-[#b9d6ce] hover:bg-white/10 hover:text-white' }}">Overview</a>
                <a href="{{ route('requests.index') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold transition {{ request()->routeIs('requests.*') ? 'bg-white text-[#18322d]' : 'text-[#b9d6ce] hover:bg-white/10 hover:text-white' }}">Service requests</a>
                <a href="{{ route('notifications.index') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold text-[#b9d6ce] transition hover:bg-white/10 hover:text-white">Notifications</a>
                @role('STUDENT_STAFF')<a href="{{ route('requests.create') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold text-[#b9d6ce] transition hover:bg-white/10 hover:text-white">New request</a>@endrole
                @role('ADMIN')<a href="{{ route('admin.requests') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold text-[#b9d6ce] transition hover:bg-white/10 hover:text-white">Admin queue</a><a href="{{ route('admin.analytics') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold {{ request()->routeIs('admin.analytics') ? 'bg-white text-[#18322d]' : 'text-[#b9d6ce] hover:bg-white/10 hover:text-white' }} transition">Analytics</a><a href="{{ route('admin.requests.export') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold text-[#b9d6ce] transition hover:bg-white/10 hover:text-white">Export CSV</a>@endrole
                @role('OFFICER')<a href="{{ route('officer.assignments') }}" class="block whitespace-nowrap rounded-xl px-4 py-3 font-semibold text-[#b9d6ce] transition hover:bg-white/10 hover:text-white">My assignments</a>@endrole
            </nav>
            <div class="mt-auto hidden border-t border-white/10 p-5 lg:block"><p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-[#9fc2b7]">{{ auth()->user()->email }}</p><div class="mt-4 flex items-center justify-between gap-2"><a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-[#e5a72f] hover:text-[#f0ba4b]">Profile</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm text-[#b9d6ce] hover:text-white">Log out</button></form></div></div>
        </aside>
        <main class="min-w-0 flex-1"><header class="flex items-center justify-between border-b border-[#d8e6e1] bg-white/75 px-6 py-5 backdrop-blur dark:border-white/10 dark:bg-[#10251f]/75 lg:px-10"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-[#7a938b]">University maintenance</p><p class="mt-1 text-sm text-[#56706a] dark:text-[#b9d6ce]">Good to see you, <strong class="text-[#18322d] dark:text-white">{{ auth()->user()->name }}</strong>.</p></div><div class="flex items-center gap-3"><x-theme-toggle />@role('STUDENT_STAFF|ADMIN')<a href="{{ route('requests.create') }}" class="hidden rounded-full bg-[#e5a72f] px-5 py-2.5 text-sm font-bold text-[#18322d] transition hover:bg-[#f0ba4b] sm:inline-block">+ New request</a>@endrole</div></header><div class="p-6 lg:p-10">@if(session('success') || session('status'))<div class="mb-6 rounded-xl border border-[#b9d6ce] bg-[#e9f5f1] px-4 py-3 text-sm font-semibold text-[#286457]">{{ session('success') ?? session('status') }}</div>@endif @yield('content')</div><x-site-footer /></main>
    </div>
</body>
</html>
