<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Claim_history extends Model
{
    //

	//protected $table = 'claim_histories';

    protected $fillable = [
        'claim_id','claim_state','assigned_by','assigned_to', 'created_at'
    ];
    
}
