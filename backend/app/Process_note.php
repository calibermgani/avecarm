<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Process_note extends Model
{
	protected $table = "process_notes";
    //
    protected $fillable = [
        'claim_id','state','claim_status','content','created_by'
    ];
}
