<?php

use Illuminate\Support\Facades\Route;
use Modules\Launchpad\Http\Controllers\LaunchpadController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('launchpad', [LaunchpadController::class, 'index'])->name('launchpad.index');
    Route::post('launchpad', [LaunchpadController::class, 'store'])->name('launchpad.store');
    Route::get('launchpad/{launch}', [LaunchpadController::class, 'show'])->name('launchpad.show');
});
