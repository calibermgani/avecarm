<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Followup_category extends Model
{
    //
    protected $fillable = [
        'name','label_name','status', 'created_by'
    ];
}
