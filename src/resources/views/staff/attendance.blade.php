@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance.css')}}">
@endsection

@section('nav')
<li class="header-nav__item">
    <form class="header-form" action="/logout" method="post">
    @csrf
        <button class="header-nav__button">ログアウト</button>
    </form>
</li>
@endsection

@section('content')
@if (session('message'))
    <div class="alert-success">
        {{ session('message') }}
    </div>
@endif
@endsection