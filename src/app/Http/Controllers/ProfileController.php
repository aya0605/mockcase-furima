<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use App\Models\User;
use App\Models\Purchase;
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

    public function editProfile() 
    {
        $user = Auth::user();
        $address = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first() ?? new Address(['user_id' => $user->id]);

        return view('user.profile_edit', compact('user', 'address'));
    }

    public function updateProfile(ProfileUpdateRequest $request) 
    {
        $user = Auth::user();

        $user->name = $request->input('name');

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image_path) {
                Storage::delete($user->profile_image_path);
            }
            $path = $request->file('profile_image')->store('public/profile_images');
            $user->profile_image_path = $path;
        }

        $user->save(); 

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

    public function showProfile()
    {
        $user = Auth::user();

        $soldItems = $user->items()->paginate(9, ['*'], 'soldPage');

        $purchasedItems = $user->purchases()->with('item')->paginate(9, ['*'], 'purchasedPage');


        return view('user.profile', compact('user', 'soldItems', 'purchasedItems')); 
    }
}
