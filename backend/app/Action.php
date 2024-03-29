<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'claim_id','action_type','action_id', 'assigned_to','assigned_by','status','created_at', 'created_by','updated_at','updated_by'
    ];
}
