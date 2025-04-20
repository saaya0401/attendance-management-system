<div class="content">
    <h1 class="list-title">{{ $formattedSelectedDay }}</h1>]
    <div class="date-select">
        <button class="day-button" wire:click="$set('selectedDay', '{{ $prevDay }}')">
            <img src="{{ asset('icon/left.png') }}" alt="矢印" class="day-image">
            <span class="day-link">前日</span>
        </button>
        <div class="current-day">
            <input type="date" class="current-day__input" wire:model.live="selectedDay">
            <span class="current-day__text">
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $selectedDay)->format('Y/m/d') }}
            </span>
        </div>
        <button class="day-button" wire:click="$set('selectedDay', '{{ $nextDay }}')">
            <span class="day-link">翌日</span>
            <img src="{{ asset('icon/right.png') }}" alt="矢印" class="day-image">
        </button>
    </div>
    <table class="attendance-table">
        <tr class="attendance-table__title">
            <th class="attendance-table__header-name">名前</th>
            <th class="attendance-table__header">出勤</th>
            <th class="attendance-table__header">退勤</th>
            <th class="attendance-table__header">休憩</th>
            <th class="attendance-table__header">合計</th>
            <th class="attendance-table__header-detail">詳細</th>
        </tr>
        @foreach($attendanceLogs as $log)
        <tr class="attendance-table__row">
            <td class="attendance-table__name">{{ $log['name'] }}</td>
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
