<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Livewire\Livewire;
use App\Livewire\StaffAttendanceList;

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($this->admin);
        $this->actingAs($this->admin);

        $this->staff=User::where('email', 'saaya@example.com')->first();
    }

    public function testStaffList(){
        $users=User::where('role', 'staff')->get();
        $response=$this->get('/admin/staff/list');
        $response->assertStatus(200);

        foreach($users as $user){
            $this->assertTrue(
                preg_match(
                    '/<tr[^>]*>.*?' . preg_quote($user->name, '/') . '.*?' . preg_quote($user->email, '/') . '.*?<\/tr>/s', $response->getContent()
                ) === 1
            );
        }
    }

    public function testAdminStaffAttendanceList(){
        $dateFirst=Carbon::today()->startOfMonth();
        $dateSecond=Carbon::today()->startOfMonth()->addDay();

        AttendanceLog::insert([
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_in',
                'date'=>$dateFirst->toDateString(),
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_out',
                'date'=>$dateFirst->toDateString(),
                'time'=>'17:00:00'
            ],
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_in',
                'date'=>$dateSecond->toDateString(),
                'time'=>'09:00:00'
            ],
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_out',
                'date'=>$dateSecond->toDateString(),
                'time'=>'18:00:00'
            ],
        ]);

        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $formattedDateFirst=$dateFirst->format('m/d') . '(' . $weekdays[$dateFirst->dayOfWeek] . ')';
        $formattedDateSecond=$dateSecond->format('m/d') . '(' . $weekdays[$dateSecond->dayOfWeek] . ')';

        $response=$this->get('/admin/attendance/staff/' . $this->staff->id);
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

    public function testAdminStaffListPreviousMonth(){
        $previousMonth=Carbon::today()->subMonth()->startOfMonth();
        $date=$previousMonth->toDateString();
        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $formattedDate=$previousMonth->format('m/d') . '(' . $weekdays[$previousMonth->dayOfWeek] . ')';

        AttendanceLog::insert([
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/admin/attendance/staff/' . $this->staff->id);
        $response->assertStatus(200);
        $component=Livewire::test(StaffAttendanceList::class, ['user'=>$this->staff])
            ->set('selectedMonth', $previousMonth->format('Y-m'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDate, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }

    public function testAdminStaffListNextMonth(){
        $nextMonth=Carbon::today()->addMonth()->startOfMonth();
        $date=$nextMonth->toDateString();
        $weekdays=['日', '月', '火', '水', '木', '金', '土'];
        $formattedDate=$nextMonth->format('m/d') . '(' . $weekdays[$nextMonth->dayOfWeek] . ')';

        AttendanceLog::insert([
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$this->staff->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/admin/attendance/staff/' . $this->staff->id);
        $response->assertStatus(200);
        $component=Livewire::test(StaffAttendanceList::class, ['user'=>$this->staff])
            ->set('selectedMonth', $nextMonth->format('Y-m'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($formattedDate, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }

    public function testAdminStaffAttendanceDetail(){
        $date=Carbon::today()->startOfMonth();

        $clockIn=AttendanceLog::create([
            'user_id'=>$this->staff->id,
            'attendance_status'=>'clock_in',
            'date'=>$date->toDateString(),
            'time'=>'08:00:00'
        ]);
        AttendanceLog::create([
            'user_id'=>$this->staff->id,
            'attendance_status'=>'clock_out',
            'date'=>$date->toDateString(),
            'time'=>'17:00:00'
        ]);

        $response=$this->get('/admin/attendance/staff/' . $this->staff->id);
        $response->assertStatus(200);
        $response->assertSee('/attendance/' . $clockIn->id);

        $response=$this->get('/attendance/' . $clockIn->id);
        $response->assertStatus(200);
    }
}
