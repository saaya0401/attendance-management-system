<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;

Route::get('/admin/login', [AdminController::class, 'loginView'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/register' , [StaffController::class, 'register'])->name('register');
Route::get('/email/verify/{id}', [StaffController::class, 'emailVerifyView']);
Route::middleware(['signed'])->get('/email/verify/{id}/{hash}', [StaffController::class, 'emailVerify'])->name('verification.verify');
Route::post('/email/verification-notification', [StaffController::class, 'emailNotification']);
Route::post('/login', [StaffController::class, 'login'])->name('login');

Route::middleware('auth')->group(function (){
    Route::post('/logout', [StaffController::class, 'logout']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
    Route::get('/attendance', [StaffController::class, 'attendanceView'])->name('attendance');
    Route::post('/attendance', [StaffController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/list', [StaffController::class, 'index'])->name('attendance.list');
    Route::get('/stamp_correction_request/list', function (Request $request) {
        $controller= auth()->user()->role === 'admin' ? \App\Http\Controllers\AdminController::class : \App\Http\Controllers\StaffController::class;
        return app($controller)->requestList($request);
    })->name('request.list');
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.list');
    Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('staff.list');
    Route::post('/export', [AdminController::class, 'export']);
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffAttendanceList'])->name('staff.attendance');
    Route::get('/attendance/{id}', [StaffController::class, 'detail'])->name('detail');
    Route::post('/attendance/{id}', [StaffController::class, 'edit'])->name('detail.edit');
    Route::patch('/admin/attendance/{id}', [AdminController::class, 'update'])->name('admin.update');
});