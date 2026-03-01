<?php

use App\Http\Controllers\ColocationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;

Route::get('/', function () {
    return view('welcome');
});


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

    Route::patch(
        '/colocations/{colocation}/leave',
        [ColocationController::class, 'leave']
    )->name('colocations.leave');
    Route::patch(
        '/colocations/{colocation}/members/{user}/remove',
        [ColocationController::class, 'removeMember']
    )->name('colocations.members.remove');

    

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::patch('/admin/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('admin.users.toggleBan');
});



Route::middleware(['auth'])->group(function () {
    Route::post('/colocations/{colocation}/categories', [CategoryController::class, 'store'])
        ->name('categories.store');

    Route::delete('/colocations/{colocation}/categories/{category}', [CategoryController::class, 'destroy'])
        ->name('categories.destroy');
});

});

