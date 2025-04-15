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
    <form action="{{ route('detail.edit', ['id' => $clockInLog->id]) }}" method="post"  class="detail-form">
        @csrf
        <table class="detail-table">
            <tr class="detail-table__row">
                <th class="detail-table__header">名前</th>
                <td class="detail-table__description">
                    <span class="detail-table__data">{{ $clockInLog->user->name }}</span>
                </td>
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">日付</th>
                <td class="detail-table__description">
                    <div class="detail-table__input-area">
                        <span class="detail-table__data">{{ $formattedYear }}</span>
                        <span class="detail-table__data">{{ $formattedDate }}</span>
                    </div>
                </td>
                <input type="hidden" name="date" value="{{ $date }}">
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">出勤・退勤</th>
                <td class="detail-table__description">
                    <div class="detail-table__input-area">
                        <input type="time" name="clock_in" value="{{ $clockInTime }}" placeholder="{{ $clockInTime }}" class="detail-table__time {{ $hasPendingRequest ? 'detail-table__readonly' : '' }}" {{ $hasPendingRequest ? 'readonly' : '' }}>
                        <span class="detail-table__between">〜</span>
                        <input type="time" name="clock_out" value="{{ $clockOutTime }}" placeholder="{{ $clockOutTime }}" class="detail-table__time {{ $hasPendingRequest ? 'detail-table__readonly' : '' }}" {{ $hasPendingRequest ? 'readonly' : '' }}>
                    </div>
                    @error('clock_in')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @for($i = 0; $i < count($breaks) + 1; $i++)
            <tr class="detail-table__row">
                <th class="detail-table__header">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                @if($hasPendingRequest && $i === count($breaks))
                <td class="detail-table__description">
                    <div class="detail-table__input-area"></div>
                </td>
                @else
                <td class="detail-table__description">
                    <div class="detail-table__input-area">
                        <input type="time" name="break_in[]" value="{{ old('break_in.' . $i, $breaks[$i]['start'] ?? '') }}"  class="detail-table__time {{ $hasPendingRequest ? 'detail-table__readonly' : '' }}" {{ $hasPendingRequest ? 'readonly' : '' }}>
                        <span class="detail-table__between">〜</span>
                        <input type="time" name="break_out[]" value="{{ old('break_out.' . $i, $breaks[$i]['end'] ?? '') }}" class="detail-table__time {{ $hasPendingRequest ? 'detail-table__readonly' : '' }}" {{ $hasPendingRequest ? 'readonly' : '' }}>
                    </div>
                    @error("break_in.$i")
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                    @error("break_out.$i")
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </td>
                @endif
            </tr>
            @endfor
            <tr class="detail-table__row">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__description-comment">
                    <textarea name="comment" class="detail-textarea {{ $hasPendingRequest ? 'detail-table__readonly' : '' }}"  {{ $hasPendingRequest ? 'readonly' : '' }}>{{ old('comment', $attendanceRequest->comment ?? '') }}</textarea>
                    @error('comment')
                    <div class="form-error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>
        @if(!$hasPendingRequest)
        <div class="detail-form__button">
            <button class="detail-form__button-submit" type="submit">修正</button>
        </div>
        @else
        <div class="detail-notion">
            <p class="detail__pending">*承認待ちのため修正はできません。</p>
        </div>
        @endif
    </form>
    <div class="footer-area"></div>
</div>
@endsection