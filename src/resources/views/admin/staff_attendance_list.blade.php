@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_attendance_list') }}">
@endsection

@section('content')
    @livewire('admin-attendance-list')
@endsection