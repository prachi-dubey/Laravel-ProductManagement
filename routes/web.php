<?php

use App\Http\Controllers\Web\NoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('notes.index');
});

/*
|--------------------------------------------------------------------------
| Blade demo: Notes CRUD (unrelated to shop APIs)
|--------------------------------------------------------------------------
| Resource controller maps REST-style routes automatically:
| GET    /notes           → index
| GET    /notes/create    → create
| POST   /notes           → store
| GET    /notes/{note}    → show
| GET    /notes/{note}/edit → edit
| PUT/PATCH /notes/{note} → update
| DELETE /notes/{note}    → destroy
*/
Route::resource('notes', NoteController::class);
