<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AdminDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($this->admin);
        $this->actingAs($this->admin);

        $this->user=User::where('email', 'saaya@example.com')->first();
        $this->date=Carbon::today()->toDateString();
        $this->clockIn=AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>$this->date,
            'time'=>'08:00:00'
        ]);
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_out',
            'date'=>$this->date,
            'time'=>'17:00:00'
        ]);
    }

    public function testAdminDetail(){
        $carbonDate=Carbon::today();
        $formattedYear=$carbonDate->year . '年';
        $formattedDate=$carbonDate->month . '月' . $carbonDate->day . '日';

        $formattedClockIn=Carbon::parse($this->clockIn->time)->format('H:i');
        $clockOut=AttendanceLog::where('user_id', $this->user->id)->where('date', $this->date)->where('attendance_status', 'clock_out')->first();
        $formattedClockOut=Carbon::parse($clockOut->time)->format('H:i');

        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($formattedYear);
        $response->assertSee($formattedDate);
        $response->assertSee($formattedClockIn);
        $response->assertSee($formattedClockOut);
    }

    public function testAdminUpdateErrorClockInOut(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->patch('/admin/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'18:00',
            'clock_out'=>'17:00',
            'comment'=>'comment',
            '_token'=>csrf_token(),
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'clock_out'=>'出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function testAdminUpdateErrorBreakIn(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->patch('/admin/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'08:00',
            'clock_out'=>'17:00',
            'break_in'=>['19:00'],
            'break_out'=>['20:00'],
            'comment'=>'comment',
            '_token'=>csrf_token(),
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'break_in.0'=>'休憩時間が勤務時間外です'
        ]);
    }

    public function testAdminUpdateErrorBreakOut(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->patch('/admin/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'08:00',
            'clock_out'=>'17:00',
            'break_in'=>['11:00'],
            'break_out'=>['18:00'],
            'comment'=>'comment',
            '_token'=>csrf_token(),
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'break_in.0'=>'休憩時間が勤務時間外です'
        ]);
    }

    public function testAdminUpdateErrorComment(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->patch('/admin/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'08:00',
            'clock_out'=>'17:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>'',
            '_token'=>csrf_token(),
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'comment'=>'備考を記入してください'
        ]);
    }
}
