@extends('layouts.notes')

@section('title', 'All Notes')

@section('content')
    <div class="card stack">
        <div class="actions" style="justify-content: space-between;">
            <div>
                <h1>Notes</h1>
                <p class="lead">Simple Blade CRUD — create, read, update, delete.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('notes.create') }}">New note</a>
        </div>

        @if ($notes->isEmpty())
            <p class="empty">No notes yet. Create your first one.</p>
        @else
            <ul class="note-list">
                @foreach ($notes as $note)
                    <li>
                        <div>
                            <a href="{{ route('notes.show', $note) }}"><strong>{{ $note->title }}</strong></a>
                            <div class="note-meta">Updated {{ $note->updated_at->diffForHumans() }}</div>
                        </div>
                        <div class="actions">
                            <a class="btn btn-secondary" href="{{ route('notes.edit', $note) }}">Edit</a>
                            <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Delete this note?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
