<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use App\Models\User;
use App\Models\Order; 
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfileController extends Controller
{
    /**
     * 配送先住所の編集画面表示
     */
    public function editShippingAddress(Request $request)
    {
        $user = Auth::user();

        // デフォルト住所 → 最新の住所 → 新規インスタンス の順で取得を試みる
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first() ?? new Address(['user_id' => $user->id]);

        // 購入画面から「変更」で飛んできた場合、戻り先のアイテムIDをセッションに保存
        if ($request->has('item_id')) {
            session(['redirect_to_item_purchase' => $request->item_id]);
        }

        return view('user.edit', compact('user', 'address'));
    }

    /**
     * 配送先住所の更新処理
     */
    public function updateShippingAddress(AddressRequest $request) 
    {
        $user = Auth::user();

        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if ($address) {
            // 既存の住所があれば更新
            $address->update($request->validated());
        } else {
            // なければ新規作成（初回登録時はデフォルト設定にする）
            $address = $user->addresses()->create(array_merge(
                $request->validated(),
                ['is_default' => true] 
            ));
        }

        // セッションに購入戻り先があれば、購入画面へリダイレクト
        if (session()->has('redirect_to_item_purchase')) {
            $itemId = session('redirect_to_item_purchase');
            session()->forget('redirect_to_item_purchase'); 

            return redirect('/items/' . $itemId . '/purchase')->with('success', '配送先住所を更新しました。');
        }

        return redirect('/user/shipping-address/edit')->with('success', '配送先住所を更新しました。');
    }

    /**
     * プロフィール編集画面の表示
     */
    public function editProfile() 
    {
        $user = Auth::user();
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first() ?? new Address(['user_id' => $user->id]);

        return view('user.profile_edit', compact('user', 'address'));
    }

    /**
     * プロフィール情報の更新（名前・画像・住所）
     */
    public function updateProfile(ProfileUpdateRequest $request) 
    {
        $user = Auth::user();

        // 1. 名前の更新
        $user->name = $request->input('name');

        // 2. プロフィール画像のアップロード処理
        if ($request->hasFile('profile_image')) {
            // 古い画像があればストレージから削除して整理
            if ($user->profile_image_path) {
                Storage::delete($user->profile_image_path);
            }
            // 新しい画像を保存し、そのパスをDBに記録
            $path = $request->file('profile_image')->store('public/profile_images');
            $user->profile_image_path = $path;
        }

        $user->save(); 

        // 3. 住所情報の更新（名前変更と同時に住所も直すパターンに対応）
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if ($address) {
            $address->update($request->only(['postal_code', 'address', 'building_name']));
        } else {
            $user->addresses()->create(array_merge(
                $request->only(['postal_code', 'address', 'building_name']),
                ['is_default' => true]
            ));
        }

        return redirect('/user/profile')->with('success', 'プロフィールを更新しました。');
    }

    /**
     * ユーザープロフィール画面（出品・購入履歴）の表示
     */
    public function showProfile(Request $request)
    {
        $user = Auth::user();

        // 現在のタブ（出品：sell / 購入：buy）を取得
        $page = $request->input('page', 'sell'); 
        $perPage = 9; 

        // 初期値として空のページネーターを作成
        $soldItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        $purchasedItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        $tradingItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);

        if ($page === 'sell') {
            // 出品した商品の取得（売却状況も一緒に読み込む）
            $soldItems = $user->items()
                              ->with('purchase') 
                              ->paginate($perPage, ['*'], 'Page')
                              ->withQueryString();

        } elseif ($page === 'buy') { 
            // 購入した商品の取得
            $purchases = $user->purchases()
                           ->with('item') 
                           ->latest()
                           ->paginate($perPage, ['*'], 'Page')
                           ->withQueryString();

            // 注文データから商品(Item)だけのコレクションを抽出
            $itemsCollection = $purchases->getCollection()->map(function ($purchase) {
                return $purchase->item;
            })->filter();

            $purchasedItems = new LengthAwarePaginator(
                $itemsCollection,
                $purchases->total(),
                $purchases->perPage(),
                $purchases->currentPage(),
                [
                    'path' => $purchases->path(),
                    'pageName' => 'Page'
                ]);

        } elseif ($page === 'trading') {
            $purchasedItemIds = $user->purchases()->pluck('item_id');

            $tradingItems = Item::where(function($query) use ($user, $purchasedItemIds) {
                $query->where('seller_id', $user->id)
                ->whereHas('purchase');
            })
            ->orWhereIn('id', $purchasedItemIds)
            ->latest()
            ->paginate($perPage, ['*'], 'page')
            ->withQueryString();
        }

        return view('user.profile', [
            'user' => $user, 
            'page' => $page, 
            'soldItems' => $soldItems,
            'purchasedItems' => $purchasedItems,
            'tradingItems' => $tradingItems,
        ]);
    }
}