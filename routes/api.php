<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProposalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{id}', [ClientController::class, 'show'])->whereNumber('id')->name('clients.show');

    Route::get('proposals', [ProposalController::class, 'index'])->name('proposals.index');
    Route::post('proposals', [ProposalController::class, 'store'])->name('proposals.store');
    Route::get('proposals/{id}', [ProposalController::class, 'show'])->whereNumber('id')->name('proposals.show');
    Route::patch('proposals/{id}', [ProposalController::class, 'update'])->whereNumber('id')->name('proposals.update');
    Route::delete('proposals/{id}', [ProposalController::class, 'destroy'])->whereNumber('id')->name('proposals.destroy');

    Route::post('proposals/{id}/submit', [ProposalController::class, 'submit'])->whereNumber('id')->name('proposals.submit');
    Route::post('proposals/{id}/approve', [ProposalController::class, 'approve'])->whereNumber('id')->name('proposals.approve');
    Route::post('proposals/{id}/reject', [ProposalController::class, 'reject'])->whereNumber('id')->name('proposals.reject');
    Route::post('proposals/{id}/cancel', [ProposalController::class, 'cancel'])->whereNumber('id')->name('proposals.cancel');

    Route::get('proposals/{id}/audit', [ProposalController::class, 'audit'])->whereNumber('id')->name('proposals.audit');
});
