<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json([
            'data' => [
                'status' => 'ok',
                'database' => 'connected',
            ],
        ]);
    } catch (Throwable) {
        return response()->json([
            'message' => 'Service unavailable',
            'errors' => [
                'database' => ['Connection failed'],
            ],
        ], 503);
    }
});
