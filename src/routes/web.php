<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ItemController::class, 'index']); 

Route::get('/register', [AuthController::class, 'showRegistrationForm']); 
Route::post('/register', [AuthController::class, 'register']); 
Route::get('/login', [AuthController::class, 'showLoginForm']); 
Route::post('/login', [AuthController::class, 'login']); 
Route::post('/logout', [AuthController::class, 'logout']); 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'index']);
    Route::get('/sell', [SellController::class, 'create']); 
});