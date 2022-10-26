<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Workorder_field extends Model
{
    //
    protected $fillable = [
        'work_order_name','work_order_type','due_date','status', 'priority','work_notes','created_by', 'created_at','updated_at','deleted_at'
    ];
}
