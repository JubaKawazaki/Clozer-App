<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\WearController;
use Illuminate\Support\Facades\Route;

// API routes (REST, JSON)
Route::middleware('auth:sanctum')->group(function () {
    // Items
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::get('/items/{item}', [ItemController::class, 'show']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);

    // Outfits
    Route::get('/outfits', [OutfitController::class, 'index']);
    Route::post('/outfits', [OutfitController::class, 'store']);
    Route::get('/outfits/{outfit}', [OutfitController::class, 'show']);
    Route::put('/outfits/{outfit}', [OutfitController::class, 'update']);
    Route::delete('/outfits/{outfit}', [OutfitController::class, 'destroy']);

    // Wears
    Route::get('/wears', [WearController::class, 'index']);
    Route::post('/wears', [WearController::class, 'store']);
    Route::get('/wears/{wear}', [WearController::class, 'show']);
    Route::delete('/wears/{wear}', [WearController::class, 'destroy']);
});
