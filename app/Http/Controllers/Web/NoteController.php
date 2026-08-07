<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreNoteRequest;
use App\Http\Requests\Web\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $notes = Note::query()
            ->latest()
            ->get();

        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        Note::query()->create($request->validated());
// Only the fields defined in your validation rules are returned.
// For create() to work, your model must allow mass assignment.
        return redirect()
            ->route('notes.index')
            ->with('success', 'Note created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note): View
    {
        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified resource in storage.
     */
    // Route Model Binding.
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $note->update($request->validated());

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note deleted successfully.');
    }
}
