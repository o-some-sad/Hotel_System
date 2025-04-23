<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationStaffController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BanController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\StripeCheckoutController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Admin routes
Route::middleware(['auth:admin'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'auth' => [
                'user' => auth()->guard('admin')->user()
            ]
        ]);
    })->name('admin.dashboard');

    // Admin Floor Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
        Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
        Route::patch('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
        Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

        // Admin Room Routes
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::patch('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Admin Ban Routes
        Route::get('/bans', [BanController::class, 'index'])->name('bans.index');
        Route::get('/bans/create', [BanController::class, 'create'])->name('bans.create');
        Route::post('/bans', [BanController::class, 'store'])->name('bans.store');
        Route::delete('/bans/{ban}', [BanController::class, 'revoke'])->name('bans.revoke');

        // User Selection Routes for Admin
        Route::get('/managers', [BanController::class, 'getManagers'])->name('managers.index');
        Route::get('/clients', [BanController::class, 'getClients'])->name('clients.index');
        Route::get('/receptionists', [BanController::class, 'getReceptionists'])->name('receptionists.index');
    });
});

// Ban notice page
Route::get('/banned', function () {
    if (!session('ban_message')) {
        return redirect('/login');
    }

    return Inertia::render('auth/Banned', [
        'banMessage' => session('ban_message'),
        'redirectAfter' => session('redirect_after', 10),
        'redirectTo' => session('redirect_to', '/login')
    ]);
})->name('banned');

// Manager routes
Route::middleware(['auth:manager', 'ban.check'])->group(function () {
    // Manager Dashboard
    Route::get('/manager/dashboard', function () {
        return Inertia::render('Manager/Dashboard', [
            'auth' => [
                'user' => auth()->guard('manager')->user()
            ]
        ]);
    })->name('manager.dashboard');

    // Manager Routes
    Route::prefix('manager')->name('manager.')->group(function () {
        // Manager Floor Routes
        Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
        Route::post('/floors', [FloorController::class, 'store'])->name('floors.store');
        Route::patch('/floors/{floor}', [FloorController::class, 'update'])->name('floors.update');
        Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('floors.destroy');

        // Manager Room Routes
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::patch('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // Manager Ban Routes - CORRECTED ROUTE NAMES HERE
        Route::get('/bans', [BanController::class, 'index'])->name('bans.index');
        Route::get('/bans/create', [BanController::class, 'create'])->name('bans.create');
        Route::post('/bans', [BanController::class, 'store'])->name('bans.store');
        Route::delete('/bans/{ban}', [BanController::class, 'revoke'])->name('bans.revoke');

        // User Selection Routes for Manager
        Route::get('/clients', [BanController::class, 'getClients'])->name('clients.index');
        Route::get('/receptionists', [BanController::class, 'getReceptionists'])->name('receptionists.index');
    });
});

// Receptionist routes
Route::middleware(['auth:receptionist', 'ban.check'])->group(function () {
    Route::get('/receptionist/dashboard', function () {
        return Inertia::render('Receptionist/Dashboard', [
            'auth' => [
                'user' => auth()->guard('receptionist')->user()
            ]
        ]);
    })->name('receptionist.dashboard');
});

// Client routes
Route::middleware(['auth:client', 'ban.check'])->group(function () {
    Route::get('/reservations', [ReservationStaffController::class, 'index'])->name('client.reservation.index');


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

//Reservation routes for staff only
Route::middleware(['role:receptionist'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/reservations', [ReservationStaffController::class, 'index'])->name('reservation.index');
    Route::get('/reservations/create', [ReservationStaffController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationStaffController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/edit', [ReservationStaffController::class, 'edit'])->name('reservations.edit');
    Route::patch('/reservations/{reservation}', [ReservationStaffController::class, 'update'])->name('reservations.update');
    Route::get('/reservations/{reservation}/delete', [ReservationStaffController::class, 'delete'])->name('reservation.delete');
    Route::delete('/reservations/{reservation}', [ReservationStaffController::class, 'destroy'])->name('reservation.destroy');
    Route::patch('/reservations/{reservation}/approve', [ReservationStaffController::class, 'approveReservation'])->name('reservation.approve');
});

Route::middleware(['auth:admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/reservations', [ReservationStaffController::class, 'index'])->name('reservation.index');
    Route::get('/reservations/create', [ReservationStaffController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationStaffController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/edit', [ReservationStaffController::class, 'edit'])->name('reservations.edit');
    Route::patch('/reservations/{reservation}', [ReservationStaffController::class, 'update'])->name('reservations.update');
    Route::get('/reservations/{reservation}/delete', [ReservationStaffController::class, 'delete'])->name('reservation.delete');
    Route::delete('/reservations/{reservation}', [ReservationStaffController::class, 'destroy'])->name('reservation.destroy');
    Route::patch('/reservations/{reservation}/approve', [ReservationStaffController::class, 'approveReservation'])->name('reservation.approve');
});

// Client reservation routes
Route::prefix('client')->name('client.')->middleware(['auth:client', 'role:client'])->group(function () {
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

// Stripe routes
Route::post('/stripe/checkout', [StripeCheckoutController::class, 'checkout'])->name('stripe.checkout');
Route::get('/stripe/checkout', [StripeCheckoutController::class, 'checkout'])->name('stripe.checkout.get');
Route::get('/stripe/success', [StripeCheckoutController::class, 'success'])->name('stripe.success');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
