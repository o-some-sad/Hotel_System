<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStuffController;
use App\Http\Controllers\EmailVerificationPromptController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\EmailVerificationNotificationController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\StripeCheckoutController;

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
    /*Route::get('/admin/rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
    Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.rooms.store');
    Route::patch('/admin/rooms/{room}', [RoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/admin/rooms/{room}', [RoomController::class, 'destroy'])->name('admin.rooms.destroy');*/
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
    /*Route::get('/manager/rooms', [RoomController::class, 'index'])->name('manager.rooms.index');
    Route::post('/manager/rooms', [RoomController::class, 'store'])->name('manager.rooms.store');
    Route::patch('/manager/rooms/{room}', [RoomController::class, 'update'])->name('manager.rooms.update');
    Route::delete('/manager/rooms/{room}', [RoomController::class, 'destroy'])->name('manager.rooms.destroy');*/
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

Route::middleware(['role:manager'])->group(function () {
    Route::resource('/managers', ManagerController::class);
    Route::resource('/receptionists', ReceptionistController::class);
});

Route::middleware(['role:receptionist'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::post('/clients/{client}/update', [ClientController::class, 'update'])->name('clients.update');
    Route::get('/clients/{client}/delete', [ClientController::class, 'delete'])->name('clients.delete');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{client}/approve', [ClientController::class, 'approve'])->name('clients.approve');
});

Route::get('/clients/search', [ClientController::class, 'search']);

//Reservation routes for stuff only
Route::middleware(['role:receptionist'])->prefix('stuff')->name('stuff.')->group(function () {
    Route::get('/reservations', [ReservationStuffController::class, 'index'])->name('reservation.index');
    Route::get('/reservations/create', [ReservationStuffController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationStuffController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/edit', [ReservationStuffController::class, 'edit'])->name('reservations.edit');
    Route::patch('/reservations/{reservation}', [ReservationStuffController::class, 'update'])->name('reservations.update');
    Route::get('/reservations/{reservation}/delete', [ReservationStuffController::class, 'delete'])->name('reservation.delete');
    Route::delete('/reservations/{reservation}', [ReservationStuffController::class, 'destroy'])->name('reservation.destroy');
    Route::patch('/reservations/{reservation}/approve', [ReservationStuffController::class, 'approveReservation'])->name('reservation.approve');
});






// reservations routes for the logged-in client
Route::prefix('client')->name('client.')->middleware(['auth:client'])->middleware(['role:client'])->group(function () {
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




// Update these routes
Route::post('/stripe/checkout', [StripeCheckoutController::class, 'checkout'])->name('stripe.checkout');
Route::get('/stripe/checkout', [StripeCheckoutController::class, 'checkout'])->name('stripe.checkout.get');
Route::get('/stripe/success', [StripeCheckoutController::class, 'success'])->name('stripe.success');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
