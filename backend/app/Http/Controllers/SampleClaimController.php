<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SampleClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['get_sample_claims', 'get_practice_user_list']]);
    }


    public function get_sample_claims(LoginRequest $request)
    {
        dd($request->all());
    }

    public function get_practice_user_list(LoginRequest $request)
    {
        $user_list =array(
            'status' =>204,
            'message' =>'Something Went Wrong'
        );
        try {
            $user_list = User::select('id', 'user_name', 'user_type', 'status')->where('role_id', 1)->get();
            if($user_list)
            {
                $user_details = array('status'=> 200, 'user_list' => $user_list);

                return Response::json($user_details);
            }

        } catch (Exception $e) {
            Log::debug($e->getMessage());
        }
    }



}
