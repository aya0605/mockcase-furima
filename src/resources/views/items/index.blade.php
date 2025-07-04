@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items/index.css') }}">
@endsection

@section('content')
<div class="main-container">
    <div class="menu-nav">
        <ul class="menu-list">
            <li class="menu-item"><a href="#">おすすめ</a></li>
            <li class="menu-item"><a href="#">マイリスト</a></li>
        </ul>
    </div>

    <div class="item-grid">
        <div class="items-list-wrapper">
            @foreach ($items as $item)
            <div class="item-col">
                <div class="item-card">
                    <a href="/items/{{ $item->id }}" class="item-link-wrapper">
                        @if ($item->image_url)
                        <img src="{{ asset($item->image_url) }}" class="card-img-top" alt="{{ $item->name }}">
                        @else
                        <img src="{{ asset('images/no_image.png') }}" class="card-img-top" alt="No Image">
                        @endif
                    </a>    
                    <div class="item-body">
                        <h5 class="item-title">{{ $item->name }}</h5>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="pagination-links mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

