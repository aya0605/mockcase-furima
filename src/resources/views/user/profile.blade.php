@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/items/index.css') }}">
@endsection

@section('content')
    <div class="mypage-container">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="profile-summary">
            <div class="profile-info">
                <div class="profile-image-display">
                    @if (Auth::user()->profile_image_path)
                        <img src="{{ Storage::url(Auth::user()->profile_image_path) }}" alt="プロフィール画像" class="profile-img">
                    @else
                        <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="profile-img">
                    @endif
                </div>
                <p class="profile-name">{{ Auth::user()->name }}</p>
            </div>
            <a href="/user/profile/edit" class="btn btn-primary profile-edit-link">プロフィールを編集</a>
        </div>

        <div class="mypage-sections">
            <div class="mypage-section sold-items-section">
                <h2>出品した商品</h2>
                @if ($soldItems->isEmpty())
                    <p>まだ出品した商品はありません。</p>
                @else
                    <div class="items-list-wrapper">
                        @foreach ($soldItems as $item)
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
                        {{ $soldItems->links() }}
                    </div>
                @endif
            </div>

            <div class="mypage-section purchased-items-section">
                <h2>購入した商品</h2>
                @if ($purchasedItems->isEmpty())
                    
                @else
                    <div class="items-list-wrapper">
                        @foreach ($purchasedItems as $purchase)
                        <div class="item-col">
                            <div class="item-card">
                                <a href="/items/{{ $purchase->item->id }}" class="item-link-wrapper">
                                    @if ($purchase->item->image_url)
                                    <img src="{{ asset($purchase->item->image_url) }}" class="card-img-top" alt="{{ $purchase->item->name }}">
                                    @else
                                    <img src="{{ asset('images/no_image.png') }}" class="card-img-top" alt="No Image">
                                    @endif
                                </a>
                                <div class="item-body">
                                    <h5 class="item-title">{{ $purchase->item->name }}</h5>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="pagination-links mt-4">
                        {{ $purchasedItems->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection