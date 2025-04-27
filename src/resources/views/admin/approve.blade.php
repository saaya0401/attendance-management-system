@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{asset('css/approve.css')}}">
@endsection

@section('content')
<div class="content">
    <h1 class="detail-title">勤怠詳細</h1>
    <form class="detail-form" method="post">
        @csrf
        @method('patch')
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
            </tr>
            <tr class="detail-table__row">
                <th class="detail-table__header">出勤・退勤</th>
                <td class="detail-table__description">
                    <div class="detail-table__input-area">
                        <span class="detail-table__time">{{ $clockInTime }}</span>
                        <span class="detail-table__between">〜</span>
                        <span class="detail-table__time">{{ $clockOutTime }}</span>
                    </div>
                </td>
            </tr>
            @for($i = 0; $i < count($breaks) + 1; $i++)
            <tr class="detail-table__row">
                <th class="detail-table__header">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                @if($i === count($breaks))
                <td class="detail-table__description">
                    <div class="detail-table__input-area"></div>
                </td>
                @else
                <td class="detail-table__description">
                    <div class="detail-table__input-area">
                        <span class="detail-table__time">{{ old('break_in.' . $i, $breaks[$i]['start'] ?? '') }}</span>
                        <span class="detail-table__between">〜</span>
                        <span class="detail-table__time">{{ old('break_out.' . $i, $breaks[$i]['end'] ?? '') }}</span>
                    </div>
                </td>
                @endif
            </tr>
            @endfor
            <tr class="detail-table__row">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__description-comment">
                    <span class="detail-textarea">{{ $attendanceRequest->comment }}</span>
                </td>
            </tr>
        </table>
        <div class="detail-form__button">
            <input type="hidden" name="attendance_request_id" value="{{ $attendanceRequest->id }}">
            @if($attendanceRequest->approval_status === 'pending')
            <button formaction="{{ route('correction.approve', ['attendance_correct_request' => $attendanceRequest->id]) }}" class="detail-form__button-submit" type="submit">承認</button>
            @elseif($attendanceRequest->approval_status === 'approved')
            <span class="detail-form__approved">承認済み</span>
            @endif
        </div>
    </form>
    <div class="footer-area"></div>
</div>
@endsection