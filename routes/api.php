<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OfficeImageController;
use App\Http\Controllers\UserReservationController;
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
Route::get('/offices', [OfficeController::class, 'index'])
    ->name('office.index');

Route::get('/offices/{office}', [OfficeController::class, 'show'])
    ->name('office.show');

Route::post('/offices', [OfficeController::class, 'create'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.create');

Route::put('/offices/{office}', [OfficeController::class, 'update'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.update');    

Route::delete('/offices/{office}', [OfficeController::class, 'delete'])
    ->middleware(['auth:sanctum','verified'])
    ->name('office.delete');   


//Office Images
Route::post('/offices/{office}/images', [OfficeImageController::class, 'store']) 
    ->middleware(['auth:sanctum','verified'])
    ->name('office.images.store');     

Route::delete('/offices/{office}/images/{image:id}', [OfficeImageController::class, 'delete']) 
    ->middleware(['auth:sanctum','verified'])
    ->name('office.images.delete');           

//User Reservations
Route::get('/reservations',[UserReservationController::class,'index'])
    ->middleware(['auth:sanctum','verified'])
    ->name('reservations.user.index');   

Route::post('/reservations',[UserReservationController::class,'create'])
    ->middleware(['auth:sanctum','verified'])
    ->name('reservations.user.create');    

Route::delete('/reservations/{reservation}',[UserReservationController::class,'cancel'])
    ->middleware(['auth:sanctum','verified'])
    ->name('reservations.user.cancel');           

//Host Reservations
Route::get('/host/reservations',[HostReservationController::class,'index'])
    ->middleware(['auth:sanctum','verified'])
    ->name('reservations.host.index');       