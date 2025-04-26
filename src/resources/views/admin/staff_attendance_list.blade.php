@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_attendance_list.css') }}">
@endsection

@section('content')
    @livewire('staff-attendance-list', ['user'=>$user])
@endsection