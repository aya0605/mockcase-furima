<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Auth;

class SellController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        return view('create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $item = new Item();
        $item->name = $request->input('name');
        $item->brand = $request->input('brand');
        $item->description = $request->input('description');
        $item->price = $request->input('product_price');
        $item->condition = $request->input('condition');
        $item->seller_id = Auth::id();

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $dir = 'images';
            $file_name = $imageFile->getClientOriginalName();
            $path_in_storage = $imageFile->storeAs('public/' . $dir, $file_name);
            $item->image_url = 'storage/' . $dir . '/' . $file_name;
        } else {
            $item->image_url = null;
        }

        $item->save();

        if ($request->filled('categories')) {
            $item->categories()->sync($request->input('categories'));
        } else {
            $item->categories()->detach();
        }

        return redirect('/')->with('message', '出品が完了しました。');
    }
}
