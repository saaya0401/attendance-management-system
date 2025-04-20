<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/staff.css')}}">
    @yield('css')
    @livewireStyles
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <a href="/attendance" class="header-link">
                <img src="{{asset('icon/CTlogo.png')}}" alt="header-logo" class="header-logo">
                <img src="{{asset('icon/COACHTECH.png')}}" alt="header-logo" class="header-logo">
            </a>
            <nav>
                <ul class="header-nav">
                    @if(Auth::check())
                        @yield('nav')
                        <li class="header-nav__item">
                            <form class="header-form" action="/logout" method="post">
                            @csrf
                                <button class="header-nav__button">ログアウト</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>
    @if (session('message'))
    <div class="alert-success">
        {{ session('message') }}
    </div>
    @endif
    <main class="main">
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>