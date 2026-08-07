@extends('layouts.notes')

@section('title', $note->title)

@section('content')
    <div class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>{{ $note->title }}</h1>
                <p class="lead">Created {{ $note->created_at->toDayDateTimeString() }}</p>
            </div>
            <div class="actions">
                <a class="btn btn-secondary" href="{{ route('notes.edit', $note) }}">Edit</a>
                <a class="btn btn-secondary" href="{{ route('notes.index') }}">Back</a>
            </div>
        </div>

        <div class="body-text">{{ $note->body }}</div>
    </div>
@endsection
