@extends('layouts.staff')

@section('css')
<link rel="stylesheet" href="{{asset('css/attendance_detail.css')}}">
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
    <h1 class="detail-title">勤怠詳細</h1>
    <form action="" method="post"  class="detail-form">
        @csrf
        <table class="detail-table">
            <tr class="detail-table__row">
                <th class="detail-table__header">名前</th>
                <td class="detail-table__description">
                    <span class="detail-table__data">木場 紗彩</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">日付</th>
                <td class="detail-table__description">
                    <span class="detail-table__data">2025年</span>
                    <span class="detail-table__data">5月2日</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">出勤・退勤</th>
                <td class="detail-table__description">
                    <span class="detail-table__time">09:00</span>
                    <span class="detail-table__between">〜</span>
                    <span class="detail-table__time">18:00</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">休憩</th>
                <td class="detail-table__description">
                    <span class="detail-table__time">12:00</span>
                    <span class="detail-table__between">〜</span>
                    <span class="detail-table__time">13:00</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__description-comment">
                    <textarea name="comment" class="detail-textarea">電車遅延のため</textarea>
                </td>
            </tr>
        </table>
        <div class="detail-form__button">
            <button class="detail-form__button-submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection