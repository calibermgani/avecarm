<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class File_upload extends Model
{
    //
        protected $fillable = [
        'id','report_date','file_name','unique_name', 'file_url','notes','total_claims', 'new_claims','Import_by','claims_processed','status','deleted_at'
    ];
}
