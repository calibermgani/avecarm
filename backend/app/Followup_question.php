<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Followup_question extends Model
{
    //
    protected $fillable = [
        'question','question_label','hint','category_id', 'field_type','field_validation','date_type', 'status','created_by'
    ];
}
