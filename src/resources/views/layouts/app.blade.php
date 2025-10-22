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
                    <form class="header-search-form" action="/" method="GET">
                        @if (Request::path() == '/')
                            <input type="hidden" name="tab" value="{{ Request::input('tab', 'recommend') }}">
                        @endif
                        
                        <input 
                            type="text" 
                            class="header-search-input" 
                            name="keyword" 
                            placeholder="なにをお探しですか？"
                            value="{{ Request::input('keyword') }}"
                        >
                        <button type="submit" style="display: none;"></button>
                    </form>
                    <nav>
                        <ul class="header-nav-list">
                            @if (Auth::check())
                                {{-- ログイン済みの場合 --}}
                                
                                {{-- ログアウト --}}
                                <li class="header-nav-item">
                                    <form class="form" action="/logout" method="post">
                                        @csrf
                                        <button class="header-nav__button">ログアウト</button>
                                    </form>
                                </li>
                                
                                {{-- マイページ --}}
                                <li class="header-nav-item"><a href="/user/profile">マイページ</a></li>
                                
                                {{-- 出品 --}}
                                <li class="header-nav-item"><a href="/sell" class="add-button">出品</a></li>
                            @else
                                {{-- 未ログインの場合 --}}
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
    @yield('scripts')
</body>

</html>