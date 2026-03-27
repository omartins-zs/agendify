<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailabilityRuleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\ServiceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::prefix('book/{company:slug}')->group(function () {
    Route::get('/', [PublicBookingController::class, 'show'])->name('booking.show');
    Route::get('/slots', [PublicBookingController::class, 'slots'])->name('booking.slots');
    Route::post('/appointments', [PublicBookingController::class, 'store'])->name('booking.store');
    Route::get('/success/{appointment}', [PublicBookingController::class, 'success'])->name('booking.success');
    Route::post('/success/{appointment}/cancel', [PublicBookingController::class, 'cancel'])->name('booking.cancel');
});

Route::middleware(['auth', 'verified', 'company.context'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('services', ServiceController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('availability-rules', AvailabilityRuleController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::resource('clients', ClientController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/appointments/slots', [AppointmentController::class, 'slots'])->name('appointments.slots');
    Route::resource('appointments', AppointmentController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
