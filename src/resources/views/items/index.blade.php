@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="main-container">
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

    <div class="item-grid">
        <div class="items-list-wrapper">
            @forelse ($items as $item)
            <div class="item-col">
                <div class="item-card">
                    <a href="/items/{{ $item->id }}" class="item-link-wrapper"> 
                        <div class="item-img-container @if ($item->sold()) sold @endif">
                            @if ($item->image_url)
                            <img src="{{ $item->image_url }}" class="item-img" alt="{{ $item->name }}">
                            @else
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