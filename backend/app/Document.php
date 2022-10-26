<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //

	//protected $table = 'documents';

    protected $fillable = [
        'document_name','category','file_name','uploaded_name','created_at','created_by','updated_at','updated_by'
    ];
}
