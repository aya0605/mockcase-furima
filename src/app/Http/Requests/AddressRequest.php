<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-\d{4}$/', 'max:8'], 
            'address' => ['required', 'string', 'max:255'], 
            'building_name' => ['required', 'string', 'max:255'], 
        ];
    }

    public function messages()
    {
        return [
            'postal_code.required' => '郵便番号は必須です。',
            'postal_code.regex' => '郵便番号はXXX-XXXXの形式で入力してください。',
            'postal_code.max' => '郵便番号は8文字以内で入力してください。',
            'address.required' => '住所は必須です。',
            'building_name.required' => '建物名は必須です。'
        ];
    }
}
