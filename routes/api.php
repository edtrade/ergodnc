<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\OfficeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tags...
Route::get('/tags', TagController::class);

// Offices...
Route::get('/offices', [OfficeController::class, 'index'])->name('office.index');
Route::get('/offices/{office}', [OfficeController::class, 'show'])->name('office.show');
Route::post('/offices', [OfficeController::class, 'create'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.create');
Route::put('/offices/{office}', [OfficeController::class, 'update'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.update');    
Route::delete('/offices/{office}', [OfficeController::class, 'delete'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.delete');        
    