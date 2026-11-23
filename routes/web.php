<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BoardController;
use App\Http\Controllers\Admin\BoardDesignController;
use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicBoardController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', [PublicBoardController::class, 'index'])->name('home');
Route::get('board/{board:slug}', [PublicBoardController::class, 'show'])->name('boards.show');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'can:dashboard.view'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('boards/{board}/design', [BoardDesignController::class, 'edit'])->name('boards.design');
    Route::put('boards/{board}/design', [BoardDesignController::class, 'update'])->name('boards.design.update');
    Route::post('uploads/image', [UploadController::class, 'image'])->name('uploads.image');
    Route::put('boards/order', [BoardController::class, 'reorder'])->name('boards.reorder');
    Route::resource('boards', BoardController::class)->except('show');
    Route::resource('cards', CardController::class)->except('show');
    Route::resource('links', LinkController::class)->except('show');
    Route::resource('applications', ApplicationController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
});

require __DIR__.'/auth.php';
