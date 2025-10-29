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
    public function create()
    {
        $categories = Category::all();

        return view('items.create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $item = new Item();
        $item->name = $request->input('name');
        $item->brand = $request->input('brand');
        $item->description = $request->input('description');
        $item->price = $request->input('product_price');
        
        $conditionInput = $request->input('condition');

        if ($conditionInput === 'new') {
            
            $item->condition_id = Condition::$UNUSED; 
        } else {
            
            $item->condition_id = (int)$conditionInput;
        }
        
        $item->seller_id = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/items');
            $item->image_url = Storage::url($path);
        } else {
            $item->image_url = null;
        }

        $item->save();

        $selectedCategories = $request->input('categories');
        if (is_array($selectedCategories)) { 
            $item->categories()->sync($selectedCategories);
        } else {
            $item->categories()->detach();
        }

        return redirect('/')->with('message', '出品が完了しました。');
    }
}