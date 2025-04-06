<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">
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
                    @yield('nav')
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