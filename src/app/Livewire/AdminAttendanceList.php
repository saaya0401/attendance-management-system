<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceLog;
use Livewire\Component;

class AdminAttendanceList extends Component
{
    public $selectedDay;

    public function mount()
    {
        $this->selectedDay=Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        $selectedDay=$this->selectedDay;
        $selectedDate=Carbon::parse($selectedDay);
        $formattedSelectedDay=$selectedDate->year . '年' . $selectedDate->month . '月' . $selectedDate->day . '日の勤怠';
        $users=User::where('role', 'staff')->get();
        $rawLogs=AttendanceLog::where('date', $selectedDay)->get();
        $results=collect();

        foreach($users as $user){
            $userDayLogs=$rawLogs->where('user_id', $user->id);
            $clockIn=$userDayLogs->firstWhere('attendance_status', 'clock_in');
            $clockOut=$userDayLogs->firstWhere('attendance_status', 'clock_out');

            $breaks=$userDayLogs->filter(fn($log)=>in_array($log->attendance_status, ['break_out', 'break_in']))->values();
            $totalBreak=0;

            for($i = 0; $i<$breaks->count(); $i += 2){
                if(isset($breaks[$i + 1])){
                    $start=Carbon::parse($breaks[$i]->time);
                    $end=Carbon::parse($breaks[$i + 1]->time);
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
            if($total != null){
                $totalHours=floor($total / 60);
                $totalMinutes=$total % 60;
                $totalDisplay=str_pad($totalHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($totalMinutes, 2, '0', STR_PAD_LEFT);
            }

            $results->push([
                'name'=>$user->name,
                'id'=>$clockIn?->id,
                'clock_in'=>$clockIn?->time ? Carbon::parse($clockIn->time)->format('H:i') : '',
                'clock_out'=>$clockOut?->time ? Carbon::parse($clockOut->time)->format('H:i') : '',
                'break'=>$breaks->isNotEmpty() ? $breakDisplay : '',
                'total'=>$totalDisplay,
            ]);
        }
        $current=Carbon::createFromFormat('Y-m-d', $this->selectedDay);
        $prevDay=$current->copy()->subDay()->format('Y-m-d');
        $nextDay=$current->copy()->addDay()->format('Y-m-d');

        return view('livewire.admin-attendance-list', [
            'attendanceLogs'=>$results,
            'prevDay'=>$prevDay,
            'nextDay'=>$nextDay,
            'formattedSelectedDay'=>$formattedSelectedDay
        ]);
    }
}
