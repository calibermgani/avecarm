<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorParameter extends Model
{
    protected $fillable = ['error_parameter','error_sub_parameter','status','created_by','updated_at','updated_by'];
}
