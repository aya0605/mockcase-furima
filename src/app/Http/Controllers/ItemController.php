<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request; 
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth; 

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::latest()->paginate(8);

        return view('index', compact('items'));
    }

    public function show(Item $item)
    {
        $item->load('categories', 'seller', 'comments.user', 'likes');
        
        return view('show', compact('item'));
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
