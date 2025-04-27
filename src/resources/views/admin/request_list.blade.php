@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="list-title">申請一覧</h1>
    <div class="tab-area">
        <div class="tab-buttons">
            <a href="{{ route('request.list')}}" class="tab-button {{$tab !== 'approved' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('request.list',  ['tab'=>'approved']) }}" class="tab-button {{$tab === 'approved' ? 'active' : '' }}">承認済み</a>
        </div>
    </div>
    <table class="request-table">
        <tr class="request-table__title">
            <th class="request-table__header-status">状態</th>
            <th class="request-table__header-name">名前</th>
            <th class="request-table__header-date">対象日時</th>
            <th class="request-table__header-reason">申請理由</th>
            <th class="request-table__header-date">申請日時
            </th>
            <th class="request-table__header-detail">詳細</th>
        </tr>
        @foreach($attendanceRequests as $attendanceRequest)
        <tr class="request-table__row">
            <td class="request-table__status">{{ $attendanceRequest->approval_status === 'pending' ? '承認待ち' : '承認済み' }}</td>
            <td class="request-table__data">{{ $attendanceRequest->user->name }}</td>
            <td class="request-table__data">{{ \Carbon\Carbon::parse($attendanceRequest->date)->format('Y/m/d') }}</td>
            <td class="request-table__data">{{ $attendanceRequest->comment }}</td>
            <td class="request-table__data">{{ \Carbon\Carbon::parse($attendanceRequest->created_at)->format('Y/m/d') }}</td>
            <td class="request-table__detail">
                <a href="{{ route('correction.approve', ['attendance_correct_request' => $attendanceRequest->attendance_request_id]) }}" class="request-table__detail-link">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
    <div class="footer-space"></div>
</div>
@endsection