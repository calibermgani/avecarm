<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $connection = 'mysql2';

    	protected $table = 'profiles';

        protected $fillable = [
        'user_id','employee_code','dob', 'gender','mobile_phone','work_phone', 'address_flag_id','updated_at','created_by','updated_by','deleted_at'
    ];
}
