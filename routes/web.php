<?php

use App\Http\Controllers\StatusPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Invitation acceptance — redirects to Filament register with token
Route::get('/invite/{token}', function (string $token) {
    return redirect(route('filament.admin.auth.register', ['token' => $token]));
})->name('invitation.accept');

// Public status pages
Route::get('/status',        [StatusPageController::class, 'index'])->name('status-page.index');
Route::get('/status/{slug}', [StatusPageController::class, 'show'])->name('status-page.show');
