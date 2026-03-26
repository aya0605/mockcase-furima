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
    /**
     * 購入確認画面の表示
     */
    public function showPurchaseForm(Item $item)
    {
        $user = Auth::user();
        $shippingAddress = null;

        // ログイン中の場合、デフォルトまたは最新の配送先住所を取得
        if ($user) {
            $shippingAddress = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();
        }

        // すでに売り切れている場合は商品詳細へ戻す（二重購入防止）
        if ($item->sold()) {
             return redirect()->to("/items/{$item->id}")->with('error', 'この商品は既に購入されています。');
        }
        
        // 支払い方法の選択肢定義
        $paymentMethods = [
            'credit_card' => 'カード支払い', 
            'convenience_store' => 'コンビニ払い',
        ];

        return view('items.purchase', compact('item', 'shippingAddress', 'paymentMethods'));
    }
    
    /**
     * 購入実行処理（注文確定）
     */
    public function processPurchase(PurchaseRequest $request, Item $item) 
    {
        $user = Auth::user();

        // 購入直前の最終売り切れチェック
        if ($item->sold()) {
             return response()->json([
                'success' => false,
                'message' => 'この商品は既に購入されています。'
            ], 409);
        }

        // 支払い方法に基づいた手数料計算
        $paymentMethod = $request->input('payment_method'); 
        $basePrice = $item->price;
        $paymentFee = 0;

        // コンビニ払いの場合は一律150円の手数料を加算
        if ($paymentMethod === 'convenience_store') { 
            $paymentFee = 150; 
        }

        $totalAmount = $basePrice + $paymentFee; // 最終的な支払い合計金額
        
        // 最新の配送先情報を取得
        $shippingAddressObject = $user->defaultShippingAddress() ?? $user->addresses()->latest()->first();

        // 住所未登録・不備のバリデーション
        if (!$shippingAddressObject) {
            return response()->json([
                'success' => false,
                'message' => '配送先情報が登録されていません。'
            ], 400); 
        }
        
        if (empty($shippingAddressObject->postal_code) || empty($shippingAddressObject->address)) {
            return response()->json([
                'success' => false,
                'message' => '配送先情報が不完全です。ご購入前のご確認をお願いいたします。'
            ], 400); 
        }

        // データベースのトランザクション開始（注文作成と商品ステータス更新を保護）
        DB::beginTransaction();

        try {
            // 注文(Order)レコードの作成
            // 後でユーザーが住所を変更しても「購入時の住所」が残るようにコピーして保存する
            $dataToCreate = [
                'buyer_id' => $user->id,
                'item_id' => $item->id,
                'total_amount' => $totalAmount, 
                'order_date' => now(),

                // 郵便番号からハイフンを除去して統一感を出す
                'shipping_postal_code' => str_replace('-', '', $shippingAddressObject->postal_code), 
                'shipping_prefecture' => $shippingAddressObject->prefecture ?? '', 
                'shipping_city' => $shippingAddressObject->city ?? '',       
                'shipping_street_address' => $shippingAddressObject->address,
                'shipping_building_name' => $shippingAddressObject->building_name ?? '', 

                'payment_method' => $paymentMethod, 
                'status' => 'pending', // 支払待ち状態で初期化
            ];

            Order::create($dataToCreate);

            // 2. 【重要】purchasesテーブルにもデータを保存する（チャット画面用）
            \App\Models\Purchase::create([
                'item_id' => $item->id,
                'user_id' => $user->id,
                'purchased_at' => now(),
                'shipping_address_id' => $shippingAddressObject->id, // AddressesTableSeederで作ったID
                'payment_method' => $paymentMethod,
                'status' => 'completed',
            ]);

            // 3. 商品を売却済み状態にする
            $item->update(['sold_status' => true]);

            DB::commit(); // 処理確定

            return response()->json([
                'success' => true,
                'message' => '購入が完了しました！',
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // エラー時はすべて巻き戻す
            
            Log::error('Purchase Transaction FAILED. Exception: ' . $e->getMessage()); 
            
            return response()->json([
                'success' => false,
                'message' => '購入処理中にエラーが発生しました。' . $e->getMessage()
            ], 500); 
        }
    }
}
