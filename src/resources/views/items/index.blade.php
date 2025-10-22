@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

{{-- タブナビゲーション --}}
<div class="main-container">
    <div class="menu-nav">
        <ul class="menu-list">
            {{-- おすすめタブ --}}
            <li class="menu-item @if ($tab === 'recommend') active @endif">
                {{-- route()呼び出しをURL文字列に戻しました --}}
                <a href="/?tab=recommend&keyword={{ $keyword }}">おすすめ</a>
            </li>
            
            {{-- マイリストタブ: 常に表示するように修正 --}}
            <li class="menu-item @if ($tab === 'mylist') active @endif">
                {{-- route()呼び出しをURL文字列に戻しました --}}
                <a href="/?tab=mylist&keyword={{ $keyword }}">マイリスト</a>
            </li>
            
        </ul>
    </div>

    <div class="item-grid">
        <div class="items-list-wrapper">
            @forelse ($items as $item)
            <div class="item-col">
                <div class="item-card">
                    {{-- 商品詳細ページへのリンク --}}
                    <a href="/items/{{ $item->id }}" class="item-link-wrapper"> 
                        
                        {{-- SOLD表示のために 'sold' クラスを付与 --}}
                        <div class="item-img-container @if ($item->sold()) sold @endif">
                            {{-- ★ここを修正しました: \Storage::url() を削除し、直接 $item->image_url を使用★ --}}
                            @if ($item->image_url)
                            <img src="{{ $item->image_url }}" class="item-img" alt="{{ $item->name }}">
                            @else
                            <img src="{{ asset('images/no_image.png') }}" class="item-img" alt="No Image">
                            @endif
                        </div>
                        
                    </a>    
                    <div class="item-body">
                        {{-- 商品名のみの表示 --}}
                        <p class="item-title">
                            {{ $item->name }}
                        </p>
                        
                    </div>
                </div>
            </div>
            @empty
                <p style="text-align: center; width: 100%; margin-top: 50px;">
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

        <div class="pagination-links mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection