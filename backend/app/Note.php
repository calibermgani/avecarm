<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Note extends Model
{
    //
    protected $fillable = [
        'id','claim_id','notes', 'notes_type','user','created_at', 'updated_at','created_by','updated_by','deleted_at'
    ];
}
