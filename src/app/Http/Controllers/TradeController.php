<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Message;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\TradeMessageRequest;


class TradeController extends Controller
{
    public function showChat($item_id)
    {
        $item = Item::with(['seller', 'messages.user', 'purchase'])->findOrFail($item_id);

        $user = Auth::user();

        $other_items = Item::where('id', '!=', $item_id)
        ->whereHas('purchase') // すでに誰かが購入している（取引が始まっている）
        ->where(function($query) use ($user) {
            $query->where('seller_id', $user->id) // 自分が「出品者」
                  ->orWhereHas('purchase', function($q) use ($user) {
                      $q->where('user_id', $user->id); // または自分が「購入者」
                  });
        })
        ->get();

        $buyerId = $item->purchase ? $item->purchase->user_id : null; 

        if ((int)$user->id !== (int)$item->seller_id && (int)$user->id !== (int)$buyerId) {
        abort(403, 'アクセス権限がありません。');
    }

        return view('trade.chat', compact('item', 'user', 'other_items'));
    }

    public function sendMessage(TradeMessageRequest $request, $item_id)
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/trade_images');
        }

        \App\Models\Message::create([
            'item_id' => $item_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image_path' => $imagePath,
        ]);

        return redirect('/trade/chat/' . $item_id);
    }

    public function destroyMessage(Message $message) {
        if (Auth::id() !== $message->user_id) {
            abort(403);
        }

        $item_id = $message->item_id;
        $message->delete();

        return redirect('/trade/chat/' . $item_id);
    }

    public function updateMessage(Request $request, Message $message)
    {
        if (Auth::id() !== $message->user_id) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->update([
            'content' => $request->content
        ]);

        return redirect('/trade/chat/' . $message->item_id);
    }

    public function storeRating(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        $to_user_id = ($user->id === $item->seller_id)
        ? $item->purchase->user_id
        : $item->seller_id;

        $user->ratingsFrom()->create([
            'item_id' => $item_id,
            'to_user_id' => $to_user_id,
            'rating' => $request->rating,
        ]);
        
        if ($item->purchase) {
            $item->purchase->update(['status' => 'completed']);
        }

        return redirect('/');
    }
}