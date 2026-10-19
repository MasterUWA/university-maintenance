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
        <div>
            <dt class="font-semibold">Time limit</dt>
            <dd>
                {{ $request->due_at?->format('M j, Y H:i') }}
                @if($request->isOverdue())<span class="ml-2 rounded bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">Overdue</span>
                @elseif($request->isAtRisk())<span class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">At risk</span>@endif
            </dd>
        </div>
        @if($request->assignments->isNotEmpty())
            <div><dt class="font-semibold">Assigned to</dt><dd>{{ $request->assignments->sortByDesc('id')->first()->officer->name }}</dd></div>
        @endif
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

    @if($request->statusUpdates->isNotEmpty())
        <div class="mt-6 border-t pt-4">
            <h2 class="font-semibold">Timeline</h2>
            <ol class="mt-2 space-y-2 border-l-2 border-[#d8e6e1] pl-4 text-sm">
                @foreach($request->statusUpdates as $update)
                    <li>
                        <span class="font-semibold">{{ str_replace('_', ' ', $update->status) }}</span>
                        <span class="text-gray-500">by {{ $update->officer->name }} &middot; {{ $update->created_at->diffForHumans() }}</span>
                        @if($update->comment)<p class="text-gray-600">{{ $update->comment }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    @if($canUpdateStatus)
        <form method="POST" action="{{ route('requests.status', $request) }}" class="mt-6 space-y-3 border-t pt-4">
            @csrf
            @method('PATCH')
            <h2 class="font-semibold">Update status</h2>
            <div class="grid gap-3 sm:grid-cols-[12rem_1fr_auto]">
                <select name="status" class="rounded border px-3 py-2">
                    @foreach(['ASSIGNED','IN_PROGRESS','COMPLETED','REJECTED','CANCELLED'] as $status)
                        <option value="{{ $status }}" @selected($request->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <input name="comment" maxlength="1000" placeholder="Optional note for the requester" class="rounded border px-3 py-2" />
                <button class="rounded bg-[#18322d] px-4 py-2 font-semibold text-white">Update</button>
            </div>
        </form>
    @endif

    @if($request->status === 'COMPLETED' && $request->rating !== null)
        <div class="mt-6 border-t pt-4">
            <h2 class="font-semibold">Feedback</h2>
            <p class="mt-1 text-sm">{{ str_repeat('★', $request->rating) }}{{ str_repeat('☆', 5 - $request->rating) }} ({{ $request->rating }}/5)</p>
            @if($request->rating_comment)<p class="text-sm text-gray-600">{{ $request->rating_comment }}</p>@endif
        </div>
    @elseif($request->status === 'COMPLETED' && $request->requester_id === auth()->id())
        <form method="POST" action="{{ route('requests.rate', $request) }}" class="mt-6 space-y-3 border-t pt-4">
            @csrf
            <h2 class="font-semibold">How did we do?</h2>
            <div class="grid gap-3 sm:grid-cols-[8rem_1fr]">
                <select name="rating" required class="rounded border px-3 py-2">
                    <option value="">Rating</option>
                    @foreach([5,4,3,2,1] as $score)<option value="{{ $score }}">{{ $score }} / 5</option>@endforeach
                </select>
                <input name="rating_comment" maxlength="1000" placeholder="Optional comment" class="rounded border px-3 py-2" />
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="reopen" value="1" /> The issue is not resolved &mdash; reopen this request</label>
            <button class="rounded bg-[#18322d] px-4 py-2 font-semibold text-white">Submit feedback</button>
        </form>
    @endif

    <section class="mt-6 border-t pt-4">
        <h2 class="font-semibold">Comments</h2>
        <div class="mt-3 space-y-3">
            @forelse($comments as $comment)
                <div class="rounded border p-3 text-sm {{ $comment->is_internal ? 'border-amber-300 bg-amber-50' : '' }}">
                    <p class="font-semibold">{{ $comment->user->name }}
                        @if($comment->is_internal)<span class="ml-1 rounded bg-amber-200 px-1.5 py-0.5 text-xs">Internal</span>@endif
                        <span class="font-normal text-gray-500">&middot; {{ $comment->created_at->diffForHumans() }}</span>
                    </p>
                    <p class="mt-1 whitespace-pre-line">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No comments yet.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('requests.comments.store', $request) }}" class="mt-4 space-y-2">
            @csrf
            <textarea name="body" required rows="2" maxlength="2000" placeholder="Write a comment..." class="w-full rounded border px-3 py-2"></textarea>
            @unlessrole('STUDENT_STAFF')
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_internal" value="1" /> Internal note (hidden from the requester)</label>
            @endunlessrole
            <button class="rounded bg-[#18322d] px-4 py-2 font-semibold text-white">Post comment</button>
        </form>
    </section>

    @role('ADMIN')
        <section class="mt-6 border-t pt-4">
            <h2 class="font-semibold">Assignment</h2>
            <div class="mt-3 flex flex-wrap items-start gap-3">
                <form method="POST" action="{{ route('admin.requests.auto-assign', $request) }}">
                    @csrf
                    <button class="rounded bg-[#e5a72f] px-4 py-2 font-semibold text-[#18322d]">Auto-assign (lightest workload)</button>
                </form>
                <form method="POST" action="{{ route('admin.requests.assign', $request) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <select name="officer_id" required class="rounded border px-3 py-2">
                        <option value="">Choose officer</option>
                        @foreach($officers as $officer)<option value="{{ $officer->id }}">{{ $officer->name }}</option>@endforeach
                    </select>
                    <button class="rounded border px-4 py-2 font-semibold">Assign</button>
                </form>
            </div>
        </section>
    @endrole

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
