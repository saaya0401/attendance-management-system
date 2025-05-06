<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
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

    public function testBreakInButton(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $this->assertTrue(
            preg_match('/<button[^>]*>休憩入<\/button>/u', $response->getContent()) === 1
        );

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'=>$this->user->id,
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function testBreakInManyTimes(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'12:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $this->assertTrue(
            preg_match('/<button[^>]*>休憩入<\/button>/u', $response->getContent()) === 1
        );
    }

    public function testBreakOutButton(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);
        $response->assertRedirect('/attendance');
        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $this->assertTrue(
            preg_match('/<button[^>]*>休憩戻<\/button>/u', $response->getContent()) === 1
        );

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'12:00:00'
        ]);
        $response->assertRedirect('/attendance');
        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function testBreakOutManyTimes(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'12:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'16:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $this->assertTrue(
            preg_match('/<button[^>]*>休憩戻<\/button>/u', $response->getContent()) === 1
        );
    }

    public function testAdminBreak(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'11:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/attendance', [
            'attendance_status'=>'break_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'12:00:00'
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/logout');
        $this->assertFalse(auth()->check());

        $admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);

        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $today=Carbon::today();
        $attendanceDate=$today->year . '年' . $today->month . '月' . $today->day . '日の勤怠';
        $response->assertSee($attendanceDate);

        $breakIn=Carbon::createFromTime(11, 0, 0);
        $breakOut=Carbon::createFromTime(12, 0, 0);
        $totalBreak=$breakIn->diffInMinutes($breakOut);
        $breakHours=floor($totalBreak/60);
        $breakMinutes=floor($totalBreak%60);
        $expectBreak=str_pad($breakHours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($breakMinutes, 2, '0', STR_PAD_LEFT);

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*.*?' . preg_quote($this->user->name, '/') . '.*?' . preg_quote($expectBreak, '/') . '.*?<\/tr>/s', $response->getContent()
            ) === 1
        );
    }
}