<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Error_type extends Model
{
    protected $fillable = [
        'id','name','status','created_by','updated_at','updated_by'
    ];
}
