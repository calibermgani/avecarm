<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;


class Address_flag extends Model
{
    protected $connection = 'mysql2';
     protected $fillable = [
        'address_company','type','address_line_1', 'address_line_2','city','state', 'zip5','zip4','is_address_match','updated_at'
    ];
}
