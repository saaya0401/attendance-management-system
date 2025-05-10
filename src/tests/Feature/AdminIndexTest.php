<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Livewire\Livewire;
use App\Livewire\AdminAttendanceList;

class AdminIndexTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($this->admin);
        $this->actingAs($this->admin);

        $this->users=User::where('role', 'staff')->get();

        $today=Carbon::today()->toDateString();
        $logs=[];

        foreach($this->users as $user){
            $logs[]=[
                'user_id'=>$user->id,
                'attendance_status'=>'clock_in',
                'date'=>$today,
                'time'=>'09:00:00',
            ];
            $logs[]=[
                'user_id'=>$user->id,
                'attendance_status'=>'clock_out',
                'date'=>$today,
                'time'=>'17:00:00'
            ];
        }
        AttendanceLog::insert($logs);

    }

    public function testAdminIndex(){
        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $carbonDate=Carbon::today();

        $component=Livewire::test(AdminAttendanceList::class)->set('selectedDay', $carbonDate->format('Y-m-d'));
        $html=$component->html();
        $formattedDate=$carbonDate->format('Y/m/d');

        foreach($this->users as $user){
            $pattern="/<tr[^>]*>.*?" . preg_quote($user->name, '/') . ".*?" . "09:00.*?" . "17:00.*?" . "<\/tr>/s";

            $this->assertTrue(
                preg_match($pattern, $html) === 1
            );
        }
    }

    public function testAdminIndexDate(){
        $carbonDate=Carbon::today();
        $titleDate=$carbonDate->year . '年' . $carbonDate->month . '月' . $carbonDate->day . '日の勤怠';
        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($titleDate);
    }

    public function testPreviousDay(){
        $previousDay=Carbon::today()->subDay();
        $date=$previousDay->toDateString();
        $formattedDate=$previousDay->format('Y/m/d');
        $user=User::where('email', 'saaya@example.com')->first();

        AttendanceLog::insert([
            [
                'user_id'=>$user->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$user->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $component=Livewire::test(AdminAttendanceList::class)
            ->set('selectedDay', $previousDay->format('Y-m-d'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($user->name, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }

    public function testNextDay(){
        $nextDay=Carbon::today()->addDay();
        $date=$nextDay->toDateString();
        $formattedDate=$nextDay->format('Y/m/d');
        $user=User::where('email', 'saaya@example.com')->first();

        AttendanceLog::insert([
            [
                'user_id'=>$user->id,
                'attendance_status'=>'clock_in',
                'date'=>$date,
                'time'=>'08:00:00'
            ],
            [
                'user_id'=>$user->id,
                'attendance_status'=>'clock_out',
                'date'=>$date,
                'time'=>'17:00:00'
            ],
        ]);

        $response=$this->get('/admin/attendance/list');
        $response->assertStatus(200);

        $component=Livewire::test(AdminAttendanceList::class)
            ->set('selectedDay', $nextDay->format('Y-m-d'));
        $html=$component->html();
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . preg_quote($user->name, '/') . '.*?08:00.*?17:00.*?<\/tr>/s', $html
            ) === 1
        );
    }
}
