{{-- レイアウトファイル（共通のヘッダーや枠組み）を継承 --}}
@extends('layouts.auth')

{{-- このページ専用のCSSを読み込むためのセクション --}}
@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
  <div class="login-form__heading">
    <h2>ログイン</h2>
  </div>

  {{-- ログイン処理を行うルート（通常は /login）へPOST送信 --}}
  <form class="form" action="/login" method="post">
    {{-- CSRF保護：なりすまし攻撃を防ぐための必須トークン --}}
    @csrf

    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">メールアドレス</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          {{-- old('email') : バリデーションエラーで戻ってきた際、入力した値を再表示する --}}
          <input type="email" name="email" value="{{ old('email') }}" />
        </div>
        {{-- メールアドレスに関するエラーがあれば表示 --}}
        <div class="form__error">
          @error('email')
          {{ $message }}
          @enderror
        </div>
      </div>
    </div>

    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">パスワード</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          {{-- パスワードはセキュリティ上、old関数で復元させないのが一般的 --}}
          <input type="password" name="password" />
        </div>
        {{-- パスワードに関するエラー（未入力など）があれば表示 --}}
        <div class="form__error">
          @error('password')
          {{ $message }}
          @enderror
        </div>
      </div>
    </div>

    {{-- 認証失敗（メールかパスワードが違うなど）の全体エラーを表示 --}}
    <div class="form__error form__error--auth">
        @error('auth')
        {{ $message }}
        @enderror
    </div>

    <div class="form__button">
      <button class="form__button-submit" type="submit">ログインする</button>
    </div>
  </form>

  {{-- 新規登録画面へのリンク --}}
  <div class="register__link">
    <a class="register__button-submit" href="/register">会員登録はこちら</a>
  </div>
</div>
@endsection