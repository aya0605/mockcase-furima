<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;

class SellController extends Controller
{
    /**
     * 出品画面の表示
     */
    public function create()
    {
        // 選択肢として表示するために、データベースから全カテゴリを取得
        $categories = Category::all();

        return view('items.create', compact('categories'));
    }

    /**
     * 出品商品の保存処理
     */
    public function store(ExhibitionRequest $request)
    {
        // 1. Itemインスタンスの作成と基本情報のセット
        $item = new Item();
        $item->name = $request->input('name');
        $item->brand = $request->input('brand');
        $item->description = $request->input('description');
        $item->price = $request->input('product_price');
        
        // 商品の状態（コンディション）の判定
        $conditionInput = $request->input('condition');

        if ($conditionInput === 'new') {
            // 文字列 'new' が送られてきた場合は、モデルで定義した定数（新品）を使用
            $item->condition_id = Condition::$UNUSED; 
        } else {
            // それ以外（数字IDなど）は数値に変換して代入
            $item->condition_id = (int)$conditionInput;
        }
        
        // 2. ログイン中のユーザーを出品者（seller_id）として紐付け
        $item->seller_id = Auth::id();

        // 3. 商品画像のアップロード処理
        if ($request->hasFile('image')) {
            // ストレージ内の 'public/items' フォルダに保存
            $path = $request->file('image')->store('public/items');
            // DBにはブラウザからアクセス可能なURL形式で保存（/storage/...）
            $item->image_url = Storage::url($path);
        } else {
            $item->image_url = null;
        }

        // ここで一旦、商品の基本情報を保存（IDが採番される）
        $item->save();

        // 4. カテゴリの紐付け（中間テーブルの更新）
        $selectedCategories = $request->input('categories');
        
        if (is_array($selectedCategories)) { 
            // syncメソッドで中間テーブルを一括更新（選択されたIDだけを有効にする）
            $item->categories()->sync($selectedCategories);
        } else {
            // 何も選択されていない場合は紐付けをすべて解除
            $item->categories()->detach();
        }

        return redirect('/')->with('message', '出品が完了しました。');
    }
}