<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;

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
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.list');
    Route::get('/attendance', [StaffController::class, 'attendanceView'])->name('attendance');
});