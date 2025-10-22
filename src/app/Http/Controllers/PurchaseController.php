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
use Log; // Logファサードを使用可能にする

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
        if ($item->is_sold) {
             return redirect()->route('item.show', $item->id)->with('error', 'この商品は既に購入されています。');
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
        if ($item->is_sold) {
             return redirect()->route('item.show', $item->id)->with('error', 'この商品は既に購入されています。');
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
            return redirect()->back()->with('error', '配送先情報が登録されていません。');
        }
        
        // 配送先情報が不完全な場合のバリデーション（オプション）
        if (empty($shippingAddressObject->postal_code) || empty($shippingAddressObject->address)) {
            return redirect()->back()->with('error', '配送先情報が不完全です。ご購入前のご確認をお願いいたします。');
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
                'shipping_building_name' => $shippingAddressObject->building_name, 

                'payment_method' => $paymentMethod, 
                'status' => 'pending', 
            ];
            
            Order::create($dataToCreate);
            
            // Itemのis_soldフラグをtrueに更新する
            $item->update(['is_sold' => true]);

            // ★デバッグログ(info)を削除しました★

            DB::commit();
            return redirect('purchase.complete')->with('success', '購入手続きが完了しました！'); 

        } catch (\Exception $e) {
            DB::rollBack();
            // 例外発生時のエラーログは残します
            Log::error('Purchase Transaction FAILED. Exception: ' . $e->getMessage()); 
            
            return redirect()->back()->with('error', '購入処理中に重大なエラーが発生しました。時間を置いて再度お試しください。');
        }
    }

    public function showCompletion()
    {
        return view('purchase.complete');
    }
}
