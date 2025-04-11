<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\AttendanceLog;

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
        $attendanceLog=AttendanceLog::where('user_id', $user->id)->whereDate('date', $today)->orderBy('created_at', 'desc')->first();
        $attendanceStatus='勤務外';
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $attendanceDate = $today->year . '年' . $today->month . '月' . $today->day . '日(' . $weekdays[$today->dayOfWeek] . ')';
        $attendanceTime=Carbon::now()->format('H:i');

        if($attendanceLog){
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
                case 'clock_out':
                    $attendanceStatus='退勤済';
                    break;
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

    public function requestList(){
        return view('staff.request_list');
    }
}
