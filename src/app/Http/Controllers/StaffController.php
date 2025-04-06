<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

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

    public function attendanceView(){
        return view('staff.attendance');
    }

    public function logout(){
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    }
}
