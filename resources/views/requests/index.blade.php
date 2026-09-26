@extends('layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-4">Requests</h1>

<form method="GET" class="flex gap-2 mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search..."
           class="border rounded px-3 py-2 w-64" />
    <select name="status" class="border rounded px-3 py-2">
        <option value="">All statuses</option>
        @foreach(['PENDING','ASSIGNED','IN_PROGRESS','COMPLETED'] as $s)
            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<div class="space-y-2">
    @foreach($requests as $r)
        <a href="{{ route('requests.show', $r) }}"
           class="block bg-white p-4 rounded shadow hover:shadow-md">
            <div class="flex justify-between">
                <div>
                    <h3 class="font-semibold">{{ $r->title }}</h3>
                    <p class="text-sm text-gray-600">{{ $r->location }} — {{ $r->category->name }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded bg-gray-100 h-fit">{{ $r->status }}</span>
            </div>
        </a>
    @endforeach
</div>

<div class="mt-4">{{ $requests->links() }}</div>
@endsection
