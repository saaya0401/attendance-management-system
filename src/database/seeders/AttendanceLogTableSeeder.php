<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceLogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users=User::where('role', 'staff')->get();

        foreach($users as $user){
            $startTime=Carbon::create('2025', '04', '07', '09', '00');

            AttendanceLog::create([
                'user_id'=>$user->id,
                'attendance_status'=>'clock_in',
                'date'=>$startTime->toDateString(),
                'time'=>$startTime->toTimeString()
            ]);

            $breakStart=$startTime->copy()->addHours(4);
            AttendanceLog::create([
                'user_id'=>$user->id,
                'attendance_status'=>'break_in',
                'date'=>$breakStart->toDateString(),
                'time'=>$breakStart->toTimeString()
            ]);

            $breakEnd=$startTime->copy()->addHours(5);
            AttendanceLog::create([
                'user_id'=>$user->id,
                'attendance_status'=>'break_out',
                'date'=>$breakEnd->toDateString(),
                'time'=>$breakEnd->toTimeString()
            ]);

            $endTime=$startTime->copy()->addHours(9);
            AttendanceLog::create([
                'user_id'=>$user->id,
                'attendance_status'=>'clock_out',
                'date'=>$endTime->toDateString(),
                'time'=>$endTime->toTimeString()
            ]);
        }
    }
}
