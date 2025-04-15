@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance.css')}}">
@endsection

@section('nav')
    @if($attendanceStatus === '退勤済')
    <li class="header-nav__item">
        <form action="{{route('attendance.list')}}" class="header-form" method="get">
            <button class="header-nav__button">今月の出勤一覧</button>
        </form>
    </li>
    <li class="header-nav__item">
        <form action="{{ route('request.list') }}" method="get">
            <button class="header-nav__button">申請一覧</button>
        </form>
    </li>
    @else
    <li class="header-nav__item">
        <form action="{{route('attendance')}}" class="header-form" method="get">
            <button class="header-nav__button">勤怠</button>
        </form>
    </li>
    <li class="header-nav__item">
        <form action="{{route('attendance.list')}}"     class="header-form" method="get">
            <button class="header-nav__button">勤怠一覧</button>
        </form>
    </li>
    <li class="header-nav__item">
        <form action="{{ route('request.list') }}" method="get">
            <button class="header-nav__button">申請</button>
        </form>
    </li>
    @endif
@endsection
@section('content')
<form class="attendance-form" method="post">
    @csrf
    <h6 class="attendance-status">{{$attendanceStatus}}</h6>
    <h3 class="attendance-date">{{$attendanceDate}}</h3>
    <input type="hidden" name="date" value="{{ \Carbon\Carbon::today()->toDateString() }}">
    <h1 class="attendance-time">{{$attendanceTime}}</h1>
    <input type="hidden" name="time" value="{{\Carbon\Carbon::now()->format('H:i:s')}}">
    <div class="attendance-button__group">
        @if($attendanceStatus === '勤務外')
        <button class="attendance-button" type="submit" formaction="{{route('attendance')}}" name="attendance_status" value="clock_in">出勤</button>
        @elseif($attendanceStatus === '出勤中')
        <button class="attendance-button__clock-out" type="submit" formaction="{{route('attendance')}}" name="attendance_status" value="clock_out">退勤</button>
        <button class="attendance-button__break-in" type="submit" formaction="{{route('attendance')}}" name="attendance_status" value="break_in">休憩入</button>
        @elseif($attendanceStatus === '休憩中')
        <button class="attendance-button__break-out" type="submit" formaction="{{route('attendance')}}" name="attendance_status" value="break_out">休憩戻</button>
        @elseif($attendanceStatus === '退勤済')
        <p class="attendance-text">お疲れ様でした。</p>
        @endif
    </div>
</form>
@endsection