<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workorder_user_field extends Model
{
    //
    protected $fillable = [
        'work_order_id','user_id','cliam_no', 'completed_claim','created_at','updated_at','deleted_at'
    ];
}
