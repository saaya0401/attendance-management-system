<div class="content">
    <h1 class="list-title">勤怠一覧</h1>
    <div class="date-select">
        <button class="month-button" wire:click="$set('selectedMonth', '{{ $prevMonth }}')">
            <img src="{{ asset('icon/left.png' )}}" alt="矢印" class="month-image">
            <span class="month-link">前月</span>
        </button>
        <div class="current-month">
            <input type="month" class="current-month__input" wire:model.live="selectedMonth">
            <span class="current-month__text">
                {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('Y/m') }}
            </span>
        </div>
        <button class="month-button" wire:click="$set('selectedMonth', '{{ $nextMonth}}')">
            <span class="month-link">翌月</span>
            <img src="{{ asset('icon/right.png') }}" alt="矢印" class="month-image">
        </button>
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
        @foreach($attendanceLogs as $log)
        <tr class="attendance-table__row">
            <td class="attendance-table__date">{{ $log['date'] }}</td>
            <td class="attendance-table__data">{{ $log['clock_in'] }}</td>
            <td class="attendance-table__data">{{ $log['clock_out'] }}</td>
            <td class="attendance-table__data">{{ $log['break'] }}</td>
            <td class="attendance-table__data">{{ $log['total'] }}</td>
            <td class="attendance-table__detail">
                @if($log['clock_in'])
                <a href="{{ route('detail', ['id'=>$log['id']]) }}" class="attendance-table__detail-link">詳細</a>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    <div class="footer-space"></div>
</div>
