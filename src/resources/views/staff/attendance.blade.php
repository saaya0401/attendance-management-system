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
<form class="attendance-form" method="post">
    @csrf
    <h6 class="attendance-status">{{$attendanceStatus}}</h6>
    <h3 class="attendance-date">{{$attendanceDate}}</h3>
    <input type="hidden" name="date" value="{{ \Carbon\Carbon::today()->toDateString() }}">
    <h1 class="attendance-time">{{$attendanceTime}}</h1>
    <input type="hidden" name="time" value="{{\Carbon\Carbon::now()->format('H:i:s')}}">
    <div class="attendance-button__group">
        @if($attendanceStatus === '勤務外')
        <button class="attendance-button" type="submit" formaction="{{route('clock.in')}}" name="attendance_status" value="clock_in">出勤</button>
        @elseif($attendanceStatus === '出勤中')
        <button class="attendance-button" type="submit" formaction="{{route('clock.out')}}" name="attendance_status" value="clock_out">退勤</button>
        <button class="attendance-button__break" type="submit" formaction="{{route('break.in')}}" name="attendance_status" value="break_in">休憩入</button>
        @elseif($attendanceStatus === '休憩中')
        <button class="attendance-button" type="submit" formaction="{{route('break.out')}}" name="attendance_status" value="break_out">休憩戻</button>
        @elseif($attendanceStatus === '退勤済')
        <p class="attendance-text">お疲れ様でした。</p>
        @endif
    </div>
</form>
@endsection