<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;


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

// トップページ
Route::get('/', [ItemController::class, 'index']);

// 商品詳細
Route::get('/items/{item}', [ItemController::class, 'show']);

// 認証済みユーザーのみアクセス可能なルートグループ
Route::middleware('auth')->group(function () {
    // コメント
    Route::post('/comments/store/{item}', [ItemController::class, 'storeComment']);
    // いいね
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike']);
    // 出品
    Route::get('/sell', [SellController::class, 'create']);
    Route::post('/sell', [SellController::class, 'store']);

    // 購入フォームの表示 
    Route::get('/items/{item}/purchase', [PurchaseController::class, 'showPurchaseForm']);

    // 購入処理実行 
    Route::post('/items/{item}/purchase', [PurchaseController::class, 'processPurchase']);
    
    // 購入完了画面 (
    Route::get('/purchase/complete', [PurchaseController::class, 'showCompletion']);

    // 配送先住所編集画面
    Route::get('/user/shipping-address/edit', [ProfileController::class, 'editShippingAddress']);
    // ユーザープロフィール情報（
    Route::post('/user/shipping-address/update', [ProfileController::class, 'updateShippingAddress']);

     // プロフィール画面
    Route::get('/user/profile', [ProfileController::class, 'showProfile']);

    Route::get('/user/profile/edit', [ProfileController::class, 'editProfile']);
    Route::post('/user/profile/update', [ProfileController::class, 'updateProfile']);

});