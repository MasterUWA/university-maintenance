@extends('layouts.app')

@section('content')
<h1 class="text-3xl font-bold mb-4">Requests</h1>

<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
    <input name="search" value="{{ request('search') }}" placeholder="Search title, location, requester, #id..."
           class="border rounded px-3 py-2 w-72" />
    <select name="status" class="border rounded px-3 py-2">
        <option value="">All statuses</option>
        @foreach(['PENDING','ASSIGNED','IN_PROGRESS','COMPLETED','REJECTED','CANCELLED'] as $s)
            <option value="{{ $s }}" @selected(request('status') == $s)>{{ str_replace('_', ' ', $s) }}</option>
        @endforeach
    </select>
    <select name="priority" class="border rounded px-3 py-2">
        <option value="">All priorities</option>
        @foreach(['URGENT','HIGH','MEDIUM','LOW'] as $p)
            <option value="{{ $p }}" @selected(request('priority') == $p)>{{ $p }}</option>
        @endforeach
    </select>
    <select name="category_id" class="border rounded px-3 py-2">
        <option value="">All categories</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="overdue" value="1" @checked(request()->boolean('overdue')) /> Overdue only</label>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
    @if(request()->hasAny(['search','status','priority','category_id','overdue']))
        <a href="{{ url()->current() }}" class="text-sm underline">Clear</a>
    @endif
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
    @if($requests->isEmpty())
        <p class="rounded bg-white p-6 text-center text-gray-500">No requests match your search.</p>
    @endif
</div>

<div class="mt-4">{{ $requests->links() }}</div>
@endsection
