<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array|min:1|max:1',
            'categories.*' => 'integer|exists:categories,id', 
            'condition' => 'required|in:new,near_unused,slightly_damaged,damaged,bad_condition',
            'product_price' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください。',
            'description.required' => '商品説明を入力してください。',
            'description.max' => '商品説明は255文字以内で入力してください。',
            'image.required' => '商品画像を選択してください。',
            'image.mimes' => 'JPEG、PNG、またはGIF形式で選択してください。',
            'categories.required' => 'カテゴリーを選択してください。',
            'condition.required' => '商品の状態を選択してください。',
            'product_price.required' => '商品価格を入力してください。',
            'product_price.numeric' => '商品価格を数値で入力してください。',
            'product_price.min' => '商品価格は0以上の数値を入力してください。',
        ];
    }

}
