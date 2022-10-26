<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = [
        'customer_name','short_name','customer_desc','contact_person','email','addressline1','addressline2','city','state','zipcode5','zipcode4','phone','phoneext','mobile','status','created_by','updated_by','updated_at'
    ];
}
