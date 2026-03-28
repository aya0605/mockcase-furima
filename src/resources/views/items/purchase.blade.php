@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
    <div class="purchase-container">
        <div class="purchase-layout">
            <div class="purchase-main-content">
                {{-- 左側 --}}
                <div class="purchase-item-info">
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
                    @else
                        <p>お届け先情報を表示するにはログインが必要です。</p>
                        <a href="/login" class="edit-address-link">ログインする</a>
                    @endauth
                </div>
            </div>

            <div class="purchase-sidebar">
                {{-- 右側 --}}
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
        // --- 1. 要素の取得 ---
        const paymentMethodSelect = document.getElementById('payment_method_select'); 
        const selectedPaymentMethodElement = document.getElementById('selected_payment_method');
        const hiddenPaymentMethodInput = document.getElementById('hidden_payment_method'); 
        const form = document.getElementById('purchaseConfirmationForm');
        const button = document.getElementById('executePurchaseButton');
        const resultContainer = document.getElementById('resultContainer'); 
        
        // LaravelのCSRF保護をJSでも通すためにトークンを取得
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = metaTag ? metaTag.content : null;

        // --- 2. 支払い方法の表示連動 ---
        function updatePaymentMethodDisplay() {
            const names = { 'credit_card': 'クレジットカード', 'convenience_store': 'コンビニ払い' };
            const selectedMethod = paymentMethodSelect.value;
            selectedPaymentMethodElement.textContent = names[selectedMethod] || '不明';
            hiddenPaymentMethodInput.value = selectedMethod; // 隠し入力に値をコピー
        }

        paymentMethodSelect.addEventListener('change', updatePaymentMethodDisplay);
        
        // --- 3. メッセージ表示用関数 ---
        function showMessageBox(message, isSuccess) {
            if (isSuccess) { resultContainer.innerHTML = ''; return; }
            // エラー時のHTMLを動的に生成
            resultContainer.innerHTML = `
                <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; font-size: 14px; margin-top: 15px;">
                    <strong>エラー:</strong> ${message}
                </div>
            `;
        }

        // --- 4. 非同期購入処理（メインロジック） ---
        if (form && button) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault(); // 通常のページ遷移をキャンセル
                
                // 二重送信防止のためにボタンを無効化
                button.disabled = true;
                button.textContent = '決済処理中...';
                
                const purchaseApiUrl = form.getAttribute('action'); 
                
                try {
                    // Fetch APIを使用してサーバーにリクエストを送信
                    const response = await fetch(purchaseApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken, 
                        },
                        body: JSON.stringify({
                            item_id: {{ $item->id }},
                            payment_method: hiddenPaymentMethodInput.value, 
                        }) 
                    });
                    
                    // リダイレクト指示があれば従う
                    if (response.redirected) {
                         window.location.href = response.url;
                         return; 
                    }

                    const result = await response.json(); 
                    
                    if (!response.ok) {
                        throw new Error(result.message || 'エラーが発生しました。');
                    }
                    
                    if (result.success) {
                        // 成功したら完了表示に切り替え
                        button.textContent = '購入完了済み';
                        button.classList.add('disabled');
                        alert('購入が完了しました！');
                    }

                } catch (error) {
                    // 失敗したらメッセージを表示してボタンを元に戻す
                    showMessageBox(error.message, false);
                    button.disabled = false;
                    button.textContent = '再度購入を試みる';
                }
            });
        }
    });
</script>
@endsection