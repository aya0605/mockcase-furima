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

    public function showProfile(Request $request)
    {
        $user = Auth::user();

        $page = $request->input('page', 'sell'); 
        $perPage = 9;

        $purchasedItemIds = $user->purchases()->pluck('item_id');

        $allTradingItems = Item::where(function($query) use ($user, $purchasedItemIds) {
                $query->where('seller_id', $user->id)->whereHas('purchase');
            })
            ->orWhereIn('id', $purchasedItemIds)
            ->get();

        $soldItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        $purchasedItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        $tradingItems = new LengthAwarePaginator(Collection::make([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);

        if ($page === 'sell') {
            $soldItems = $user->items()
                              ->with('purchase') 
                              ->paginate($perPage, ['*'], 'Page')
                              ->withQueryString();

        } elseif ($page === 'buy') { 
            $purchases = $user->purchases()
                           ->with('item') 
                           ->latest()
                           ->paginate($perPage, ['*'], 'Page')
                           ->withQueryString();

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
            'allTradingItems' => $allTradingItems,
        ]);
    }
}