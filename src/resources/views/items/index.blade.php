@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="main-container">
    {{-- タブ切り替えメニュー（おすすめ / マイリスト） --}}
    <div class="menu-nav">
        <ul class="menu-list">
            <li class="menu-item @if ($tab === 'recommend') active @endif">
                <a href="/?tab=recommend&keyword={{ $keyword }}">おすすめ</a>
            </li>
            
            <li class="menu-item @if ($tab === 'mylist') active @endif">
                <a href="/?tab=mylist&keyword={{ $keyword }}">マイリスト</a>
            </li>
        </ul>
    </div>

    {{-- 商品グリッド表示エリア --}}
    <div class="item-grid">
        <div class="items-list-wrapper">
            {{-- @forelse: 商品があればループ、なければ @empty 以降を表示 --}}
            @forelse ($items as $item)
            <div class="item-col">
                <div class="item-card">
                    {{-- 商品詳細画面へのリンク --}}
                    <a href="/items/{{ $item->id }}" class="item-link-wrapper"> 
                        {{-- 
                           $item->sold(): Itemモデル内のロジックで売却済みか判定
                           売却済みなら 'sold' クラスを付与し、CSSで「SOLD」ラベルを表示させる 
                        --}}
                        <div class="item-img-container @if ($item->sold()) sold @endif">
                            @if ($item->image_url)
                            <img src="{{ $item->image_url }}" class="item-img" alt="{{ $item->name }}">
                            @else
                            {{-- 画像がない場合は、public/images/no_image.pngを表示 --}}
                            <img src="{{ asset('images/no_image.png') }}" class="item-img" alt="No Image">
                            @endif
                        </div>
                    </a>    
                    <div class="item-body">
                        <p class="item-title">
                            {{ $item->name }}
                        </p>
                    </div>
                </div>
            </div>
            @empty
                {{-- 商品が1つも取得できなかった場合の表示 --}}
                <p style="text-align: center; width: 100%; margin-top: 50px;">
                    {{-- 条件分岐により、なぜ「何もない」のかを親切に伝える --}}
                    @if ($tab === 'mylist' && auth()->guest())
                        マイリストを表示するにはログインが必要です。
                    @elseif ($keyword)
                        「{{ $keyword }}」に一致する商品はありません。
                    @else
                        表示できる商品がありません。
                    @endif
                </p>
            @endforelse
        </div>

        {{-- 
           ページネーションのリンク（「前へ」「次へ」やページ番号）を表示 
           Controller側のpaginate()メソッドと連動 
        --}}
        <div class="pagination-links mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection