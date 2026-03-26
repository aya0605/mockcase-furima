{{-- 認証系画面で共通のレイアウト（layouts/auth.blade.php）を使用 --}}
@extends('layouts.auth')

{{-- このページ専用のCSS（register.css）を読み込み --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-form__content">
    <div class="register-form__heading">
        <h2>会員登録</h2>
    </div>

    {{-- 会員登録処理（通常はFortifyや独自のRegisterController）へPOST送信 --}}
    <form class="form" action="/register" method="post">
        {{-- CSRF保護：セキュリティのために必須のトークン --}}
        @csrf

        {{-- ユーザー名入力エリア --}}
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">ユーザー名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    {{-- old('name'): バリデーションエラー時に、入力した名前が消えないように保持 --}}
                    <input type="text" name="name" value="{{ old('name') }}" />
                </div>
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        {{-- メールアドレス入力エリア --}}
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">メールアドレス</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="email" name="email" value="{{ old('email') }}" />
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        {{-- パスワード入力エリア --}}
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">パスワード</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="password" name="password" />
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        {{-- 確認用パスワード入力エリア --}}
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">確認用パスワード</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    {{-- name属性は「password_confirmation」にするのがLaravelの標準ルール --}}
                    <input type="password" name="password_confirmation" />
                </div>
                <div class="form__error">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>
    </form>

    {{-- 既にアカウントを持っている人向けのログイン画面へのリンク --}}
    <div class="login__link">
        <a class="register__button-submit" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection