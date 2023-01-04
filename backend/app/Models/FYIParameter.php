<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FYIParameter extends Model
{
    protected $fillable = ['fyi_parameter','fyi_sub_parameter','status','created_by','updated_at','updated_by'];
}
