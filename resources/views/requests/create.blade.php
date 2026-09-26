@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" class="max-w-xl bg-white p-6 rounded shadow">
    @csrf
    <h2 class="text-2xl font-bold mb-4">New Service Request</h2>

    <input name="title" placeholder="Title" value="{{ old('title') }}"
           class="w-full border rounded px-3 py-2 mb-2" />
    @error('title') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

    <textarea name="description" placeholder="Describe the problem" rows="4"
              class="w-full border rounded px-3 py-2 mb-2">{{ old('description') }}</textarea>
    @error('description') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

    <input name="location" placeholder="Location" value="{{ old('location') }}"
           class="w-full border rounded px-3 py-2 mb-2" />

    <select name="category_id" class="w-full border rounded px-3 py-2 mb-2">
        <option value="">Select category</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>

    <select name="priority" class="w-full border rounded px-3 py-2 mb-4">
        @foreach(['LOW','MEDIUM','HIGH','URGENT'] as $p)
            <option value="{{ $p }}" @selected(old('priority', 'MEDIUM') == $p)>{{ $p }}</option>
        @endforeach
    </select>

        <label for="evidence" class="block text-sm font-medium mb-2">Evidence (optional)</label>
        <input id="evidence" name="evidence[]" type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf"
            class="w-full border rounded px-3 py-2 mb-4" />
        @error('evidence.*') <p class="text-red-500 text-sm mb-2">{{ $message }}</p> @enderror

    <button class="bg-blue-600 text-white px-4 py-2 rounded w-full">Submit Request</button>
</form>
@endsection
