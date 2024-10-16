<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Authentication
Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
        Route::post('logout', 'logout')->name('auth.logout')->middleware('auth:api'); //This middleware ensures that the user is authenticated via a JWT token
    });
//----------------------------------------------------------------------------------
//Admin
Route::group(['middleware' => ['auth:api', 'admin']], function () {
    Route::resource('programs', ProgramController::class)->except(['index', 'show']);
    Route::get('/tours/report', [TourController::class, 'reportToursByDriver']);
    Route::resource('tours', TourController::class)->except(['index', 'show']);
    Route::resource('users', UserController::class);
});
//----------------------------------------------------------------------------------
Route::middleware('auth:api')->group(function () {
    //Programs
    Route::get('/programs/{id}', [ProgramController::class, 'show']); // Show a specific program by ID
    Route::get('/programs', [ProgramController::class, 'index']); // Show all programs
    //Tours
    Route::get('/tours/search', [TourController::class, 'searchForTour']); // Search for a tour
    Route::get('/tours/{id}', [TourController::class, 'show']); // Show a specific tour by ID
    Route::get('/tours', [TourController::class, 'index']); // Show all available tours
    Route::post('/tours/register/{id}', [TourController::class, 'registerInTour']); // Register in a tour
});
