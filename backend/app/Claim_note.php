<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Claim_note extends Model
{
    //
    protected $fillable = [
        'claim_id','state','content','created_by'
    ];
}
