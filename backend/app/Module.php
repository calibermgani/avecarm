<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Module extends Model
{
    //
    protected $fillable = [
        'id','module_name','parent_module_id', 'created_at','updated_at','created_by', 'updated_by','deleted_at'
    ];
}
