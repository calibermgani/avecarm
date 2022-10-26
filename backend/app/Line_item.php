<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Line_item extends Model
{
    //
    protected $fillable = [
        'id','claim_id','total_ar_due', 'ins_ar','pat_ar','units', 'modifier','icd','cpt','dos','created_at','updated_at'
    ];
}
