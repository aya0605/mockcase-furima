@extends('layouts.app')

@section('css')
    {{-- CSSはprofile.cssのみ残しました --}}
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- 1. ユーザー情報エリアの修正 (模範解答のクラス名に合わせる) --}}
    <div class="user">
        <div class="user__info">
            <div class="user__img">
                @if (Auth::user()->profile_image_path)
                    <img src="{{ Storage::url(Auth::user()->profile_image_path) }}" alt="プロフィール画像" class="user__icon">
                @else
                    {{-- default_profile.pngが404エラーなので、asset()のパスを修正するか、ファイルを配置してください --}}
                    <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="user__icon">
                @endif
            </div>
            <p class="user__name">{{ Auth::user()->name }}</p>
        </div>
        <div class="mypage__user--btn">
            <a class="btn2" href="/user/profile/edit">プロフィールを編集</a>
        </div>
    </div>

    {{-- 2. タブ切り替え構造の修正 (模範解答のクラス名とURLロジックに合わせる) --}}
    <div class="border">
        <ul class="border__list">
            {{-- page='sell' が現在のタブの状態を保持する変数 $page に入っていると仮定 --}}
            <li class="@if ($page === 'sell') active @endif"><a href="/user/profile?page=sell">出品した商品</a></li>
            <li class="@if ($page === 'buy') active @endif"><a href="/user/profile?page=buy">購入した商品</a></li>
        </ul>
    </div>

    {{-- 3. 商品一覧表示エリアの修正 (模範解答のクラス名と構造に合わせる) --}}
    <div class="items">
        <div class="items-list-wrapper">
            
            @if ($page === 'sell')
                {{-- 出品した商品セクション (FN015-1) --}}
                @forelse ($soldItems as $item)
                <div class="item-col">
                    <div class="item">
                        <a href="/items/{{ $item->id }}">
                            {{-- 模範解答の画像コンテナの構造に合わせる。sold()の判定はコントローラー側で実施 --}}
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
                @empty
                    <p>まだ出品した商品はありません。</p>
                @endforelse
            @elseif ($page === 'buy')
                {{-- 購入した商品セクション (FN015-2) --}}
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
                            {{-- $purchase->item->name から $item->name に修正 --}}
                            <p class="item__name">{{ $item->name }}</p>
                        </a>
                    </div>
                </div>
                @endif
                @empty
                    <p>まだ購入した商品はありません。</p>
                @endforelse
            @endif
        </div>

        {{-- ページネーション (現在は簡略化のため $soldItems->links() を削除) --}}
        {{-- コントローラー側で $items をページネーションにかけている場合、links() は $items に適用 --}}
        {{-- {{ $items->links() }} --}} 
    </div>
</div>
@endsection