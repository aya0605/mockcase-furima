<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function editShippingAddress(Request $request)
    {
        $user = Auth::user();

        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first() ?? new Address(['user_id' => $user->id]);

        if ($request->has('item_id')) {
            session(['redirect_to_item_purchase' => $request->item_id]);
        }

        return view('user.edit', compact('user', 'address'));
    }

    public function updateShippingAddress(AddressRequest $request) 
    {
        $user = Auth::user();

         $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if ($address) {
            $address->update($request->validated());
        } else {
            $address = $user->addresses()->create(array_merge(
                $request->validated(),
                ['is_default' => true] 
            ));
        }

        if (session()->has('redirect_to_item_purchase')) {
            $itemId = session('redirect_to_item_purchase');
            session()->forget('redirect_to_item_purchase'); 

            return redirect('/items/' . $itemId . '/purchase')->with('success', '配送先住所を更新しました。');
        }

        return redirect('/user/shipping-address/edit')->with('success', '配送先住所を更新しました。');

    }

    public function editProfile() // ★このメソッドが追加されているか確認★
    {
        $user = Auth::user();
        // ユーザーのデフォルト住所、なければ最新の住所、それでもなければ新しいAddressインスタンス
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first() ?? new Address(['user_id' => $user->id]);

        return view('user.profile.edit', compact('user', 'address'));
    }

    public function updateProfile(ProfileUpdateRequest $request) // ★このメソッドも追加されているか確認★
    {
        $user = Auth::user();

        // 1. ユーザー名の更新
        $user->name = $request->input('name');

        // 2. プロフィール画像の更新
        if ($request->hasFile('profile_image')) {
            // 古い画像があれば削除
            if ($user->profile_image_path) {
                Storage::delete($user->profile_image_path);
            }
            // 新しい画像を保存し、パスをDBに保存
            $path = $request->file('profile_image')->store('public/profile_images');
            $user->profile_image_path = $path;
        }

        $user->save(); // Userモデルを保存

        // 3. 住所情報の更新（Addressモデルを操作）
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if ($address) {
            // 既存の住所を更新
            $address->update($request->only(['postal_code', 'address', 'building_name']));
        } else {
            // 新しい住所を作成し、デフォルトに設定
            $user->addresses()->create(array_merge(
                $request->only(['postal_code', 'address', 'building_name']),
                ['is_default' => true]
            ));
        }

        return redirect('/user/profile/edit')->with('success', 'プロフィールを更新しました。');
    }
}
