@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="container">
    {{-- プロフィール更新完了時などのメッセージ表示 --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="user">
        <div class="user__info">
            <div class="user__img">
                {{-- プロフィール画像 --}}
                @if (Auth::user()->profile_image_path)
                    <img src="{{ Storage::url(Auth::user()->profile_image_path) }}" alt="プロフィール画像" class="user__icon">
                @else
                    <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="user__icon">
                @endif
            </div>
            <p class="user__name">{{ Auth::user()->name }}</p>
        </div>
        <div class="mypage__user--btn">
            <a class="btn2" href="/user/profile/edit">プロフィールを編集</a>
        </div>
    </div>

    {{-- タブ切り替えメニュー --}}
    <div class="border">
        <ul class="border__list">
            <li class="@if ($page === 'sell') active @endif"><a href="/user/profile?page=sell">出品した商品</a></li>
            <li class="@if ($page === 'buy') active @endif"><a href="/user/profile?page=buy">購入した商品</a></li>
            <li class="@if ($page === 'trading') active @endif"><a href="/user/profile?page=trading">取引中の商品</a></li>
        </ul>
    </div>

    <div class="items">
        <div class="items-list-wrapper">
            
            {{-- 「出品した商品」タブの場合 --}}
            @if ($page === 'sell')
                @forelse ($soldItems as $item)
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
                @empty
                    <p>まだ出品した商品はありません。</p>
                @endforelse
                
            {{-- 「購入した商品」 --}}
            @elseif ($page === 'buy')
                @forelse ($purchasedItems as $item)
                @if ($item)
                <div class="item-col">
                    <div class="item">
                        <a href="/items/{{ $item->id }}"> 
                            <div class="item__img--container @if ($item->sold()) sold @endif">
                                @if ($item->image_url)
                                <img src="{{ asset($item->image_url) }}" class="item__img" alt="{{ $item->name }}">
                                @else
                                <img src="{{ asset('images/no_image.png') }}" class="item__img" alt="No Image">
                                @endif
                            </div>
                            <p class="item__name">{{ $item->name }}</p>
                        </a>
                    </div>
                </div>
                @endif
                @empty
                    <p>まだ購入した商品はありません。</p>
                @endforelse

                @elseif ($page === 'trading')
                @forelse ($tradingItems as $item)
                    <div class="item-col">
                        <div class="item">
                            <a href="/trade/chat/{{ $item->id }}">
                                <div class="item__img--container">
                                    <img src="{{ $item->image_url ? asset($item->image_url) : asset('images/no_image.png') }}" class="item__img">
                                </div>
                                <p class="item__name">{{ $item->name }}</p>
                            </a>
                        </div>
                    </div>
                @empty
                    <p>現在取引中の商品はありません。</p>
                @endforelse
            @endif
        </div>
    </div>
</div>
@endsection