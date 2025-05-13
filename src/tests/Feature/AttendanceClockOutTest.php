<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceClockOutTest extends TestCase
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

    public function testClockOutButton(){
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00'
        ]);
        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $this->assertTrue(
            preg_match('/<button[^>]*>退勤<\/button>/u', $response->getContent()) === 1,
        );

        $response=$this->post('/attendance', [
            'attendance_status'=>'clock_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'17:00:00',
            '_token' => csrf_token(),
        ]);
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'17:00:00'
        ]);

        $response=$this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function testAdminClockOut(){
        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $response=$this->post('/attendance', [
            'attendance_status'=>'clock_in',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'08:00:00',
            '_token' => csrf_token(),
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/attendance', [
            'attendance_status'=>'clock_out',
            'date'=>Carbon::today()->toDateString(),
            'time'=>'17:00:00',
            '_token' => csrf_token(),
        ]);
        $response->assertRedirect('/attendance');

        $response=$this->post('/logout', [
            '_token' => csrf_token(),
        ]);
        $this->assertFalse(auth()->check());

        $admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);

        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $today=Carbon::today();
        $attendanceDate=$today->year . '年' . $today->month . '月' . $today->day . '日の勤怠';
        $response->assertSee($attendanceDate);

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($this->user->name, '/') . '.*?17:00.*?<\/tr>/s', $response->getContent()
            ) === 1
        );
    }
}
