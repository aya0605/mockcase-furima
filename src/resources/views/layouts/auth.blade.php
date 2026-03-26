<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH furima</title>
    {{-- ブラウザ間の表示崩れを防ぐリセットCSS --}}
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    {{-- 共通の基本スタイル --}}
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    {{-- 各認証ページ専用のCSSをここに差し込む --}}
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__inner">
                <div class="header-utilities">
                    {{-- 
                        ロゴのみを表示。
                        認証画面では他のリンクを減らし、
                        離脱率を下げる工夫がなされています。
                    --}}
                    <a href="/">
                        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
                    </a>
                </div>
            </div>
        </header>
        
        {{-- 各認証ページ（ログインや会員登録）のフォーム本体がここに入ります --}}
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>