@extends('layouts.notes')

@section('title', 'Edit Note')

@section('content')
    <div class="card stack">
        <div>
            <h1>Edit note</h1>
            <p class="lead">PUT/PATCH via <code>@method('PUT')</code> because HTML forms only support GET/POST.</p>
        </div>

        <form action="{{ route('notes.update', $note) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title', $note->title) }}" required maxlength="120">
                @error('title')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="body">Body</label>
                <textarea id="body" name="body" required maxlength="5000">{{ old('body', $note->body) }}</textarea>
                @error('body')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Update note</button>
                <a class="btn btn-secondary" href="{{ route('notes.show', $note) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
