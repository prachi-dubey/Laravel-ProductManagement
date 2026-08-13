<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Shop API is running. Use Postman for /api endpoints.',
        'data' => [
            'health' => url('/up'),
            'auth' => [
                'register' => url('/api/register'),
                'login' => url('/api/login'),
            ],
        ],
    ]);
});
