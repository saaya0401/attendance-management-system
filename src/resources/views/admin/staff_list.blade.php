@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_list.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="list-title">スタッフ一覧</h1>
    <table class="staff-table">
        <tr class="staff-table__title">
            <th class="staff-table__space"></th>
            <th class="staff-table__header-name">名前</th>
            <th class="staff-table__header-mail">メールアドレス</th>
            <th class="staff-table__header-detail">月次勤怠</th>
            <th class="staff-table__space"></th>
        </tr>
        @foreach($users as $user)
        <tr class="staff-table__row">
            <td class="staff-table__space"></td>
            <td class="staff-table__name">{{ $user->name }}</td>
            <td class="staff-table__email">{{ $user->email }}</td>
            <td class="staff-table__detail">
                <a href="{{ route('staff.attendance', ['id'=>$user->id]) }}" class="staff-table__link">詳細</a>
            </td>
            <td class="staff-table__space"></td>
        </tr>
        @endforeach
    </table>
</div>
@endsection