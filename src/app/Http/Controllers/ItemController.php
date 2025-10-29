<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {
       $tab = $request->input('tab', 'recommend');
        $keyword = $request->input('keyword');

        $query = Item::query();

        if (Auth::check()) {
            $currentUserId = Auth::id();
            $query->where('seller_id', '!=', $currentUserId);
        }

        if ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        if ($tab === 'mylist') {
            if (Auth::check()) {
                $likedItemIds = Auth::user()->likes->pluck('item_id');
                $query->whereIn('id', $likedItemIds);
            } else {
                $query->where('id', null);
            }

        } else {
            
        }

        $items = $query->with('purchase')
                       ->orderBy('created_at', 'desc')
                       ->paginate(12)
                       ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'tab' => $tab, 
            'keyword' => $keyword, 
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

    /**
     * 
     * 
     *
     * @param PurchaseRequest $request
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(PurchaseRequest $request, Item $item)
    {
        $validated = $request->validated();

        $userId = Auth::id();

        if ($item->sold()) {
            return response()->json(['success' => false, 'message' => 'この商品は売り切れました。'], 409); 
        }
        if ($item->seller_id === $userId) {
            return response()->json(['success' => false, 'message' => '自身が出品した商品は購入できません。'], 403); 
        }

        try {
            DB::beginTransaction();

            $item->update(['sold_status' => true]);

            $transaction = Transaction::create([
                'item_id' => $item->id,
                'buyer_id' => $userId,
                'payment_method' => $validated['payment_method'],
                'amount_paid' => $item->price,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '購入が完了しました。',
                'transaction_id' => $transaction->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Purchase failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '購入処理中にシステムエラーが発生しました。'], 500); 
        }
    }
}
