<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    //
    protected $fillable = [
        'claim_id','action_type','action_id', 'assigned_to','assigned_by','status','created_at', 'created_by','updated_at','updated_by','deleted_at'
    ];
}
