<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;


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

Route::get('/items/{item}', [ItemController::class, 'show']);

Route::post('/comments/store/{item}', [ItemController::class, 'storeComment'])
    ->middleware('auth');

Route::post('/items/{item}/like', [ItemController::class,'toggleLike'])
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/sell', [SellController::class, 'create']);
    Route::post('/sell', [SellController::class, 'store']); 
});