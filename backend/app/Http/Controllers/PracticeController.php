<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Address_flag;
use App\Profile;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiRequest;
use App\Practice;
use Validator;
use JWTFactory;
use JWTAuth;
use DB;
use Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DBConnectionController as DBConnectionController;
use App\Role;
use App\User_work_profile;
use App;
use Config;
use Session;

class PracticeController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['createpractice','getPractices','selectPractice']]);
    }

    public function createpractice(ApiRequest $request)
    {
        $data=$request->get('data');

        
        $dbconnection = new DBConnectionController();
        $db_name=strtolower($data['practice_name']);
        $dbconnection->createSchema($db_name);


        $user=Practice::create([
            'practice_name'                => $data['practice_name'],
            'practice_description'         => @$data['practice_desc'],
            'email'                        => $data['email'],
            'phone'                        => @$data['phone_no'],
            'fax'                          => @$data['fax_no'],
            'avatar_name'                  => @$data['avatar_name'],
            'practice_link'                => @$data['practice_link'],
            'doing_business_as'            => @$data['business_name'],
            'speciality_id'                => @$data['spec'],
            'taxanomy_id'                  => @$data['taxonomy'],
            'billing_entity'               => 'Yes',
            'entity_type'                  => @$data['entity_type'],
            'tax_id'                       => @$data['tax_id'],
            'group_tax_id'                 => @$data['tax_id'],
            'npi'                          => @$data['npi'],
            'group_npi'                    => @$data['medicare_id2'],
            'medicare_ptan'                => @$data['medicare_ptan'],
            'medicaid'                     => @$data['medicare_id'],
            'mail_add_1'                   => @$data['mail_address_1'],
            'mail_add_2'                   => @$data['mail_address_2'],
            'mail_city'                    => @$data['mail_city'],
            'mail_state'                   => @$data['mail_state'],
            'mail_zip5'                    => @$data['mail_zip'],
            'mail_zip4'                    => @$data['mail_zip'],
            'primary_add_1'                => @$data['prim_address1'],
            'primary_add_2'                => @$data['prim_address2'],
            'primary_city'                 => @$data['prim_city'],
            'primary_state'                => @$data['prim_state'],
            'primary_zip5'                 => @$data['prim_zip_code'],
            'primary_zip4'                 => @$data['prim_zip_code'],
            'practice_db_id'               => @$db_name,
            'status'                       => 'Active',
            'created_by'                   => $request->get('uid')          ]);

            return response()->json([
                'data' => $dbconnection, 'status' => 'success'
                ]);
}

public function getPractices(ApiRequest $request)
{
    $practice_list=[];
    $user_id=$request->get('user');
    $user=User::where('id', $user_id)->pluck('role_id');
    $user_role=Role::where('id',$user)->pluck('role_name');

    if($user_role[0] == 'Administrator')
    {
        $practice_list=Practice::all();
    }
    else{
        // $get_practice_details=
        $practice_assigned=User_work_profile::where('user_id',$user_id)->get();

        foreach($practice_assigned as $practice)
        {
            $practice_data=Practice::where('id',$practice['practice_id'])->get();

            if($practice_data != null)
            {
                array_push($practice_list,$practice_data[0]);
            }
        }
    }


    //Practice Calculation done Here

    return response()->json([
        'data' => $practice_list
        ]);
}

public function selectPractice(ApiRequest $request)
{
 
    $user_id=$request->get('user_id');
    $practice=$request->get('prac_id');

    $practice_data=Practice::where('id',$practice)->first();

    $dbconnection = new DBConnectionController();
    $practice_assigned=User_work_profile::where('user_id',$user_id)->where('practice_id',$practice)->pluck('role_id');
    
    // $dbconnection->configureConnectionByName($practice_data['id']);
    
    $session= Session::put('practice_dbid', $practice_data['id']);
  
       return response()->json([
        'data' => Session::get('practice_dbid'),
        'role' => $practice_assigned
        ]);
}

}
