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
<div class="content">
    <h1 class="list-title">勤怠一覧</h1>
<div class="date-select">
    <div class="before-month__link">
        <img src="{{asset('icon/left.png')}}" alt="矢印" class="before-month__image">
        <p class="before-month">前月</p>
    </div>
    <div class="current-month">2023/06</div>
    <div class="before-month__link">
        <p class="before-month">翌月</p>
        <img src="{{asset('icon/right.png')}}" alt="矢印" class="before-month__image">
    </div>
</div>
<table class="attendance-table">
    <tr class="attendance-table__title">
        <th class="attendance-table__header-date">日付</th>
        <th class="attendance-table__header">出勤</th>
        <th class="attendance-table__header">退勤</th>
        <th class="attendance-table__header">休憩</th>
        <th class="attendance-table__header">合計</th>
        <th class="attendance-table__header-detail">詳細</th>
    </tr>
    <tr class="attendance-table__row">
        <td class="attendance-table__date">08/01(木)</td>
        <td class="attendance-table__data">09:00</td>
        <td class="attendance-table__data">18:00</td>
        <td class="attendance-table__data">1:00</td>
        <td class="attendance-table__data">8:00</td>
        <td class="attendance-table__detail">
            <a href="" class="attendance-table__detail-link">詳細</a>
        </td>
    </tr>
    <tr class="attendance-table__row">
        <td class="attendance-table__date">08/01(木)</td>
        <td class="attendance-table__data">09:00</td>
        <td class="attendance-table__data">18:00</td>
        <td class="attendance-table__data">1:00</td>
        <td class="attendance-table__data">8:00</td>
        <td class="attendance-table__detail">
            <a href="" class="attendance-table__detail-link">詳細</a>
        </td>
    </tr>
    <tr class="attendance-table__row">
        <td class="attendance-table__date">08/01(木)</td>
        <td class="attendance-table__data">09:00</td>
        <td class="attendance-table__data">18:00</td>
        <td class="attendance-table__data">1:00</td>
        <td class="attendance-table__data">8:00</td>
        <td class="attendance-table__detail">
            <a href="" class="attendance-table__detail-link">詳細</a>
        </td>
    </tr>
    <tr class="attendance-table__row">
        <td class="attendance-table__date">08/01(木)</td>
        <td class="attendance-table__data">09:00</td>
        <td class="attendance-table__data">18:00</td>
        <td class="attendance-table__data">1:00</td>
        <td class="attendance-table__data">8:00</td>
        <td class="attendance-table__detail">
            <a href="" class="attendance-table__detail-link">詳細</a>
        </td>
    </tr>
    <tr class="attendance-table__row">
        <td class="attendance-table__date">08/01(木)</td>
        <td class="attendance-table__data">09:00</td>
        <td class="attendance-table__data">18:00</td>
        <td class="attendance-table__data">1:00</td>
        <td class="attendance-table__data">8:00</td>
        <td class="attendance-table__detail">
            <a href="" class="attendance-table__detail-link">詳細</a>
        </td>
    </tr>
</table>
</div>
@endsection