@extends('layouts.app')

@section('content')
<article class="max-w-3xl bg-white p-6 rounded shadow">
    <div class="flex justify-between gap-4 mb-4">
        <h1 class="text-2xl font-bold">{{ $request->title }}</h1>
        <span class="text-sm px-2 py-1 rounded bg-gray-100 h-fit">{{ $request->status }}</span>
    </div>
    <dl class="space-y-3">
        <div><dt class="font-semibold">Description</dt><dd>{{ $request->description }}</dd></div>
        <div><dt class="font-semibold">Location</dt><dd>{{ $request->location }}</dd></div>
        <div><dt class="font-semibold">Category</dt><dd>{{ $request->category->name }}</dd></div>
        <div><dt class="font-semibold">Priority</dt><dd>{{ $request->priority }}</dd></div>
    </dl>
    @if($request->attachments->isNotEmpty())
        <div class="mt-6 border-t pt-4">
            <h2 class="font-semibold">Evidence</h2>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach($request->attachments as $attachment)
                    <li><a class="text-blue-600 underline" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->path) }}" target="_blank">{{ $attachment->original_name }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    @role('ADMIN')
        <details class="mt-6 border-t pt-4">
            <summary class="cursor-pointer font-semibold">Admin actions</summary>
            <form method="POST" action="{{ route('admin.requests.update', $request) }}" class="mt-4 space-y-3">
                @csrf
                @method('PATCH')
                <input name="title" value="{{ $request->title }}" required class="w-full rounded border px-3 py-2" />
                <textarea name="description" required rows="3" class="w-full rounded border px-3 py-2">{{ $request->description }}</textarea>
                <input name="location" value="{{ $request->location }}" required class="w-full rounded border px-3 py-2" />
                <div class="grid gap-3 sm:grid-cols-3">
                    <select name="category_id" class="rounded border px-3 py-2">
                        @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                            <option value="{{ $category->id }}" @selected($request->category_id === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="rounded border px-3 py-2">
                        @foreach(['LOW','MEDIUM','HIGH','URGENT'] as $priority)
                            <option value="{{ $priority }}" @selected($request->priority === $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded border px-3 py-2">
                        @foreach(['PENDING','ASSIGNED','IN_PROGRESS','COMPLETED','REJECTED','CANCELLED'] as $status)
                            <option value="{{ $status }}" @selected($request->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded bg-[#18322d] px-4 py-2 font-semibold text-white">Save changes</button>
            </form>
            <form method="POST" action="{{ route('admin.requests.destroy', $request) }}" class="mt-3" onsubmit="return confirm('Delete this service request?');">
                @csrf
                @method('DELETE')
                <button class="rounded border border-red-300 px-4 py-2 font-semibold text-red-600">Delete request</button>
            </form>
        </details>
    @endrole
</article>
@endsection
