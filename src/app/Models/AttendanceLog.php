<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable=['user_id', 'attendance_request_id', 'attendance_status', 'date', 'time'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function attendanceRequest(){
        return $this->belongsTo(AttendanceRequest::class);
    }
}
