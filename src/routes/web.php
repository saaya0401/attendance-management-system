<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;

Route::get('/admin/login', [AdminController::class, 'adminLoginView'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'adminLogin'])->name('admin.login');
Route::post('/register' , [StaffController::class, 'register'])->name('register');
Route::post('/login', [StaffController::class, 'login'])->name('login');

