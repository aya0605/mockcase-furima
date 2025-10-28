<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Address; 
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Log; 

class PurchaseController extends Controller
{
    public function showPurchaseForm(Item $item)
    {
        $user = Auth::user();
        $shippingAddress = null;

        if ($user) {
            $shippingAddress = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();
        }

        // 既に購入済みの場合はリダイレクトする安全策を追加
        if ($item->sold()) {
             return redirect()->to("/items/{$item->id}")->with('error', 'この商品は既に購入されています。');
        }
        
        // 支払い方法のダミーリストをViewに渡す（View側で必要）
        $paymentMethods = [
            'credit_card' => 'カード支払い', 
            'convenience_store' => 'コンビニ払い',
        ];

        return view('items.purchase', compact('item', 'shippingAddress', 'paymentMethods'));
    
    }
    
    public function processPurchase(PurchaseRequest $request, Item $item) 
    {
        $user = Auth::user();

        // 購入チェック
        if ($item->sold()) {
             return response()->json([
                'success' => false,
                'message' => 'この商品は既に購入されています。'
            ], 409);
        }

        $paymentMethod = $request->input('payment_method'); 
        $basePrice = $item->price;
        $paymentFee = 0;

        // 支払い方法による手数料計算
        if ($paymentMethod === 'convenience_store') { 
            $paymentFee = 150; 
        }

        $totalAmount = $basePrice + $paymentFee; 
        
        $shippingAddressObject = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        if (!$shippingAddressObject) {
            return response()->json([
                'success' => false,
                'message' => '配送先情報が登録されていません。'
            ], 400); // 400 Bad Request
        }
        
        if (empty($shippingAddressObject->postal_code) || empty($shippingAddressObject->address)) {
            return response()->json([
                'success' => false,
                'message' => '配送先情報が不完全です。ご購入前のご確認をお願いいたします。'
            ], 400); // 400 Bad Request
        }

        DB::beginTransaction();

        try {
            
            $dataToCreate = [
                'buyer_id' => $user->id,
                'item_id' => $item->id,
                'total_amount' => $totalAmount, 
                'order_date' => now(),

                // shipping_postal_codeからハイフンを除去
                'shipping_postal_code' => str_replace('-', '', $shippingAddressObject->postal_code), 
                // Addressモデルにないカラムには空文字列を挿入してDBのNOT NULL制約に対応
                'shipping_prefecture' => $shippingAddressObject->prefecture ?? '', 
                'shipping_city' => $shippingAddressObject->city ?? '',       
                'shipping_street_address' => $shippingAddressObject->address,
                'shipping_building_name' => $shippingAddressObject->building_name ?? '', 

                'payment_method' => $paymentMethod, 
                'status' => 'pending', 
            ];
            
            Order::create($dataToCreate);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '購入が完了しました！',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Purchase Transaction FAILED. Exception: ' . $e->getMessage()); 
            
            return response()->json([
                'success' => false,
                'message' => '購入処理中にエラーが発生しました。' . $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

}
