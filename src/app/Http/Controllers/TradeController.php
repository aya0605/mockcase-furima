<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Message;
use App\Models\Rating;
use App\Mail\TradeCompletedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\TradeMessageRequest;


class TradeController extends Controller
{
    public function showChat($item_id)
    {
        $item = Item::with(['seller', 'messages.user', 'purchase'])->findOrFail($item_id);

        $user = Auth::user();

        $other_items = Item::where('items.id', '!=', $item_id)
        ->whereHas('purchase') 
        ->where(function($query) use ($user) {
            $query->where('items.seller_id', $user->id) 
                  ->orWhereHas('purchase', function($q) use ($user) {
                      $q->where('user_id', $user->id); 
                  });
        })

        ->with(['latestMessage']) 
        ->leftJoin('messages', function ($join) {
            $join->on('items.id', '=', 'messages.item_id')
                ->whereRaw('messages.id = (select max(id) from messages as m where m.item_id = items.id)');
        })
        ->select('items.*')
        ->orderByRaw('COALESCE(messages.created_at, items.created_at) DESC') 
        ->get();

        $buyerId = $item->purchase ? $item->purchase->user_id : null; 

        if ((int)$user->id !== (int)$item->seller_id && (int)$user->id !== (int)$buyerId) {
        abort(403, 'アクセス権限がありません。');
    }

       if ($item->purchase) {
        $column = ((int)$user->id === (int)$item->seller_id) ? 'seller_last_read_at' : 'buyer_last_read_at';
        $item->purchase->update([$column => now()]);
    }

        $show_rating_modal = session('open_rating_modal', false);

        return view('trade.chat', compact('item', 'user', 'other_items', 'show_rating_modal'));
    }

    public function sendMessage(TradeMessageRequest $request, $item_id)
    {
        $item = Item::with('purchase')->findOrFail($item_id);
        $user = Auth::user();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/trade_images');
        }

        $item->messages()->create([
            'user_id' => $user->id,
            'content' => $request->content,
            'image_path' => $imagePath,
        ]);

        if ($item->purchase) {
        $column = ((int)$user->id === (int)$item->seller_id) ? 'seller_last_read_at' : 'buyer_last_read_at';
        $item->purchase->update([$column => now()]);
    }
    
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

        if ($user->id !== $item->seller_id) {
            Mail::to($item->seller->email)->send(new TradeCompletedNotification($item));
        }

        return redirect('/');
    }
}