@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{asset('css/admin_attendance_list.css')}}">
@endsection

@section('content')
    @livewire('admin-attendance-list')
@endsection