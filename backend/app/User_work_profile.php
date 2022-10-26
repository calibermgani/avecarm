<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_work_profile extends Model
{
    // protected $connection = 'mysql2';
    protected $fillable = [
        'user_id','practice_id','role_id','claim_assign_limit','created_at','caller_benchmark','created_by','updated_by','updated_at'
    ];
}
