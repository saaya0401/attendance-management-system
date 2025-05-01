<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user=User::where('email', 'saaya@example.com')->first();
        $this->assertNotNull($this->user);
        $this->actingAs($this->user);
    }

    public function testOffDutyStatus(){
        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function testClockInStatus(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00',
        ]);
        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function testBreakInStatus(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function testClockOutStatus(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'18:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
