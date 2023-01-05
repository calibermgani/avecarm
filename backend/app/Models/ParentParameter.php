<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentParameter extends Model
{
    
    protected $fillable = ['err_params', 'status', 'created_by', 'updated_by'];
    
    protected $table ='parent_parameter';

}


