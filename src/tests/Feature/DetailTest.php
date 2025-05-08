<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class DetailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp():void
    {
        parent::setUp();
        $this->seed();

        $this->user=User::where('email', 'saaya@example.com')->first();
        $this->assertNotNull($this->user);
        $this->actingAs($this->user);

        $this->date=Carbon::today()->toDateString();
        $this->clockIn=AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>$this->date,
            'time'=>'08:00:00'
        ]);
        AttendanceLog::insert([
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'break_in',
                'date'=>$this->date,
                'time'=>'12:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'break_out',
                'date'=>$this->date,
                'time'=>'13:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_out',
                'date'=>$this->date,
                'time'=>'17:00:00'
            ],
        ]);
    }

    public function testDetailName(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    public function testDetailDate(){
        $carbonDate=Carbon::parse($this->date);
        $formattedYear=$carbonDate->format('Y年');
        $formattedDate=$carbonDate->format('n月j日');

        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response->assertSee($formattedYear);
        $response->assertSee($formattedDate);
    }

    public function testDetailClockInOut(){
        $clockInTime=AttendanceLog::where('user_id', $this->user->id)->where('date', $this->date)->where('attendance_status', 'clock_in')->first();
        $formattedClockIn=Carbon::parse($clockInTime->time)->format('H:i');
        $clockOutTime=AttendanceLog::where('user_id', $this->user->id)->where('date', $this->date)->where('attendance_status', 'clock_out')->first();
        $formattedClockOut=Carbon::parse($clockOutTime->time)->format('H:i');

        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response->assertSee($formattedClockIn);
        $response->assertSee($formattedClockOut);
    }

    public function testDetailBreakInOut(){
        $breakInTime=AttendanceLog::where('user_id', $this->user->id)->where('date', $this->date)->where('attendance_status', 'break_in')->first();
        $formattedBreakIn=Carbon::parse($breakInTime->time)->format('H:i');
        $breakOutTime=AttendanceLog::where('user_id', $this->user->id)->where('date', $this->date)->where('attendance_status', 'break_out')->first();
        $formattedBreakOut=Carbon::parse($breakOutTime->time)->format('H:i');

        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response->assertSee($formattedBreakIn);
        $response->assertSee($formattedBreakOut);
    }
}
