<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\NoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('notes.index')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Blade Notes demo — session auth (Breeze cookie), not Sanctum
    Route::resource('notes', NoteController::class);
});

require __DIR__.'/auth.php';
