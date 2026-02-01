<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'reason',
        'type',
    ];

    //relasi
    public function attendance() 
    {
        return $this->belongsTo(Attendance::class);
    }


    //accessor buat dapetin member lewat attendance
    public function member()
    {
        return $this->hasOneThrough(
            Member::class,
            Attendance::class,
            'id', //fk di attendance
            'id', //fk di member
            'attendance_id', //pk di permission
            'member_id'  //pk di attendance
        );
    }


}
