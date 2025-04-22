<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BanController;

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

    // Admin Floor Routes
    Route::get('/admin/floors', [FloorController::class, 'index'])->name('admin.floors.index');
    Route::post('/admin/floors', [FloorController::class, 'store'])->name('admin.floors.store');
    Route::patch('/admin/floors/{floor}', [FloorController::class, 'update'])->name('admin.floors.update');
    Route::delete('/admin/floors/{floor}', [FloorController::class, 'destroy'])->name('admin.floors.destroy');
    Route::get('/admin/managers', [FloorController::class, 'getManagers'])->name('admin.managers.index');
    
    // Admin Room Routes
    Route::get('/admin/rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::patch('/admin/rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/admin/rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // Admin Ban Routes
    Route::get('/admin/bans', [BanController::class, 'index'])->name('admin.bans.index');
    Route::get('/admin/bans/create', [BanController::class, 'create'])->name('admin.bans.create');
    Route::post('/admin/bans', [BanController::class, 'store'])->name('admin.bans.store');
    Route::delete('/admin/bans/{ban}', [BanController::class, 'revoke'])->name('admin.bans.revoke');
});

Route::middleware(['auth:manager'])->group(function () {
    Route::get('/manager/dashboard', function () {
        return Inertia::render('Manager/Dashboard', [
            'auth' => [
                'user' => auth()->guard('manager')->user()
            ]
        ]);
    })->name('manager.dashboard');

    // Manager Floor Routes
    Route::get('/manager/floors', [FloorController::class, 'index'])->name('manager.floors.index');
    Route::post('/manager/floors', [FloorController::class, 'store'])->name('manager.floors.store');
    Route::patch('/manager/floors/{floor}', [FloorController::class, 'update'])->name('manager.floors.update');
    Route::delete('/manager/floors/{floor}', [FloorController::class, 'destroy'])->name('manager.floors.destroy');
    
    // Manager Room Routes
    Route::get('/manager/rooms', [RoomController::class, 'index'])->name('manager.rooms.index');
    Route::post('/manager/rooms', [RoomController::class, 'store'])->name('manager.rooms.store');
    Route::patch('/manager/rooms/{room}', [RoomController::class, 'update'])->name('manager.rooms.update');
    Route::delete('/manager/rooms/{room}', [RoomController::class, 'destroy'])->name('manager.rooms.destroy');

    // Manager Ban Routes
    Route::get('/manager/bans', [BanController::class, 'index'])->name('bans.index');
    Route::get('/manager/bans/create', [BanController::class, 'create'])->name('bans.create');
    Route::post('/manager/bans', [BanController::class, 'store'])->name('bans.store');
    Route::delete('/manager/bans/{ban}', [BanController::class, 'revoke'])->name('bans.revoke');
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

    Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['role:receptionist'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('/clients/{client}/update', [ClientController::class, 'update'])->name('clients.update');
    Route::get('/clients/{client}/delete', [ClientController::class, 'delete'])->name('clients.delete');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
});


//Reservation routes
Route::get('/reservations/stuff', [ReservationController::class, 'index'])->name('reservation.index');
Route::get('/reservation/{client}/delete', [ReservationController::class, 'delete'])->name('reservation.delete');



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
    Route::patch('/reservations/{reservationId}', [ReservationController::class, 'update'])->name('reservations.update');
    // Show a specific reservation
    Route::get('/reservations/{reservation}', [ReservationController::class, 'clientReservation'])->name('reservations.show');
    // Delete a reservation
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'deleteLoggedInReservation'])->name('reservations.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

// Admin routes
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Other admin routes
    
    // Ban management for admins
    Route::get('/bans', [BanController::class, 'index'])->name('bans.index');
    Route::get('/bans/create', [BanController::class, 'create'])->name('bans.create');
    Route::post('/bans', [BanController::class, 'store'])->name('bans.store');
    Route::delete('/bans/{ban}', [BanController::class, 'revoke'])->name('bans.revoke');
});

// Manager routes
Route::middleware(['auth:manager'])->prefix('manager')->name('manager.')->group(function () {
    // Other manager routes
    
    // Ban management for managers
    Route::get('/bans', [BanController::class, 'index'])->name('bans.index');
    Route::get('/bans/create', [BanController::class, 'create'])->name('bans.create');
    Route::post('/bans', [BanController::class, 'store'])->name('bans.store');
    Route::delete('/bans/{ban}', [BanController::class, 'revoke'])->name('bans.revoke');
    
    // Routes for fetching users
    Route::get('/clients', [BanController::class, 'getClients'])->name('clients.index');
    Route::get('/receptionists', [BanController::class, 'getReceptionists'])->name('receptionists.index');
});

// Add routes for user selection in ban creation
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/managers', [BanController::class, 'getManagers'])->name('managers.index');
    Route::get('/clients', [BanController::class, 'getClients'])->name('clients.index');
    Route::get('/receptionists', [BanController::class, 'getReceptionists'])->name('receptionists.index');
});