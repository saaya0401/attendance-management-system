<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class AttendanceDateTest extends TestCase
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

    public function testAttendanceDate(){
        $response=$this->get('/attendance');
        $response->assertStatus(200);

        $now=Carbon::now();
        $today=Carbon::today();

        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $attendanceDate=$today->year . '年' . $today->month . '月' . $today->day . '日(' . $weekdays[$today->dayOfWeek] . ')';
        $attendanceTime=$now->format('H:i');

        $response->assertSee($attendanceDate);
        $response->assertSee($attendanceTime);
    }
}
