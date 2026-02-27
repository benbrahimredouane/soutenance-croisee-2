<?php

use App\Http\Controllers\ColocationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::resource('colocation', ColocationController::class);


Route::middleware(['auth'])->group(function () {
    Route::get('/colocations/create', [ColocationController::class, 'create'])->name('colocations.create');
    Route::post('/colocations', [ColocationController::class, 'store'])->name('colocations.store');
    Route::get('/colocations/{colocation}', [ColocationController::class, 'show'])->name('colocations.show');
    Route::patch('/colocations/{colocation}/cancel', [ColocationController::class, 'cancel'])->name('colocations.cancel');


    Route::get('/colocations/{colocation}/invite', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/colocations/{colocation}/invite', [InvitationController::class, 'store'])->name('invitations.store');

    Route::get('/join', [InvitationController::class, 'joinForm'])->name('invitations.joinForm');
    Route::post('/join', [InvitationController::class, 'join'])->name('invitations.join');

    Route::post('/colocations/{colocation}/expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store');

    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy');



    Route::patch('/settlements/{settlement}/paid', [SettlementController::class, 'markPaid'])
        ->name('settlements.paid');
});
