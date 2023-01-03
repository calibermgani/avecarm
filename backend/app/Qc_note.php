<?php

namespace App;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use App\Error_type;

class Qc_note extends Model
{

    protected $table = 'qc_notes';


    protected $casts = [
        'options' => 'json',
    ];

    protected $fillable = [
        'claim_id','state','content','root_cause','error_type','error_parameter','error_sub_parameter','fyi_parameter','fyi_sub_parameter','created_by'
    ];

    public function root() {
        return $this->belongsTo('App\Root_cause', 'root_cause', 'id');
    }

    public function error_types() {
        return $this->belongsToJson('App\Error_type', 'options->error_types[]->error_type', 'id');
    }
    

}
