<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Insurance extends Model
{
    //
    protected $fillable = [
        'id','ins_name','ins_type', 'policy_id','group_id','ins_address_line_1', 'ins_address_line_2','ins_city','ins_state','ins_zipcode','ins_phone_no','ins_auth','created_at', 'updated_at','created_by','updated_by', 'deleted_at'
    ];

}
