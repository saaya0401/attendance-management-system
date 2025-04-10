<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/staff.css')}}">
    @yield('css')
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
                    <li class="header-nav__item">
                        <form action="{{route('attendance')}}" class="header-form" method="get">
                            <button class="header-nav__button">勤怠</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <form action="{{route('attendance.list')}}" class="header-form" method="get">
                            <button class="header-nav__button">勤怠一覧</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <form action="{{ auth()->user()->role === 'admin' ? route('admin.request.list') : route('request.list') }}" method="get">
                            <button class="header-nav__button">申請</button>
                        </form>
                    </li>
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
    <main class="main">
        @yield('content')
    </main>
</body>
</html>