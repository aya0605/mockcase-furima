<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\Request; 
use App\Http\Requests\CommentRequest;
use App\Http\Requests\PurchaseRequest; 
use Illuminate\Support\Facades\Auth; 

class ItemController extends Controller
{
    public function index(Request $request)
    {
       $tab = $request->input('tab', 'recommend'); 
        $keyword = $request->input('keyword');

        // 2. 基本となるクエリの構築
        $query = Item::query();

        // 3. FN014-4: 自分が出品した商品は表示されない
        if (Auth::check()) {
            // ユーザーIDを直接取得してクエリに追加
            $currentUserId = Auth::id();
            $query->where('seller_id', '!=', $currentUserId);
        }

        // 4. FN016: 検索機能 (商品名で部分一致検索)
        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // 5. FN014/FN015: タブによる表示の切り替え
        if ($tab === 'mylist') {
            // FN015: マイリスト (いいねした商品)
            if (Auth::check()) {
                // 認証済みの場合のみ、いいねした商品に絞り込む
                $likedItemIds = Auth::user()->likes->pluck('item_id');
                $query->whereIn('id', $likedItemIds);
            } else {
                // FN015-4: 未認証の場合は何も表示しない
                $query->where('id', null);
            }
            
        } else {
            // FN014: おすすめ (全商品表示)
            // デフォルトのクエリ (3. 4.) のまま
        }
        
        // 6. データの取得
        $items = $query->with('purchase') 
                       ->orderBy('created_at', 'desc')
                       ->paginate(12)
                       ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'tab' => $tab, // Bladeに現在のタブの状態を渡す
            'keyword' => $keyword, // Bladeに検索キーワードを渡す
        ]);
    }

    public function show(Item $item)
    {
        $item->load('categories', 'seller', 'comments.user', 'likes');
        return view('items.detail', compact('item')); 
    }

    public function storeComment(CommentRequest $request, Item $item)
    {
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->item_id = $item->id;
        $comment->content = $request->input('content');
        $comment->save();

        return redirect("/items/{$item->id}")->with('success', 'コメントを投稿しました！');
    }

    public function toggleLike(Request $request, Item $item)
    {
        $user = Auth::user();

        if ($user->hasLiked($item)) {
            $user->likes()->where('item_id', $item->id)->delete();
            $message = 'いいね機能解除';
        } else {
            $user->likes()->create(['item_id' => $item->id]);
            $message = 'いいねしました！';
        }
        return redirect("/items/{$item->id}")->with('status', $message);
    }
}
