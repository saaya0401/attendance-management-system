<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class StaffEditTest extends TestCase
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

    public function testEditErrorClockInOut(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'18:00',
            'clock_out'=>'17:00',
            'comment'=>'comment'
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'clock_out'=>'出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function testEditErrorBreakIn(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['19:00'],
            'break_out'=>['20:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'break_in.0'=>'休憩時間が勤務時間外です'
        ]);
    }

    public function testEditErrorBreakOut(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['17:00'],
            'break_out'=>['20:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'break_in.0'=>'休憩時間が勤務時間外です'
        ]);
    }

    public function testEditErrorComment(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>''
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'comment'=>'備考を記入してください'
        ]);
    }

    public function testEdit(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);
        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();

        $attendanceRequest=AttendanceRequest::where('user_id', $this->user->id)->where('date', $this->date)->first();
        $this->assertNotNull($attendanceRequest);
        $this->assertSame('pending', $attendanceRequest->approval_status);
        $this->assertSame('comment', $attendanceRequest->comment);

        $expectedChanges=[
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'breaks'=>[
                ['start'=>'11:00', 'end'=>'12:00']
            ]
        ];
        $this->assertEquals($expectedChanges, json_decode($attendanceRequest->request_changes, true));

        $response=$this->post('/logout');
        $this->assertFalse(auth()->check());

        $admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);

        $response=$this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $formattedDate=Carbon::parse($this->date)->format('Y/m/d');
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認待ち' . '.*?' . preg_quote('saaya', '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );

        $response=$this->get('stamp_correction_request/approve/' . $attendanceRequest->id);
        $carbonDate=Carbon::parse($this->date);
        $formattedYear=$carbonDate->year . '年';
        $formattedDay=$carbonDate->month . '月' . $carbonDate->day . '日';
        $response->assertStatus(200);
        $response->assertSee('saaya');
        $response->assertSee($formattedYear);
        $response->assertSee($formattedDay);
    }

    public function testEditPending(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);

        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();

        $response=$this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $formattedDate=Carbon::parse($this->date)->format('Y/m/d');
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認待ち' . '.*?' . preg_quote($this->user->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );
    }

    public function testEditApproved(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);

        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();

        $response=$this->post('/logout');
        $this->assertFalse(auth()->check());

        $admin=User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->actingAs($admin);

        $attendanceRequest=AttendanceRequest::where('user_id', $this->user->id)->where('date', $this->date)->first();
        $response=$this->patch('stamp_correction_request/approve/' . $attendanceRequest->id, [
            'approval_status'=>'approved'
        ]);
        $response->assertRedirect();

        $response=$this->post('/logout');
        $this->assertFalse(auth()->check());
        $this->actingAs($this->user);

        $response=$this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $formattedDate=Carbon::parse($this->date)->format('Y/m/d');
        $this->assertTrue(
            preg_match(
                '/<tr[^>]*>.*?' . '承認済み' . '.*?' . preg_quote($this->user->name, '/') . '.*?' . preg_quote($formattedDate, '/') . '.*?<\/tr>/s', $response->getContent()) === 1
        );
    }

    public function testEditDetail(){
        $response=$this->get('/attendance/' . $this->clockIn->id);
        $response->assertStatus(200);

        $response=$this->post('/attendance/' . $this->clockIn->id, [
            'date'=>$this->date,
            'clock_in'=>'09:00',
            'clock_out'=>'18:00',
            'break_in'=>['11:00'],
            'break_out'=>['12:00'],
            'comment'=>'comment'
        ]);
        $response->assertRedirect();

        $response=$this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $attendanceRequest=AttendanceRequest::where('user_id', $this->user->id)->where('date', $this->date)->first();
        $response=$this->get('/attendance/' . $attendanceRequest->id);
        $response->assertStatus(200);
    }
}
