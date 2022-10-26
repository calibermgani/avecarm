<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mysql2';
    protected $table = "roles";
    protected $fillable = [
        'role_name','status','created_by'
    ];
}
