<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AdminApproveTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($this->admin);
        $this->actingAs($this->admin);

        $this->user1=User::where('email', 'saaya@example.com')->first();
        $this->user2=User::where('email', 'koharu@example.com')->first();
        $this->date=Carbon::today();

        $this->attendanceRequest1=AttendanceRequest::create([
            'user_id'=>$this->user1->id,
            'approval_status'=>'pending',
            'date'=>$this->date->toDateString(),
            'request_changes'=>json_encode([
                'clock_in'=>'08:00',
                'clock_out'=>'17:00'
            ]),
            'comment'=>'comment'
        ]);
        AttendanceLog::create([
            'user_id'=>$this->user1->id,
            'attendance_status'=>'clock_in',
            'date'=>$this->attendanceRequest1->date,
            'time'=>'07:00:00',
            'attendance_request_id'=>$this->attendanceRequest1->id
        ]);

        $this->attendanceRequest2=AttendanceRequest::create([
            'user_id'=>$this->user2->id,
            'approval_status'=>'pending',
            'date'=>$this->date->toDateString(),
            'request_changes'=>json_encode([
                'clock_in'=>'08:00',
                'clock_out'=>'17:00'
            ]),
            'comment'=>'comment'
        ]);
        AttendanceLog::create([
            'user_id'=>$this->user2->id,
            'attendance_status'=>'clock_in',
            'date'=>$this->attendanceRequest2->date,
            'time'=>'07:00:00',
            'attendance_request_id'=>$this->attendanceRequest2->id
        ]);
    }

    public function testAdminPendingRequest(){
        $formattedDate=Carbon::parse($this->date)->format('Y/m/d');

        $response=$this->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認待ち' . '.*?' . preg_quote($this->user1->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認待ち' . '.*?' . preg_quote($this->user2->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );
    }

    public function testAdminApprovedRequest(){
        $this->attendanceRequest1->update([
            'approval_status'=>'approved',
        ]);

        $this->attendanceRequest2->update([
            'approval_status'=>'approved',
        ]);

        $formattedDate=Carbon::parse($this->date)->format('Y/m/d');

        $response=$this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認済み' . '.*?' . preg_quote($this->user1->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );

        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認済み' . '.*?' . preg_quote($this->user2->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );
    }

    public function testAdminRequestDetail(){
        $formattedYear=$this->date->year . '年';
        $formattedDate=$this->date->month . '月' . $this->date->day . '日';
        $requestChanges=json_decode($this->attendanceRequest1->request_changes, true);
        $formattedClockIn=Carbon::createFromFormat('H:i', $requestChanges['clock_in'])->format('H:i');
        $formattedClockOut=Carbon::createFromFormat('H:i', $requestChanges['clock_out'])->format('H:i');

        $response=$this->get('/stamp_correction_request/approve/' . $this->attendanceRequest1->id);
        $response->assertStatus(200);
        $response->assertSee($this->user1->name);
        $response->assertSee($formattedYear);
        $response->assertSee($formattedDate);
        $response->assertSee($formattedClockIn);
        $response->assertSee($formattedClockOut);
        $response->assertSee($this->attendanceRequest1->comment);
    }

    public function testAdminRequestApprove(){
        $response=$this->get('/stamp_correction_request/approve/' . $this->attendanceRequest1->id);
        $response->assertStatus(200);
        $response->assertSee('/stamp_correction_request/approve/' . $this->attendanceRequest1->id);

        $response=$this->patch('/stamp_correction_request/approve/' . $this->attendanceRequest1->id, [
            'approval_status'=>'approved',
            '_token'=>csrf_token(),
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'=>$this->user1->id,
            'attendance_request_id'=>$this->attendanceRequest1->id,
            'attendance_status'=>'clock_in',
            'date'=>$this->date->toDateString(),
            'time'=>'08:00:00'
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id'=>$this->user1->id,
            'attendance_request_id'=>$this->attendanceRequest1->id,
            'attendance_status'=>'clock_out',
            'date'=>$this->date->toDateString(),
            'time'=>'17:00:00'
        ]);
    }
}
