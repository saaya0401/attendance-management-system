@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance.css')}}">
@endsection

@section('content')
@if (session('message'))
    <div class="alert-success">
        {{ session('message') }}
    </div>
@endif
<div class="content">
    <h6 class="attendance-status">勤務外</h6>
    <h3 class="attendance-date">2023年6月1日(木)</h3>
    <h1 class="attendance-time">08:00</h1>
    <form action="{{route('attendance')}}" class="attendance-form" method="post">
        @csrf
        <button class="attendance-button" type="submit">出勤</button>
    </form>
</div>
@endsection