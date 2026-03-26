<?php

namespace App\Http\Controllers;

// 使用するモデルや外部クラスのインポート
use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\Transaction; 
use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log; 

class ItemController extends Controller
{
    /**
     * 商品一覧画面の表示
     * おすすめ（全商品）とマイリスト（いいねした商品）の切り替えを行う
     */
    public function index(Request $request)
    {
        // 現在のタブ（recommend または mylist）と検索キーワードを取得
        $tab = $request->input('tab', 'recommend');
        $keyword = $request->input('keyword');

        $query = Item::query();

        // ログインしている場合、自分が商品一覧に出ないように除外（フリマアプリの一般的仕様）
        if (Auth::check()) {
            $currentUserId = Auth::id();
            $query->where('seller_id', '!=', $currentUserId);
        }

        // キーワード検索がある場合、商品名で部分一致検索
        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // マイリストタブが選択された場合の処理
        if ($tab === 'mylist') {
            if (Auth::check()) {
                // 自分が「いいね」した商品のID一覧を取得して絞り込み
                $likedItemIds = Auth::user()->likes->pluck('item_id');
                $query->whereIn('id', $likedItemIds);
            } else {
                // 未ログインなら何も表示しない
                $query->where('id', null);
            }
        }

        // データの取得（売却済み判定用のpurchaseリレーションを含める）
        $items = $query->with('purchase')
                       ->orderBy('created_at', 'desc') // 新着順
                       ->paginate(12)                  // 1ページ12件
                       ->withQueryString();            // ページをめくっても検索条件を保持

        return view('items.index', [
            'items' => $items,
            'tab' => $tab, 
            'keyword' => $keyword, 
        ]);
    }

    /**
     * 商品詳細画面の表示
     */
    public function show(Item $item)
    {
        // 関連するカテゴリ、出品者、コメント（とその投稿者）、いいね情報を一括取得（Eager Load）
        $item->load('categories', 'seller', 'comments.user', 'likes');
        return view('items.detail', compact('item'));
    }

    /**
     * 商品へのコメント投稿
     */
    public function storeComment(CommentRequest $request, Item $item)
    {
        $comment = new Comment();
        $comment->user_id = Auth::id();     // 誰が
        $comment->item_id = $item->id;      // どの商品に
        $comment->content = $request->input('content'); // 何と言ったか
        $comment->save();

        return redirect("/items/{$item->id}")->with('success', 'コメントを投稿しました！');
    }

    /**
     * いいねの切り替え（トグル機能）
     */
    public function toggleLike(Request $request, Item $item)
    {
        $user = Auth::user();

        // すでにいいねしていれば削除、していなければ新規作成
        if ($user->hasLiked($item)) {
            $user->likes()->where('item_id', $item->id)->delete();
            $message = 'いいね機能解除';
        } else {
            $user->likes()->create(['item_id' => $item->id]);
            $message = 'いいねしました！';
        }
        return redirect("/items/{$item->id}")->with('status', $message);
    }

    /**
     * 商品購入処理（非同期通信/API想定）
     */
    public function purchase(PurchaseRequest $request, Item $item)
    {
        $validated = $request->validated(); // バリデーション済みデータの取得
        $userId = Auth::id();

        // 【ガード設定】売り切れチェック
        if ($item->sold()) {
            return response()->json(['success' => false, 'message' => 'この商品は売り切れました。'], 409); 
        }
        // 【ガード設定】自分が出品したものは買えない
        if ($item->seller_id === $userId) {
            return response()->json(['success' => false, 'message' => '自身が出品した商品は購入できません。'], 403); 
        }

        try {
            // トランザクション開始（商品更新と取引記録作成をセットで行う）
            DB::beginTransaction();

            // 商品を「売り切れ」状態に更新
            $item->update(['sold_status' => true]);

            // 取引（Transaction）テーブルにレコードを作成
            $transaction = Transaction::create([
                'item_id' => $item->id,
                'buyer_id' => $userId,
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $item->price,
            ]);

            DB::commit(); // すべて成功したら確定

            return response()->json([
                'success' => true,
                'message' => '購入が完了しました。',
                'transaction_id' => $transaction->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // どこかで失敗したら元に戻す
            Log::error("Purchase failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '購入処理中にシステムエラーが発生しました。'], 500); 
        }
    }
}