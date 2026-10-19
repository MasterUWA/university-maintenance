@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">Officer</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight">My assignments</h1>
    <p class="mt-2 text-[#6b817b]">Open work sorted by priority, then by time limit.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-[#18322d] p-5 text-white"><p class="text-sm text-[#b9d6ce]">Open</p><p class="mt-3 text-3xl font-bold">{{ $assignments->count() }}</p></div>
        <div class="rounded-2xl border border-red-200 bg-white p-5"><p class="text-sm text-red-600">Overdue</p><p class="mt-3 text-3xl font-bold text-red-600">{{ $overdueCount }}</p></div>
        <div class="rounded-2xl border border-amber-200 bg-white p-5"><p class="text-sm text-amber-700">At risk (under 12h)</p><p class="mt-3 text-3xl font-bold text-amber-700">{{ $atRiskCount }}</p></div>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-center gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search my assignments..." class="w-72 rounded border px-3 py-2" />
        <select name="priority" class="rounded border px-3 py-2">
            <option value="">All priorities</option>
            @foreach(['URGENT','HIGH','MEDIUM','LOW'] as $p)<option value="{{ $p }}" @selected(request('priority') == $p)>{{ $p }}</option>@endforeach
        </select>
        <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="overdue" value="1" @checked(request()->boolean('overdue')) /> Overdue only</label>
        <button class="rounded bg-[#18322d] px-4 py-2 font-semibold text-white">Search</button>
        @if(request()->hasAny(['search','priority','overdue']))<a href="{{ url()->current() }}" class="text-sm underline">Clear</a>@endif
    </form>

    <div class="mt-6 space-y-2">
        @forelse($assignments as $item)
            <a href="{{ route('requests.show', $item) }}" class="block rounded-2xl border bg-white p-4 shadow-sm hover:shadow-md {{ $item->isOverdue() ? 'border-red-300' : 'border-[#d8e6e1]' }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-bold">{{ $item->title }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $item->category->name }} &middot; {{ $item->location }} &middot; {{ $item->requester->name }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold">
                        <span class="rounded-full bg-gray-100 px-3 py-1">{{ $item->priority }}</span>
                        <span class="rounded-full bg-[#e9f5f1] px-3 py-1 text-[#39766b]">{{ str_replace('_', ' ', $item->status) }}</span>
                        @if($item->isOverdue())<span class="rounded-full bg-red-100 px-3 py-1 text-red-700">Overdue {{ $item->due_at->diffForHumans() }}</span>
                        @else<span class="rounded-full px-3 py-1 {{ $item->isAtRisk() ? 'bg-amber-100 text-amber-700' : 'bg-gray-100' }}">Due {{ $item->due_at->diffForHumans() }}</span>@endif
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-[#d8e6e1] bg-white p-10 text-center"><p class="font-bold">Nothing assigned right now</p><p class="mt-2 text-sm text-gray-500">New assignments will appear here.</p></div>
        @endforelse
    </div>
</div>
@endsection
