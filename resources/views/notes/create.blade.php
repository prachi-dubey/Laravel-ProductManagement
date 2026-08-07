@extends('layouts.notes')

@section('title', 'Create Note')

@section('content')
    <div class="card stack">
        <div>
            <h1>Create note</h1>
            <p class="lead">POST form → validation → redirect with flash message.</p>
        </div>

        <form action="{{ route('notes.store') }}" method="POST">
            @csrf

            <div class="field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required maxlength="120">
                @error('title')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="body">Body</label>
                <textarea id="body" name="body" required maxlength="5000">{{ old('body') }}</textarea>
                @error('body')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Save note</button>
                <a class="btn btn-secondary" href="{{ route('notes.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
