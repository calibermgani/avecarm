<?php
namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Statuscode extends Model
{
    protected $table = 'status';

    protected $fillable = [
        'status_code','description','status','created_at','updated_at','created_by','updated_by','deleted_at','modules'
    ];
}
