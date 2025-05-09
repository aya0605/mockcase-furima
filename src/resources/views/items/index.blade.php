@extends('layouts.app')

@section('content')
<div class="main-container">
    <div class="menu-nav">
        <ul class="menu-list">
            <li class="menu-item"><a href="#">おすすめ</a></li>
            <li class="menu-item"><a href="#">マイリスト</a></li>
        </ul>
    </div>

    <div class="item-grid">
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach ($items as $item)
            <div class="col">
                <div class="item-card">
                    @if ($item->image_url)
                    <img src="{{ $item->image_url }}" class="card-img-top" alt="{{ $item->name }}">
                    @else
                    <img src="{{ asset('images/no_image.png') }}" class="card-img-top" alt="No Image">
                    @endif
                    <div class="item-body">
                        <h5 class="item-title">{{ $item->name }}</h5>
                    </div>
                </div>
            </div>
            @empty
            <p>商品はまだありません。</p>
            @endforelse
        </div>

        <div class="pagination-links mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/items/index.css') }}">
@endsection
