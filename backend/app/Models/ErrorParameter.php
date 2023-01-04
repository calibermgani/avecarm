<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorParameter extends Model
{
    protected $fillable = ['name','status','created_by','updated_at','updated_by'];
}
