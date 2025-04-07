<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $fillable=['user_id', 'approval_status', 'date', 'request_changes', 'comment'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
