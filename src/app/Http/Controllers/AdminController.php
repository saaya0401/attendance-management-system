<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

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
}
