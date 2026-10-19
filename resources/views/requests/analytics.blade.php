@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl">
    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">Admin</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight">Analytics</h1>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl bg-[#18322d] p-5 text-white"><p class="text-sm text-[#b9d6ce]">Total requests</p><p class="mt-3 text-3xl font-bold">{{ $total }}</p></div>
        <div class="rounded-2xl border border-[#d8e6e1] bg-white p-5"><p class="text-sm text-gray-600">Open</p><p class="mt-3 text-3xl font-bold">{{ $open }}</p></div>
        <div class="rounded-2xl border border-red-200 bg-white p-5"><p class="text-sm text-red-600">Overdue</p><p class="mt-3 text-3xl font-bold text-red-600">{{ $overdue }}</p></div>
        <div class="rounded-2xl border border-[#d8e6e1] bg-white p-5"><p class="text-sm text-gray-600">Avg. resolution</p><p class="mt-3 text-3xl font-bold">{{ $averageHours !== null ? $averageHours.' h' : 'n/a' }}</p></div>
        <div class="rounded-2xl border border-[#d8e6e1] bg-white p-5"><p class="text-sm text-gray-600">Avg. rating</p><p class="mt-3 text-3xl font-bold">{{ $averageRating !== null ? number_format($averageRating, 1).' / 5' : 'n/a' }}</p><p class="mt-1 text-xs text-gray-500">{{ $ratedCount }} rated</p></div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        @php
            $bars = [
                'By status' => $byStatus->mapWithKeys(fn ($count, $label) => [str_replace('_', ' ', $label) => $count]),
                'By category' => $byCategory->pluck('service_requests_count', 'name'),
                'Busiest locations' => $byLocation->pluck('total', 'location'),
            ];
        @endphp
        @foreach($bars as $heading => $rows)
            <section class="rounded-2xl border border-[#d8e6e1] bg-white p-6">
                <h2 class="text-lg font-bold">{{ $heading }}</h2>
                @php $max = max(1, $rows->max() ?? 0); @endphp
                <div class="mt-4 space-y-3">
                    @forelse($rows as $label => $count)
                        <div>
                            <div class="flex justify-between text-sm"><span class="truncate">{{ $label }}</span><span class="font-bold">{{ $count }}</span></div>
                            <div class="mt-1 h-2 rounded-full bg-[#edf2f0]"><div class="h-2 rounded-full bg-[#39766b]" style="width: {{ round($count / $max * 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No data yet.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
