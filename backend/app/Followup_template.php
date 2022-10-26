<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Followup_template extends Model
{
    //
    protected $fillable = [
        'claim_id','rep_name','date','phone', 'insurance','category_id','content','created_by'
    ];

    public function insurance() {
        return $this->belongsTo('App\Insurance', 'insurance_id', 'id');
    }
}
