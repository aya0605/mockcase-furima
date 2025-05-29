<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH furima</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__inner">
                <div class="header-utilities">

                    <a href="/">
                        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
                    </a>
                    <form class="header-search-form">
                        <input type="text" class="header-search-input" placeholder="何をお探しですか？">
                        <button type="submit" class="header-search-button">検索</button>
                    </form>
                    <nav>
                        <ul class="header-nav-list">
                            @if (Auth::check())
                                <form class="form" action="/logout" method="post">
                                    @csrf
                                    <button class="header-nav__button">ログアウト</button>
                                </form>
                                <li class="header-nav-item"><a href="/profile">マイページ</a></li>
                                <li class="header-nav-item"><a href="/sell" class="add-button">出品</a></li>
                            @else
                                <li class="header-nav-item"><a href="/login">ログイン</a></li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>