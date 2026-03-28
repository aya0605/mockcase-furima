@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ユーザー情報 --}}
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (Auth::user()->profile_image_path)
                    <img src="{{ Storage::url(Auth::user()->profile_image_path) }}" alt="プロフィール画像" class="user__icon">
                @else
                    <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="user__icon">
                @endif
            </div>
            
            {{-- 名前と星 --}}
            <div class="user__text"> 
                <p class="user__name">{{ Auth::user()->name }}</p>
                
                @php 
                    $avgRating = Auth::user()->getAverageRating(); 
                @endphp

                @if($avgRating > 0)
                    <div class="user__rating">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="rating-star {{ $i <= $avgRating ? 'is-active' : '' }}">★</span>
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        <div class="mypage__user--btn">
            <a class="btn2" href="/user/profile/edit">プロフィールを編集</a>
        </div>
    </div> 

    {{-- タブメニュー --}}
    <div class="border">
        @php
            $totalUnreadCount = $allTradingItems->sum(fn($item) => $item->getUnreadCount(Auth::id()));
        @endphp

        <ul class="border__list">
            <li class="@if ($page === 'sell') active @endif"><a href="/user/profile?page=sell">出品した商品</a></li>
            <li class="@if ($page === 'buy') active @endif"><a href="/user/profile?page=buy">購入した商品</a></li>
            <li class="@if ($page === 'trading') active @endif" style="position: relative;">
                <a href="/user/profile?page=trading">
                    取引中の商品
                    @if($totalUnreadCount > 0)
                        <span class="tab-badge">{{ $totalUnreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    {{-- 商品一覧 --}}
    <div class="items">
        <div class="items-list-wrapper">
            @if ($page === 'sell')
                @foreach ($soldItems as $item)
                    <div class="item-col">
                        <div class="item">
                            <a href="/items/{{ $item->id }}">
                                <div class="item__img--container @if ($item->sold()) sold @endif">
                                    <img src="{{ $item->image_url ? asset($item->image_url) : asset('images/no_image.png') }}" class="item__img">
                                </div>
                                <p class="item__name">{{ $item->name }}</p>
                            </a>
                        </div>
                    </div>
                @endforeach

            @elseif ($page === 'buy')
                @foreach ($purchasedItems as $item)
                    <div class="item-col">
                        <div class="item">
                            <a href="/items/{{ $item->id }}"> 
                                <div class="item__img--container @if ($item->sold()) sold @endif">
                                    <img src="{{ $item->image_url ? asset($item->image_url) : asset('images/no_image.png') }}" class="item__img">
                                </div>
                                <p class="item__name">{{ $item->name }}</p>
                            </a>
                        </div>
                    </div>
                @endforeach

            @elseif ($page === 'trading')
                @foreach ($tradingItems as $item)
                    <div class="item-col">
                        <a href="/trade/chat/{{ $item->id }}">
                            <div class="item__img--container" style="position: relative;">
                                @php $unreadCount = $item->getUnreadCount(Auth::id()); @endphp
                                @if($unreadCount > 0)
                                    <div class="notification-badge">{{ $unreadCount }}</div>
                                @endif
                                <img src="{{ $item->image_url ? asset($item->image_url) : asset('images/no_image.png') }}" class="item__img">
                            </div>
                            <p class="item__name">{{ $item->name }}</p>
                        </a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection