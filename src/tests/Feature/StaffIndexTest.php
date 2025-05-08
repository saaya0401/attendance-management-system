<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Livewire\Livewire;
use App\Livewire\AttendanceList;

class StaffIndexTest extends TestCase
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

    public function testStaffIndex(){
        $dateFirst=Carbon::today()->startOfMonth();
        $dateSecond=Carbon::today()->startOfMonth()->addDay();
        AttendanceLog::insert([
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_in',
                'date'=>$dateFirst->toDateString(),
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_out',
                'date'=>$dateFirst->toDateString(),
                'time'=>'17:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_in',
                'date'=>$dateSecond->toDateString(),
                'time'=>'09:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_out',
                'date'=>$dateSecond->toDateString(),
                'time'=>'18:00:00'
            ],
        ]);

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $formattedDateFirst=$dateFirst->format('m/d') . '(' . $weekdays[$dateFirst->dayOfWeek] . ')';
        $formattedDateSecond=$dateSecond->format('m/d') . '(' . $weekdays[$dateSecond->dayOfWeek] . ')';
        $response=$this->get('/attendance/list');
        $response->assertStatus(200);

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDateFirst, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $response->getContent()
            ) === 1
        );
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDateSecond, '/') . '.*?09:00.*?18:00.*?<\/tr>/s', $response->getContent()
            ) === 1
        );
    }

    public function testCurrentMonth(){
        $date=Carbon::today()->format('Y/m');
        $response=$this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($date);
    }

    public function testPreviousMonth(){
        $previousMonth=Carbon::today()->subMonth()->startOfMonth();
        $date=$previousMonth->toDateString();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $formattedDate=$previousMonth->format('m/d') . '(' . $weekdays[$previousMonth->dayOfWeek] . ')';

        AttendanceLog::insert([
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/attendance/list');
        $response->assertStatus(200);

        $component=Livewire::test(AttendanceList::class)
            ->set('selectedMonth', $previousMonth->format('Y-m'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDate, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }

    public function testNextMonth(){
        $nextMonth=Carbon::today()->addMonth()->startOfMonth();
        $date=$nextMonth->toDateString();
        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $formattedDate=$nextMonth->format('m/d') . '(' . $weekdays[$nextMonth->dayOfWeek] . ')';

        AttendanceLog::insert([
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->user->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/attendance/list');
        $response->assertStatus(200);

        $component=Livewire::test(AttendanceList::class)->set('selectedMonth', $nextMonth->format('Y-m'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDate, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }

    public function testSelectDetail(){
        $date=Carbon::today()->toDateString();
        $clockIn=AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_in',
            'date'=>$date,
            'time'=>'08:00:00'
        ]);
        AttendanceLog::create([
            'user_id'=>$this->user->id,
            'attendance_status'=>'clock_out',
            'date'=>$date,
            'time'=>'17:00:00'
        ]);

        $response=$this->get('/attendance/list');
        $response->assertStatus(200);

        $response=$this->get('/attendance/' . $clockIn->id);
        $response->assertStatus(200);
    }
}
