<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ClientController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{id}', [ClientController::class, 'show'])->whereNumber('id')->name('clients.show');
});
