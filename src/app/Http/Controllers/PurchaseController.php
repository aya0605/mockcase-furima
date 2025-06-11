<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Address; 
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function showPurchaseForm(Item $item)
    {
        $user = Auth::user();
        $shippingAddress = null;

        if ($user) {
            $shippingAddress = $user->defaultShippingAddress() ?? $user->address()->latest()->first();
        }
        
        return view('items.purchase', compact('item', 'shippingAddress'));
    
    }
    public function processPurchase(PurchaseRequest $request, Item $item) 
    {
        $user = Auth::user();

        $paymentMethod = $request->input('payment_method'); 
        $basePrice = $item->price;
        $paymentFee = 0;

        if ($paymentMethod === 'convenience_store') { 
            $paymentFee = 150; 
        }

        $totalAmount = $basePrice + $paymentFee; 
        
        $shippingAddressObject = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if (!$shippingAddressObject) {
            return redirect()->back()->with('error', '配送先情報が登録されていません。');
        }

        // ★Addressモデルから住所情報を取得して連結★
        $shippingAddressText = '';
        if ($shippingAddressObject->postal_code) {
            $shippingAddressText .= '〒' . $shippingAddressObject->postal_code . ' ';
        }
        if ($shippingAddressObject->address) {
            $shippingAddressText .= $shippingAddressObject->address;
        }
        if ($shippingAddressObject->building_name) {
            $shippingAddressText .= ' ' . $shippingAddressObject->building_name;
        }

        Order::create([
            'buyer_id' => $user->id,
            'item_id' => $item->id,
            'total_amount' => $totalAmount, 
            'order_date' => now(),
            'shipping_address' => $shippingAddressText,
            'payment_method' => $paymentMethod, 
            'status' => 'pending', 
        ]);

        // 購入完了後、グローバルな購入完了ページにリダイレクト
        return redirect('/purchase/complete')->with('success', '購入手続きが完了しました！'); 
    }

    public function showCompletion(Item $item)
    {
        return view('purchase.complete');
    }
}
