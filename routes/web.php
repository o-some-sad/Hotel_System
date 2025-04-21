<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'auth' => [
                'user' => auth()->guard('admin')->user()
            ]
        ]);
    })->name('admin.dashboard');
});

Route::middleware(['auth:manager'])->group(function () {
    Route::get('/manager/dashboard', function () {
        return Inertia::render('Manager/Dashboard', [
            'auth' => [
                'user' => auth()->guard('manager')->user()
            ]
        ]);
    })->name('manager.dashboard');
});

Route::middleware(['auth:receptionist'])->group(function () {
    Route::get('/receptionist/dashboard', function () {
        return Inertia::render('Receptionist/Dashboard', [
            'auth' => [
                'user' => auth()->guard('receptionist')->user()
            ]
        ]);
    })->name('receptionist.dashboard');
});

Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/dashboard', function () {
        return Inertia::render('Client/Dashboard', [
            'auth' => [
                'user' => auth()->guard('client')->user()
            ]
        ]);
    })->name('client.dashboard');
});

Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');;
Route::get('/clients/create', [ClientController::class, 'create']);
Route::post('clients/store', [ClientController::class, 'store']);
Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
Route::patch('/clients/{client}/update', [ClientController::class, 'update'])->name('clients.update');
Route::get('/clients/{client}/delete', [ClientController::class, 'delete'])->name('clients.delete');
Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');




// reservations routes for the logged-in client
Route::prefix('client')->name('client.')->group(function () {
    // Show all reservations for the logged-in client
    Route::get('/reservations', [ReservationController::class, 'loggedInReservations'])->name('reservations');
    // Show form to create a new reservation
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    // Store a new reservation
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    // Show form to edit a reservation
    Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    // Update a reservation
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    // Show a specific reservation
    Route::get('/reservations/{reservation}', [ReservationController::class, 'clientReservation'])->name('reservations.show');
    // Delete a reservation
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'deleteLoggedInReservation'])->name('reservations.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
