@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance_list.css')}}">
@endsection

@section('nav')
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
@endsection

@section('content')
    @livewire('attendance-list')
@endsection