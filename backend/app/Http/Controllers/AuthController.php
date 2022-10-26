<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\User;
use App\User_work_profile;
use App\Role;
use Validator;
use JWTFactory;
use JWTAuth;
use DB;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','verifytoken','getPermissions'] ]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('user_name', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could not create token'], 500);
        }
        /*
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        */
    

        // $destinationPath=public_path('../config/permission.txt');
        // $destinationPath2=public_path('../config/edit_permission.txt');
        // $jsondec = json_decode(file_get_contents($destinationPath) , true); 
        // $jsondec_edit = json_decode(file_get_contents($destinationPath2) , true); 

        // return $this->respondWithToken($token,$jsondec,$jsondec_edit);

        return $this->respondWithToken($token);
    }

    public function verifytoken(LoginRequest $request)
    {
        $token = JWTAuth::getToken();
        $new_token = JWTAuth::refresh($request->get('token'));
        // $token = JWTAuth::getToken();
        // $token=$token->get('token');

        return $this->respondWithToken($new_token);


        // $destinationPath=public_path('../config/permission.txt');
        // $destinationPath2=public_path('../config/edit_permission.txt');
        // $jsondec = json_decode(file_get_contents($destinationPath) , true); 
        // $jsondec_edit = json_decode(file_get_contents($destinationPath2) , true); 
        
        // $user=Auth()->user();

        // return response()->json([
        //     'message'    => $new_token,
        //     'permission' => $jsondec[$user['role_id']],
        //     'edit_permission' => $jsondec_edit[$user['role_id']]
        //     ]);
    }

    public function getPermissions(LoginRequest $request)
    {
        $user_id=$request->get('id');
        $user;

        $destinationPath=public_path('../config/permission.txt');
        $destinationPath2=public_path('../config/edit_permission.txt');
        $jsondec = json_decode(file_get_contents($destinationPath) , true); 
        $jsondec_edit = json_decode(file_get_contents($destinationPath2) , true); 

        if($request->get('user_role') == 'Admin')
        {
            $user=User::where('id',$user_id)->first(); 
        }
        else{
            $practice = $request->get('practice_id');
 
    
            // if($user_id == 5)
            // {
            //     $modulePermission=;
            //     $moduleEditPermission=$jsondec_edit[$user['role_id']];
            // }
            // else{
             
        $user=User_work_profile::where('user_id',$user_id)->where('practice_id',$practice)->first(); 
        }

    
    
        $role=DB::table('roles')->where('id',$user['role_id'])->pluck('role_name');
        //     $modulePermission=$jsondec[$user['role_id']];
        //     $moduleEditPermission=$jsondec_edit[$user['role_id']];
        // }

      

     return response()->json([
            'permission' => $jsondec[$user['role_id']],
            'edit_permission' => $jsondec_edit[$user['role_id']]
            ]);
    }
    



    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $destinationPath=public_path('../config/permission.txt');
        $destinationPath2=public_path('../config/edit_permission.txt');
        $jsondec = json_decode(file_get_contents($destinationPath) , true); 
        $jsondec_edit = json_decode(file_get_contents($destinationPath2) , true); 

        return $this->respondWithToken(auth()->refresh(),$jsondec,$jsondec_edit);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user=Auth()->user();


        if($user['user_type'] == 'Admin' )
        {
            $role ="Admin";
        }
        else{
            $role ="Practice";
        }
        // $role=DB::table('roles')->where('id',$user['role_id'])->pluck('role_name');


        return response()->json([
            'access_token'       => $token,
            'token_type'         => 'bearer',
            'expires_in'         => auth()->factory()->getTTL() * 60,
            'user'               => Auth()->user(),
            'token'              => JWTAuth::setToken($token),
            // 'permission'         => $jsondec[$user['role_id']],
            // 'edit_permission'    => $jsondec_edit[$user['role_id']],
            'role'               => $role

        ]);
    }
  
}