@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item_detail.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('content')
    <div class="item-detail-container">
        <div class="item-image-section">
            @if ($item->image_url)
                <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="item-main-image">
            @else
                <img src="{{ asset('images/no_image.png') }}" alt="画像なし" class="item-main-image">
            @endif
        </div>

        <div class="item-info-section">
            <h1 class="item-name">{{ $item->name }}</h1>
            @if ($item->brand)
                <p class="item-brand">ブランド名: {{ $item->brand }}</p>
            @endif

            <p class="item-price"> ￥{{ number_format($item->price) }}(税込)</p>

            <div class="item-interactions"> 
                <span class="like-count">
                    <form action="/items/{{ $item->id }}/like" method="POST" style="display: inline;">
                        @csrf
                        @auth
                            @if (Auth::user()->hasLiked($item))
                                <button type="submit" class="like-button liked">
                                    <i class="fas fa-heart"></i> 
                                </button>
                            @else
                                <button type="submit" class="like-button">
                                    <i class="far fa-heart"></i> 
                                </button>
                            @endif
                        @else
                            <a href="/login" class="like-button disabled-like-button" title="ログインしていいね！">
                                <i class="far fa-heart"></i>
                            </a>
                        @endauth
                    </form>
                    {{ $item->likes->count() }}
                </span>
                <span class="comment-count">
                    <i class="fas fa-comment"></i> 
                    {{ $item->comments->count() }}
                </span>
            </div>

            <div class="item-actions">
                <a href="#" class="buy-button">購入手続きへ</a>
            </div>

            <div class="item-description-block">
                <h3>商品説明</h3>
                <p>{{ $item->description }}</p>
            </div>

            <div class="item-details-block">
                <h3>商品の情報</h3>
                <p><strong>カテゴリー:</strong>
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
                        <p class="no-comments">こちらにコメントが入ります。</p>
                    @endforelse
                </div>

                <form action="/comments/store/{{ $item->id }}" method="POST" class="comment-form">
                    @csrf
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