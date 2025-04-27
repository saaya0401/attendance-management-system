<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
        $user=User::find($id);
        return view('admin.staff_attendance_list', compact('user'));
    }

    public function export(Request $request){
        $logs=$request->input('logs', []);
        $csvData=[];
        $csvData[]=[
            '日付', '出勤', '退勤', '休憩', '合計'
        ];
        foreach($logs as $log){
            $csvData[]=[
                $log['date'] ?? '',
                $log['clock_in'] ?? '',
                $log['clock_out'] ?? '',
                $log['break'] ?? '',
                $log['total'] ?? '',
            ];
        }

        $month=$request->input('month');
        $monthFormatted=\Carbon\Carbon::createFromFormat('Y/m', $month)->format('Y年m月');
        $userName=$request->input('user_name');
        $filename=$userName . 'さんの' . $monthFormatted . 'の勤怠.csv';

        return response()->streamDownload(function () use ($csvData){
            $file=fopen('php://output', 'w');
            foreach($csvData as $row){
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename, [
            'Content-Type'=>'text/csv',
        ]);
    }

    public function requestList(Request $request){
        $tab=$request->query('tab');
        if($tab === 'approved'){
            $attendanceRequests=AttendanceRequest::where('approval_status', 'approved')->orderBy('user_id')->orderBy('date')->get();
        }else{
            $attendanceRequests=AttendanceRequest::where('approval_status', 'pending')->orderBy('user_id')->orderBy('date')->get();
        }
        foreach($attendanceRequests as $attendanceRequest){
            $log=AttendanceLog::where('user_id', $attendanceRequest->user_id)->where('date', $attendanceRequest->date)->where('attendance_status', 'clock_in')->first();
            $attendanceRequest->attendance_request_id=$log->attendance_request_id;
        }
        return view('admin.request_list', compact('tab', 'attendanceRequests'));
    }

    public function requestApproveView($attendanceCorrectRequest){
        $attendanceRequest=AttendanceRequest::find($attendanceCorrectRequest);
        $clockInLog=AttendanceLog::where('attendance_request_id', $attendanceRequest->id)->where('attendance_status', 'clock_in')->first();
        $date=$clockInLog['date'];
        $userId=$clockInLog['user_id'];
        $carbonDate = Carbon::parse($date);
        $formattedYear = $carbonDate->format('Y年');
        $formattedDate = $carbonDate->format('n月j日');
        $clockInTime=Carbon::parse($clockInLog['time'])->format('H:i');
        $clockOutTime=null;
        $breaks=[];

        if($attendanceRequest->request_changes){
            $changes=json_decode($attendanceRequest->request_changes, true);
            if(isset($changes['clock_in'])){
                $clockInTime=$changes['clock_in'];
            }

            if(isset($changes['clock_out'])){
                $clockOutTime=$changes['clock_out'];
            }

            if(isset($changes['breaks'])){
                $breaks=$changes['breaks'];
            }
        }

        return view('admin.approve', compact('clockInLog', 'date', 'clockInTime', 'clockOutTime', 'breaks', 'formattedYear', 'formattedDate', 'attendanceRequest'));
    }

    public function requestApprove($attendanceCorrectRequest){
        $attendanceRequest=AttendanceRequest::find($attendanceCorrectRequest);

        $attendanceRequest->update([
            'approval_status'=>'approved'
        ]);

        $changes=json_decode($attendanceRequest->request_changes, true);
        if(isset($changes['clock_in'])  && $changes['clock_in']){
            AttendanceLog::updateOrCreate(
                [
                    'user_id' => $attendanceRequest->user_id,
                    'date' => $attendanceRequest->date,
                    'attendance_status' => 'clock_in'
                ],
                [
                    'time' => $changes['clock_in'],
                    'attendance_request_id' => $attendanceRequest->id
                ]
            );
        }

        if (isset($changes['clock_out']) && $changes['clock_out']) {
            AttendanceLog::updateOrCreate(
                [
                    'user_id' => $attendanceRequest->user_id,
                    'date' => $attendanceRequest->date,
                    'attendance_status' => 'clock_out'
                ],
                [
                    'time' => $changes['clock_out'],
                    'attendance_request_id' => $attendanceRequest->id
                ]
            );
        }

        if (isset($changes['breaks']) && is_array($changes['breaks'])) {
            $existingBreakIns = AttendanceLog::where([
                'user_id' => $attendanceRequest->user_id,
                'date' => $attendanceRequest->date,
                'attendance_status' => 'break_in'
            ])->orderBy('id')->get();

            $existingBreakOuts = AttendanceLog::where([
                'user_id' => $attendanceRequest->user_id,
                'date' => $attendanceRequest->date,
                'attendance_status' => 'break_out'
            ])->orderBy('id')->get();

            foreach ($changes['breaks'] as $i => $break) {
                if (isset($break['start']) && $break['start']) {
                    AttendanceLog::updateOrCreate(
                        [
                            'user_id' => $attendanceRequest->user_id,
                            'date' => $attendanceRequest->date,
                            'attendance_status' => 'break_in',
                            'id' => isset($existingBreakIns[$i]) ? $existingBreakIns[$i]->id : null
                        ],
                        [
                            'time' => $break['start'],
                            'attendance_request_id' => $attendanceRequest->id
                        ]
                    );
                }

                if (isset($break['end']) && $break['end']) {
                    AttendanceLog::updateOrCreate(
                        [
                            'user_id' => $attendanceRequest->user_id,
                            'date' => $attendanceRequest->date,
                            'attendance_status' => 'break_out',
                            'id' => isset($existingBreakOuts[$i]) ? $existingBreakOuts[$i]->id : null
                        ],
                        [
                            'time' => $break['end'],
                            'attendance_request_id' => $attendanceRequest->id
                        ]
                    );
                }
            }
        }
        return redirect()->route('correction.approve', ['attendance_correct_request'=>$attendanceCorrectRequest])->with('message', '勤怠修正の申請を承認しました');
    }
}
