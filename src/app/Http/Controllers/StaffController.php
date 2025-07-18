<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\AttendanceEditRequest;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\AttendanceRequest;

class StaffController extends Controller
{
    public function register(RegisterRequest $request){
        $user_data=$request->only(['name', 'email', 'password']);
        $user=User::create($user_data);
        $user->sendEmailVerificationNotification();
        $id=$user->id;
        return redirect('/email/verify/' . $id);
    }

    public function emailVerifyView($id){
        $user=User::find($id);
        return view('staff.email_verify', compact('user'));
    }

    public function emailVerify($id, $hash){
        $user = User::find($id);
        $user->markEmailAsVerified();
        Auth::login($user);
        return redirect('attendance')->with('message', 'メール認証が完了しました');
    }

    public function emailNotification(Request $request){
        $user=User::find($request->id);
        $user->sendEmailVerificationNotification();
        $id=$user->id;
        return redirect('/email/verify/' . $id)->with('message', '認証メールを再送しました');
    }

    public function login(LoginRequest $request){
        $credentials=$request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
                'email'=>'ログイン情報が登録されていません'
            ]);
        }
        $user=Auth::user();
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            $id=$user->id;
            return redirect('/email/verify/' . $id);
        }
        return redirect(route('attendance'));
    }

    public function logout(){
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }

    public function attendanceView(){
        $user=Auth::user();
        $today=Carbon::today();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $attendanceDate = $today->year . '年' . $today->month . '月' . $today->day . '日(' . $weekdays[$today->dayOfWeek] . ')';
        $attendanceTime=Carbon::now()->format('H:i');

        $todayLogs=AttendanceLog::where('user_id', $user->id)->whereDate('date', $today)->orderBy('time')->get();
        $attendanceStatus='勤務外';
        $attendanceLog=null;

        if($todayLogs->isNotEmpty()){
            $clockOutLog=$todayLogs->where('attendance_status', 'clock_out')->first();
            if($clockOutLog){
                $attendanceStatus='退勤済';
                $attendanceLog=$clockOutLog;
            }else{
                $lastLog=$todayLogs->last();
                $attendanceLog=$lastLog;
                switch($attendanceLog->attendance_status){
                    case 'clock_in':
                        $attendanceStatus='出勤中';
                        break;
                    case 'break_out':
                        $attendanceStatus='出勤中';
                        break;
                    case 'break_in':
                        $attendanceStatus='休憩中';
                        break;
                }
            }
        }
        return view('staff.attendance', compact('attendanceLog','attendanceStatus', 'attendanceDate', 'attendanceTime'));
    }

    public function attendance(Request $request){
        $attendanceData=$request->only(['attendance_status', 'date', 'time'] );
        $attendanceData['user_id']=Auth::id();
        $attendanceLog=AttendanceLog::create($attendanceData);
        return redirect()->route('attendance');
    }

    public function index(){
        return view('staff.attendance_list');
    }

    public function detail($id){
        $layout=Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.staff';
        $clockInLog=AttendanceLog::find($id);
        $carbonDate = Carbon::parse($clockInLog['date']);
        $formattedYear = $carbonDate->format('Y年');
        $formattedDate = $carbonDate->format('n月j日');
        $clockInTime=Carbon::parse($clockInLog['time'])->format('H:i');

        $date=$clockInLog['date'];
        $userId=$clockInLog['user_id'];
        $hasApprovedRequest=AttendanceRequest::where('user_id', $userId)->where('date', $date)->where('approval_status', 'pending')->doesntExist();
        $dateLogs=AttendanceLog::where('date', $date)->where('user_id', $userId)->orderBy('time')->get();

        $clockOutTime=null;
        $breaks=[];
        $breakInTime=null;

        foreach($dateLogs as $dateLog){
            $time=Carbon::parse($dateLog->time)->format('H:i');
            switch($dateLog->attendance_status){
                case 'clock_out';
                    $clockOutTime=$time;
                    break;
                case 'break_in';
                    $breakInTime=$time;
                    break;
                case 'break_out';
                    if($breakInTime !== null){
                        $breaks[]=[
                            'start'=>$breakInTime,
                            'end'=>$time,
                        ];
                        $time=null;
                    }
                    break;
            }
        }

        $attendanceRequest=AttendanceRequest::where('user_id', $userId)->where('date', $date)->orderBy('created_at', 'desc')->first();

        if ($attendanceRequest) {
            $hasPendingRequest = true;
            $changes = json_decode($attendanceRequest->request_changes, true);

            if (isset($changes['clock_in'])) {
                $clockInTime = $changes['clock_in'];
            }
            if (isset($changes['clock_out'])) {
                $clockOutTime = $changes['clock_out'];
            }
            if (isset($changes['breaks'])) {
                $breaks = $changes['breaks'];
            }
        } else {
            $hasPendingRequest = false;
        }

        return view('staff.attendance_detail', compact('layout', 'clockInLog', 'date', 'clockInTime', 'clockOutTime', 'breaks', 'formattedYear', 'formattedDate', 'hasApprovedRequest', 'attendanceRequest'));
    }

    public function edit(AttendanceEditRequest $request, $id){
        $userId=Auth::id();
        $date=$request->input('date');
        $comment=$request->input('comment');
        $id=AttendanceLog::where('user_id', $userId)->where('date', $date)
        ->where('attendance_status', 'clock_in')->first()->id;
        $changes=[
            'clock_in'=>$request->input('clock_in'),
            'clock_out'=>$request->input('clock_out'),
            'breaks'=>[]
        ];
        $breakIns=$request->input('break_in', []);
        $breakOuts=$request->input('break_out', []);
        foreach($breakIns as $i=>$start){
            $end=$breakOuts[$i] ?? null;
            if($start && $end){
                $changes['breaks'][]=[
                    'start'=>$start,
                    'end'=>$end,
                ];
            }
        }

        $attendanceRequest=AttendanceRequest::updateOrCreate(
            [
                'user_id'=>$userId,
                'date'=>$date,
            ],
            [
                'comment'=>$comment,
                'request_changes'=>json_encode($changes)
            ]);

        if($changes['clock_in']){
            AttendanceLog::where([
                'user_id'=>$userId,
                'date'=>$date,
                'attendance_status'=>'clock_in',
            ])->update([
                'attendance_request_id'=>$attendanceRequest->id
            ]);
        }

        if($changes['clock_out']){
            AttendanceLog::where([
                'user_id'=>$userId,
                'date'=>$date,
                'attendance_status'=>'clock_out',
            ])->update([
                'attendance_request_id'=>$attendanceRequest->id
            ]);
        }

        foreach($changes['breaks'] as $i=>$break){
            if($break['start']){
                AttendanceLog::where([
                    'user_id'=>$userId,
                    'date'=>$date,
                    'attendance_status'=>'break_in',
                ])->update([
                    'attendance_request_id'=>$attendanceRequest->id
                ]);
            }
            if($break['end']){
                AttendanceLog::where([
                    'user_id'=>$userId,
                    'date'=>$date,
                    'attendance_status'=>'break_out',
                ])->update([
                    'attendance_request_id'=>$attendanceRequest->id
                ]);
            }
        }
        return redirect()->route('detail', ['id'=>$id])->with('message', '勤怠修正を申請しました');
    }

    public function requestList(Request $request){
        $userId=Auth::id();
        $tab=$request->query('tab');
        if($tab === 'approved'){
            $attendanceRequests=AttendanceRequest::where('user_id', $userId)->where('approval_status', 'approved')->orderBy('date')->get();
        }else{
            $attendanceRequests=AttendanceRequest::where('user_id', $userId)->where('approval_status', 'pending')->orderBy('date')->get();
        }
        foreach($attendanceRequests as $attendanceRequest){
            $log=AttendanceLog::where('user_id', $userId)->where('date', $attendanceRequest->date)->where('attendance_status', 'clock_in')->first();
            $attendanceRequest->detail_id=$log->id;
        }
        return view('staff.request_list', compact('tab', 'attendanceRequests'));
    }
}
