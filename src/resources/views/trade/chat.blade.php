@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/trade_chat.css') }}">
@endsection

@section('body-params')
    data-show-rating-modal="{{ $show_rating_modal ? 'true' : 'false' }}"
@endsection    

@section('content')
<div class="container">
    <div class="trade-wrapper">
        <aside class="side-nav">
            <h3 class="side-nav__title">その他の取引</h3>

            <ul class="side-nav__list">
                @forelse($other_items as $other_item)
                    <li class="side-nav__item">
                        <a href="{{ url('/trade/chat/' . $other_item->id) }}" class="side-nav__link">
                            {{ $other_item->name }}
                        </a>
                    </li>
                @empty
                    <li class="side-nav__item side-nav__link">他の取引はありません</li>
                @endforelse
            </ul>
            </aside>

        <main class="chat-main">
            <div class="chat-header">
                <span class="chat-header__title">「{{ $user->id === $item->seller_id ? ($item->purchase->user->name ?? '購入者') : $item->seller->name }}」さんとの取引画面</span>

                    <button type="button" class="btn-complete-trade" onclick="openRatingModal()">
                        取引を完了する
                    </button>
                
            </div>

            <div class="item-info">
                <div class="item-info__img">
                    <img src="{{ $item->img_url ? asset('storage/' . $item->img_url) : asset('images/no_image.png') }}" alt="">
                </div>
                <div class="item-info__details">
                    <h2 class="item-info__name">{{ $item->name }}</h2>
                    <p class="item-info__price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class="message-list">
                @foreach($item->messages as $message)
                    <div class="message {{ $message->user_id === $user->id ? 'is-me' : 'is-other' }}">

                        <div class="message__user">
                            <div class="user-icon"></div>
                            <span class="user-name">{{ $message->user->name }}</span>
                        </div>

                        <div class="message__bubble">
                            {{ $message->content }}
                        </div>

                        @if($message->user_id === $user->id)
                            <div class="message__actions">
                                <span class="action-link" onclick="toggleEdit({{ $message->id }})">編集</span>
                                <form action="{{ url('/trade/message/' . $message->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-link" style="border:none; background:none; cursor:pointer; color:#999; font-size:10px;">
                                        削除
                                    </button>
                                </form> 
                            </div>
                            <div id="edit-form-{{ $message->id }}" style="display: none; margin-top: 10px;">
                                <form action="{{ url('/trade/message/' . $message->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="content" style="width: 100%; min-height: 60px;">{{ $message->content }}</textarea>
                                    <div style="text-align: right; margin-top: 5px;">
                                        <button type="button" onclick="toggleEdit({{ $message->id }})">キャンセル</button>
                                        <button type="submit">保存</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <form class="chat-form" action="/trade/chat/{{ $item->id }}/send" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                @error('content')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                @error('image')
                    <p class="error-message">{{ $message }}</p>
                @enderror

                <textarea name="content" class="chat-form__input" placeholder="取引メッセージを入力してください">{{ old('content') }}</textarea>

                <div class="chat-form__actions">
                    <label class="chat-form__btn-add-image">
                        画像を追加
                        <input type="file" name="image" id="image-input" class="chat-form__file-input" accept="image/png, image/jpeg">
                    </label>

                    <button type="submit" class="chat-form__submit-icon">
                        <i class="fa-regular fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
<div id="rating-modal" class="modal-overlay">
    <div class="modal-content">
        <h3 class="modal-title">取引が完了しました。</h3>
        <p class="modal-subtitle">今回の取引相手はどうでしたか？</p>
        
        <form action="{{ url('/trade/rating/' . $item->id) }}" method="POST">
            @csrf
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>

            <div class="submit-area">
                <button type="submit" class="btn-submit-rating">送信する</button>
            </div>
        </form>
    </div>
</div>
<script src="{{ asset('js/trade_chat.js') }}"></script>
@endsection
