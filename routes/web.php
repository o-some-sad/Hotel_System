<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MangeClientController;

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

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
