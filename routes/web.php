<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WearController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

// Routes Inertia (page web)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'home')->name('home');
    Route::view('/profile', 'profile.edit')->name('profile.edit');

    // Items
    Route::view('/items', 'items')->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');

    // Outfits
    Route::view('/outfits', 'outfits')->name('outfits.index');
    Route::get('/outfits/create', [OutfitController::class, 'create'])->name('outfits.create');
    Route::get('/outfits/{outfit}', [OutfitController::class, 'show'])->name('outfits.show');

    // Wears
    Route::view('/wears', 'wears')->name('wears.index');
    Route::get('/wears/create', [WearController::class, 'create'])->name('wears.create');
    Route::get('/wears/{wear}', [WearController::class, 'show'])->name('wears.show');

    // Profil (update + delete)
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Alias pour les contrôleurs Breeze qui redirigent vers 'dashboard'
Route::get('/dashboard', fn() => redirect('/'))->middleware(['auth', 'verified'])->name('dashboard');

// API routes (REST, JSON)
Route::middleware('auth:sanctum')->group(function () {
    // Items
    Route::get('/api/items', [ItemController::class, 'index']);
    Route::post('/api/items', [ItemController::class, 'store']);
    Route::get('/api/items/{item}', [ItemController::class, 'show']);
    Route::put('/api/items/{item}', [ItemController::class, 'update']);
    Route::delete('/api/items/{item}', [ItemController::class, 'destroy']);

    // Outfits
    Route::get('/api/outfits', [OutfitController::class, 'index']);
    Route::post('/api/outfits', [OutfitController::class, 'store']);
    Route::get('/api/outfits/{outfit}', [OutfitController::class, 'show']);
    Route::put('/api/outfits/{outfit}', [OutfitController::class, 'update']);
    Route::delete('/api/outfits/{outfit}', [OutfitController::class, 'destroy']);

    // Wears
    Route::get('/api/wears', [WearController::class, 'index']);
    Route::post('/api/wears', [WearController::class, 'store']);
    Route::get('/api/wears/{wear}', [WearController::class, 'show']);
    Route::delete('/api/wears/{wear}', [WearController::class, 'destroy']);
});
