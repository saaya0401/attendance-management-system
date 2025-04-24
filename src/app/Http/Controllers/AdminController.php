<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AttendanceEditRequest;
use App\Models\AttendanceLog;
use App\Models\AttendanceRequest;
use App\Models\User;

class AdminController extends Controller
{
    public function loginView(){
        return view('admin.login');
    }

    public function login(LoginRequest $request){
        $credentials=$request->only('email', 'password');
        if(!Auth::attempt($credentials)){
            throw ValidationException::withMessages([
                'email'=>'ログイン情報が登録されていません'
            ]);
        }
        $user=Auth::user();
        if($user->role !== 'admin'){
            Auth::logout();
            throw ValidationException::withMessages([
                'email'=>'管理者アカウントではありません'
            ]);
        }
        return redirect(route('admin.list'));
    }

    public function logout(){
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/admin/login');
    }

    public function index(){
        return view('admin.attendance_list');
    }

    public function update(AttendanceEditRequest $request, $id){
        $userId=$request->input('user_id');
        $date=$request->input('date');
        $comment=$request->input('comment');
        $changes=[
            'clock_in'=>$request->input('clock_in'),
            'clock_out'=>$request->input('clock_out'),
            'breaks'=>[]
        ];
        $breakIns=$request->input('break_in', []);
        $breakOuts=$request->input('break_out', []);

        foreach($breakIns as $i=>$start){
            $end=$breakOuts[$i] ?? null;
            if($start || $end){
                $changes['breaks'][]=[
                    'start'=>$start,
                    'end'=>$end
                ];
            }
        }

        $attendanceRequest=AttendanceRequest::create([
            'user_id'=>$userId,
            'date'=>$date,
            'comment'=>$comment,
            'request_changes'=>json_encode($changes),
            'approval_status'=>'approved'
        ]);

        if($changes['clock_in']){
            AttendanceLog::updateOrCreate(
                [
                    'user_id'=>$userId,
                    'date'=>$date,
                    'attendance_status'=>'clock_in'
                ],
                [
                    'time'=>$changes['clock_in'],
                    'attendance_request_id'=>$attendanceRequest->id
                ]);
            }

        if($changes['clock_out']){
            AttendanceLog::updateOrCreate(
                [
                    'user_id'=>$userId,
                    'date'=>$date,
                    'attendance_status'=>'clock_out'
                ],
                [
                    'time'=>$changes['clock_out'],
                    'attendance_request_id'=>$attendanceRequest->id
                ]);
        }

        $existingBreakIns=AttendanceLog::where([
            'user_id'=>$userId,
            'date'=>$date,
            'attendance_status'=>'break_in'
        ])->orderBy('id')->get();
        $existingBreakOuts=AttendanceLog::where([
            'user_id'=>$userId,
            'date'=>$date,
            'attendance_status'=>'break_out'
        ])->orderBy('id')->get();

        foreach($changes['breaks'] as $i=>$break){
            if($break['start']){
                if(isset($existingBreakIns[$i])){
                    $existingBreakIns[$i]->update([
                        'time'=>$break['start'],
                        'attendance_request_id'=>$attendanceRequest->id
                    ]);
                }else{
                    AttendanceLog::create([
                        'user_id'=>$userId,
                        'attendance_status'=>'break_in',
                        'time'=>$break['start'],
                        'date'=>$date,
                        'attendance_request_id'=>$attendanceRequest->id
                    ]);
                }
            }

            if($break['end']){
                if(isset($existingBreakOuts[$i])){
                    $existingBreakOuts[$i]->update([
                        'time'=>$break['end'],
                        'attendance_request_id'=>$attendanceRequest->id
                    ]);
                }else{
                    AttendanceLog::create([
                        'user_id'=>$userId,
                        'attendance_status'=>'break_out',
                        'time'=>$break['end'],
                        'date'=>$date,
                        'attendance_request_id'=>$attendanceRequest->id
                    ]);
                }
            }
        }
        return redirect()->route('detail', ['id'=>$id])->with('message', '勤怠を修正しました');
    }

    public function staffList(){
        $users=User::where('role', 'staff')->get();
        return view('admin.staff_list', compact('users'));
    }

    public function staffAttendanceList($id){
        $user=User::find($id)->first();
        return view('admin.staff_attendance_list', compact('user'));
    }
}
