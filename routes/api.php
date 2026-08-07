<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Postman)
|--------------------------------------------------------------------------
| Prefixed with /api automatically.
| No auth yet — we will lock these down with Sanctum in a later phase.
|
| Tip in Postman: set Header Accept: application/json
| so validation errors return JSON instead of HTML redirects.
*/

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);

// M:N helper — attach/replace tags on a product
Route::put('products/{product}/tags', [ProductController::class, 'syncTags'])
    ->name('products.tags.sync');
