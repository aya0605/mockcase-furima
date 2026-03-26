{{-- resources/views/items/detail.blade.php --}}

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item_detail.css') }}">
    {{-- FontAwesome: ハートやコメントのアイコンを表示するために読み込み --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('content')
<div class="item-detail-container">
    {{-- 左側：商品画像セクション --}}
    <div class="item-image-section">
        @if ($item->image_url)
            {{-- asset()関数を使用して、publicフォルダ内の画像パスを正しく生成 --}}
            <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-main-image">
        @else
            {{-- 画像がない場合のフォールバック画像 --}}
            <img src="{{ asset('images/no_image.png') }}" alt="画像なし" class="item-main-image">
        @endif
    </div>

    {{-- 右側：商品情報セクション --}}
    <div class="item-info-section">
        <h1 class="item-name">{{ $item->name }}</h1>
        @if ($item->brand)
            <p class="item-brand">ブランド名: {{ $item->brand }}</p>
        @endif

        {{-- number_format: 1000を1,000のようにカンマ区切りにする --}}
        <p class="item-price"> ￥{{ number_format($item->price) }}(税込)</p>

        {{-- いいね・コメント数表示エリア --}}
        <div class="item-interactions">
            <span class="like-count">
                <form action="/items/{{ $item->id }}/like" method="POST" style="display: inline;">
                    @csrf
                    @auth
                        {{-- ログイン中：Userモデルに定義した hasLiked メソッドでアイコンを切り替え --}}
                        @if (Auth::user()->hasLiked($item))
                            <button type="submit" class="like-button liked">
                                <i class="fas fa-heart"></i> {{-- 塗りつぶしのハート --}}
                            </button>
                        @else
                            <button type="submit" class="like-button">
                                <i class="far fa-heart"></i> {{-- 枠線のみのハート --}}
                            </button>
                        @endif
                    @else
                        {{-- 未ログイン：クリックするとログイン画面へ誘導 --}}
                        <a href="/login" class="like-button disabled-like-button" title="ログインしていいね！">
                            <i class="far fa-heart"></i>
                        </a>
                    @endauth
                </form>
                {{-- リレーション経由でいいねの総数を取得 --}}
                {{ $item->likes->count() }}
            </span>
            <span class="comment-count">
                <i class="fas fa-comment"></i>
                {{ $item->comments->count() }}
            </span>
        </div>

        {{-- 購入ボタンエリア --}}
        <div class="item-actions">
            @auth
                <a href="/items/{{ $item->id }}/purchase" class="buy-button">購入手続きへ</a>
            @else
                <a href="/login" class="buy-button">購入手続きへ (ログインが必要です)</a>
            @endauth
        </div>

        <div class="item-description-block">
            <h3>商品説明</h3>
            <p>{{ $item->description }}</p>
        </div>

        <div class="item-details-block">
            <h3>商品の情報</h3>
            <p><strong>カテゴリー:</strong>
                {{-- 多対多のリレーションだが、ここでは最初の1つを表示 --}}
                @if ($item->categories->isNotEmpty())
                    <span class="category-tag">{{ $item->categories->first()->name }}</span>
                @else
                    <span>カテゴリーなし</span>
                @endif
            </p>
            <p><strong>商品の状態:</strong> {{ $item->condition }}</p>
        </div>

        <p class="item-seller">出品者: {{ $item->seller->name }}</p>

        <hr>

        {{-- コメントセクション --}}
        <div class="item-comments-section">
            <h3>商品へのコメント</h3>
            <div class="comment-list">
                @forelse ($item->comments as $comment)
                    <div class="comment-item">
                        <p class="comment-user">
                            <strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}
                        </p>
                        <span class="comment-timestamp">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    
                @endforelse
            </div>

            {{-- コメント投稿フォーム --}}
            <form action="/comments/store/{{ $item->id }}" method="POST" class="comment-form">
                @csrf
                {{-- 未ログイン時は入力を無効化(disabled) --}}
                <textarea name="content" rows="3" placeholder="コメントを入力してください..." {{ Auth::check() ? '' : 'disabled' }}></textarea>

                @error('content')
                    <div class="alert alert-danger" style="color: red; margin-top: 5px;">{{ $message }}</div>
                @enderror

                @auth
                    <button type="submit">コメントを送信する</button>
                @else
                    <a href="/login" class="button comment-login-button">
                        コメントを送信するにはログインしてください
                    </a>
                @endauth
            </form>
        </div>
    </div>
</div>
@endsection