{{-- resources/views/items/purchase.blade.php --}}

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
    <div class="purchase-container">
        <div class="purchase-layout">
            <div class="purchase-main-content">
                {{-- 左側のメインコンテンツ --}}
                <div class="purchase-item-info">
                    {{-- 1. 商品画像 --}}
                    @if ($item->image_url)
                        <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="purchase-item-image">
                    @else
                        <img src="{{ asset('images/no_image.png') }}" alt="画像なし" class="purchase-item-image">
                    @endif

                    <div class="item-details-wrapper">
                        <h2>{{ $item->name }}</h2>
                        <p class="purchase-item-price">価格: ￥{{ number_format($item->price) }}(税込)</p>
                    </div>
                </div>

                <div class="payment-method-info">
                    <h3>支払い方法</h3>
                    <div class="form-group">
                        <select id="payment_method_select" class="form-control"> 
                        <option value="convenience_store">コンビニ払い</option>
                            <option value="credit_card">クレジットカード</option>
                        </select>
                        @error('payment_method')
                            <div class="alert alert-danger" style="color: red; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="shipping-address-info">
                    <h3>配送先</h3>
                    @auth
                        @if($shippingAddress)
                            <p><strong>郵便番号:</strong> {{ $shippingAddress->postal_code ?? '未登録' }}</p>
                            <p><strong>住所:</strong> {{ $shippingAddress->address ?? '未登録' }}</p>
                            <p><strong>建物名:</strong> {{ $shippingAddress->building_name ?? '未登録' }}</p>
                            @if (empty($shippingAddress->postal_code) || empty($shippingAddress->address))
                                <p class="text-danger">配送先情報が未登録または不完全です。ご購入前のご確認をお願いいたします。</p>
                            @endif
                        @else
                            <p>配送先が登録されていません。</p>
                        @endif
                        <a href="/user/shipping-address/edit?item_id={{ $item->id }}" class="edit-address-link">変更する</a>
                        @error('shipping_address_valid')
                            <div class="alert alert-danger" style="color: red; margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    @else
                        <p>お届け先情報を表示するにはログインが必要です。</p>
                        <a href="/login" class="edit-address-link">ログインする</a>
                    @endauth
                </div>
            </div>

            <div class="purchase-sidebar">
                {{-- 右側のサイドバーコンテンツ --}}
                <div class="purchase-summary-box">
                    <div class="summary-row">
                        <span class="summary-label">商品代金</span>
                        <span class="summary-value">￥{{ number_format($item->price) }}</span>
                    </div>

                    <div class="summary-row payment-display-row">
                        <span class="summary-label">支払い方法</span>
                        <span class="summary-value" id="selected_payment_method">クレジットカード</span>
                    </div>
                </div>

                <form action="/items/{{ $item->id }}/purchase" method="POST" class="purchase-form">
                    @csrf
                    <input type="hidden" name="payment_method" id="hidden_payment_method">

                    <button type="submit" class="confirm-purchase-button">購入する</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const paymentMethodSelect = document.getElementById('payment_method_select'); 
        const selectedPaymentMethodElement = document.getElementById('selected_payment_method');
        const hiddenPaymentMethodInput = document.getElementById('hidden_payment_method'); 

        const paymentMethodNames = {
            'credit_card': 'クレジットカード',
            'convenience_store': 'コンビニ払い'
        };

        function updatePaymentMethodDisplay() {
            const selectedMethod = paymentMethodSelect.value;
            selectedPaymentMethodElement.textContent = paymentMethodNames[selectedMethod] || '不明';
            hiddenPaymentMethodInput.value = selectedMethod; 
        }

        updatePaymentMethodDisplay();

        paymentMethodSelect.addEventListener('change', updatePaymentMethodDisplay);
    });
</script>
@endsection