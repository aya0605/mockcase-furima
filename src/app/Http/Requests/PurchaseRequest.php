<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; 
use App\Models\Address; 

class PurchaseRequest extends FormRequest
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
            'payment_method' => ['required', 'string', 'in:credit_card,convenience_store'],
            'shipping_address_valid' => ['required', 'accepted'], 
       
        ];
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'shipping_address_valid.required' => '配送先情報が登録されていません。',
        ];
    }

    protected function prepareForValidation()
    {
        $user = Auth::user();
        $isShippingAddressValid = false; 

        if ($user) {
            $defaultAddress = $user->defaultShippingAddress(); 

            if (!$defaultAddress) {
                $defaultAddress = $user->addresses()->latest()->first();
            }

            if ($defaultAddress) {
                $isShippingAddressValid = !empty($defaultAddress->postal_code) &&
                                          !empty($defaultAddress->address) &&
                                          !empty($defaultAddress->building_name);
            }
        }
        
        $this->merge([
            'shipping_address_valid' => $isShippingAddressValid ? true : null,
        ]);
    }
}
