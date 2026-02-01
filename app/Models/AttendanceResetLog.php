<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceResetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'reset_by',
        'old_status',
        'new_status',
        'old_check_in',
        'new_check_in',
        'new_check_out',
        'old_check_out',
        'reason',
    ];


    protected $casts = [
        'old_check_in' => 'datetime:H:i:s',
        'new_check_in' => 'datetime:H:i:s',
        'old_check_out' => 'datetime:H:i:s',
        'new_check_out' => 'datetime:H:i:s',
    ];



    //relasi
    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }


    public function admin() {
        return $this->belongsTo(User::class, 'reset_by');
    }
}
