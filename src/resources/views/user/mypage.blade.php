{{-- resources/views/user/mypage.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="mypage-container">
        <h1>{{ Auth::user()->name }}さんのマイページ</h1>

        <div class="mypage-sections">
            <div class="mypage-section">
                <h2>プロフィール情報</h2>
                <p>ユーザー名: {{ Auth::user()->name }}</p>
                <p>メールアドレス: {{ Auth::user()->email }}</p>
                @if (Auth::user()->profile_image_path)
                    <img src="{{ Storage::url(Auth::user()->profile_image_path) }}" alt="プロフィール画像" class="mypage-profile-image">
                @else
                    <img src="{{ asset('images/default_profile.png') }}" alt="デフォルト画像" class="mypage-profile-image">
                @endif
                {{-- ★ここにプロフィール編集へのリンクを追加★ --}}
                <a href="/user/profile/edit" class="btn btn-primary">プロフィールを編集する</a>
            </div>

            <div class="mypage-section">
                <h2>購入履歴</h2>
                {{-- 購入履歴の表示など --}}
                <p>まだ購入履歴はありません。</p>
            </div>

            <div class="mypage-section">
                <h2>出品履歴</h2>
                {{-- 出品履歴の表示など --}}
                <p>まだ出品履歴はありません。</p>
            </div>
        </div>
    </div>
@endsection