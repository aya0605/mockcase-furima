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
                        <img src="{{ \Storage::url($item->image_url) }}" alt="{{ $item->name }}" class="purchase-item-image">
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
                        {{-- 支払い方法のセレクトボックス --}}
                        <select id="payment_method_select" class="form-control" {{ $item->sold() || $item->seller_id === Auth::id() ? 'disabled' : '' }}> 
                            <option value="convenience_store" selected>コンビニ払い</option>
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
                        <a href="/user/shipping-address/edit" class="edit-address-link">変更する</a> 
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
                        <span class="summary-value" id="selected_payment_method">コンビニ払い</span>
                    </div>
                </div>
                
                {{-- エラーメッセージ表示用コンテナ --}}
                <div id="resultContainer" class="mt-4" style="margin-bottom: 20px;"></div>

                <form id="purchaseConfirmationForm" action="/items/{{ $item->id }}/purchase" method="POST" class="purchase-form">
                    @csrf
                    <input type="hidden" name="payment_method" id="hidden_payment_method" value="convenience_store">
                    
                    @if ($item->sold())
                        <button type="button" class="confirm-purchase-button disabled" disabled>Sold</button>
                    @elseif ($item->seller_id === Auth::id())
                        <button type="button" class="confirm-purchase-button disabled" disabled>購入できません</button>
                    @else
                        <button type="submit" id="executePurchaseButton" class="confirm-purchase-button">購入する</button>
                    @endif
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

        const form = document.getElementById('purchaseConfirmationForm');
        const button = document.getElementById('executePurchaseButton');
        const resultContainer = document.getElementById('resultContainer'); 
        const itemId = {{ $item->id }}; 
        
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = metaTag ? metaTag.content : null;

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
        
        
        // =========================================================
        // 非同期購入処理のロジック
        // =========================================================
        
        function showMessageBox(message, isSuccess) {
            if (isSuccess) {
                resultContainer.innerHTML = ''; /
                return;
            }

            const color = '#721c24'; 
            const bgColor = '#f8d7da'; 
            const borderColor = '#f5c6cb'; 
            const typeText = 'エラー:';
            const iconPath = 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';

            const icon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 1.2rem; height: 1.2rem; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}" /></svg>`;

            resultContainer.innerHTML = `
                <div style="color: ${color}; background-color: ${bgColor}; border: 1px solid ${borderColor}; padding: 10px; border-radius: 4px; font-size: 14px; margin-top: 15px;">
                    <span style="margin-right: 5px;">${icon}</span>
                    <strong>${typeText}</strong> ${message}
                </div>
            `;
        }


        if (form && button && resultContainer) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault(); 
                
                if (!csrfToken) {
                    showMessageBox('セキュリティトークンが見つかりません。ページをリロードしてください。', false);
                    return;
                }

                button.disabled = true;
                button.textContent = '決済処理中...';
                button.style.opacity = 0.7; 
                button.style.cursor = 'wait';
                resultContainer.innerHTML = ''; 

                const postData = {
                    item_id: itemId,
                    payment_method: hiddenPaymentMethodInput.value, 
                };
                
                const purchaseApiUrl = form.getAttribute('action'); 
                
                try {
                    const response = await fetch(purchaseApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken, 
                        },
                        body: JSON.stringify(postData) 
                    });
                    
                    if (response.redirected) {
                         window.location.href = response.url;
                         return; 
                    }

                    const result = await response.json(); 
                    
                    if (!response.ok) {
                        const errorMsg = result.message || 'サーバーエラーが発生しました。';
                        if (response.status === 422 && result.errors) {
                             const validationErrors = Object.values(result.errors).flat().join(' ');
                             throw new Error(`入力エラー: ${validationErrors}`);
                        }
                        throw new Error(errorMsg);
                    }
                    
                    if (result.success) {
                        button.textContent = '購入完了済み';
                        button.classList.add('disabled');
                        button.disabled = true;
                        button.style.opacity = 1; 

                        paymentMethodSelect.disabled = true;
                        
                    } else {
                        throw new Error(result.message || '購入処理中に予期せぬエラーが発生しました。');
                    }

                } catch (error) {
                    showMessageBox(error.message, false);

                    button.disabled = false;
                    button.textContent = '再度購入を試みる';
                    button.style.opacity = 1; 
                    button.style.cursor = 'pointer';
                }
            });
        }
    });
</script>
@endsection
