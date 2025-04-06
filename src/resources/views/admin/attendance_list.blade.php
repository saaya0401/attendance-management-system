@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance_list.css')}}">
@endsection

@section('nav')
<li class="header-nav__item">
    <form class="header-form" action="/admin/logout" method="post">
    @csrf
        <button class="header-nav__button">ログアウト</button>
    </form>
</li>
@endsection

@section('content')

@endsection