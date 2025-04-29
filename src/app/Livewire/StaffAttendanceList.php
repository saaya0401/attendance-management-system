<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceLog;

class StaffAttendanceList extends Component
{
    public $selectedMonth;
    public User $user;

    public function mount(User $user)
    {
        $this->user=$user;
        $this->selectedMonth =Carbon::now()->format('Y-m');
    }

    public function render()
    {
        $selectedMonth=$this->selectedMonth;
        $startOfMonth=Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $endOfMonth=Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();

        $rawLogs=AttendanceLog::where('user_id', $this->user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->groupBy('date');

        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $results=collect();

        for($date=$startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()){
            $carbonDate=$date->copy();
            $formattedDate=$carbonDate->format('Y-m-d');
            $dayLogs=$rawLogs->get($formattedDate, collect());
            $clockIn=$dayLogs->firstWhere('attendance_status', 'clock_in');
            $clockOut=$dayLogs->firstWhere('attendance_status', 'clock_out');

            $breaks=$dayLogs->filter(fn($log)=>in_array($log->attendance_status, ['break_out', 'break_in']))->values();
            $totalBreak=0;

            for($i=0; $i<$breaks->count(); $i+=2){
                if(isset($breaks[$i+1])){
                    $start=Carbon::parse($breaks[$i]->time);
                    $end=Carbon::parse($breaks[$i+1]->time);
                    $totalBreak+=$start->diffInMinutes($end);
                }
            }

            $total=null;
            if($clockIn && $clockOut){
                $start=Carbon::parse($clockIn->time);
                $end=Carbon::parse($clockOut->time);
                $total=$start->diffInMinutes($end) - $totalBreak;
            }

            $breakHours=floor($totalBreak / 60);
            $breakMinutes=$totalBreak % 60;
            $breakDisplay=str_pad($breakHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($breakMinutes, 2, '0', STR_PAD_LEFT);

            $totalDisplay='';
            if($total !== null){
                $totalHours=floor($total / 60);
                $totalMinutes=$total % 60;
                $totalDisplay=str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($totalMinutes, 2, '0', STR_PAD_LEFT);
            }

            $results->push([
                'date'=>$carbonDate->format('m/d') . '(' . $weekdays[$carbonDate->dayOfWeek] . ')',
                'id'=>$clockIn?->id,
                'clock_in'=>$clockIn?->time ? Carbon::parse($clockIn->time)->format('H:i') : '',
                'clock_out'=>$clockOut?->time ? Carbon::parse($clockOut->time)->format('H:i') : '',
                'break'=>$breaks->isNotEmpty() ? $breakDisplay : '',
                'total'=>$totalDisplay,
            ]);
        }

        $current=Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $prevMonth=$current->copy()->subMonth()->format('Y-m');
        $nextMonth=$current->copy()->addMonth()->format('Y-m');
        return view('livewire.staff-attendance-list', [
            'attendanceLogs'=>$results,
            'prevMonth'=>$prevMonth,
            'nextMonth'=>$nextMonth,
            'user'=>$this->user
        ]);
    }
}
