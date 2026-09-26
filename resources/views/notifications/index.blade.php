@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="flex items-end justify-between gap-4">
        <div><p class="text-sm font-bold uppercase tracking-[0.16em] text-[#39766b]">Updates</p><h1 class="mt-2 text-4xl font-bold">Notifications</h1></div>
    </div>
    <div class="mt-8 divide-y divide-[#edf2f0] rounded-2xl border border-[#d8e6e1] bg-white px-6">
        @forelse($notifications as $notification)
            <div class="flex items-start justify-between gap-4 py-5 {{ $notification->read_at ? 'opacity-60' : '' }}">
                <div><p class="font-bold">{{ $notification->title }}</p><p class="mt-1 text-sm text-[#6b817b]">{{ $notification->message }}</p><p class="mt-2 text-xs text-[#9aada7]">{{ $notification->created_at->diffForHumans() }}</p></div>
                @if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf @method('PATCH')<button class="text-xs font-bold text-[#39766b]">Mark read</button></form>@endif
            </div>
        @empty
            <p class="py-10 text-center text-sm text-[#6b817b]">You are all caught up.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $notifications->links() }}</div>
</div>
@endsection