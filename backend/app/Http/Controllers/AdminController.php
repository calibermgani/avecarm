<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ApiRequest;
use App\Customer;
use Log;
use File;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['createVendor','getVendor','updateVendor','getLogs','viewLog']]);
    }

    public function createVendor(ApiRequest $request){
        $user_id=$request->get('user_id');
        $form_data=$request->get('form');
        $form_data['created_by']=$user_id;

        $create = Customer::create(
            $form_data
        );
        return response()->json([
            'data' => $create
            ]);
    }

    public function getVendor()
    {
        $customer = Customer::all();

        return response()->json([
            'data' => $customer
            ]);
    }

    public function updateVendor(ApiRequest $request)
    {
        $user_id=$request->get('user_id');
        $update_data=$request->get('form');
        $update_id=$request->get('upd_id');

        $update=Customer::where('id',$update_id)->update($update_data);
        $customer = Customer::all();


       return response()->json([
        'data' => $customer
        ]);
    }

    public function getLogs()
    {

        $data=[];
        $destinationPath=public_path('../storage/logs');
        // $destinationPath2=public_path('../config/edit_permission.txt');
        // $jsondec = json_decode(file_get_contents($destinationPath) , true); 


        $filesInFolder = \File::files($destinationPath);
$i=0;
foreach($filesInFolder as $path)
{
    $name = pathinfo($path);
    $file_name = $name['filename'];
    $date=explode("laravel-",$file_name);

    $data[$i]['file_name']=$file_name;
    $data[$i]['date']=$date[1];
    $i++;
}


        return response()->json([
            'data' => $data
            ]);
    }

    public function viewLog(ApiRequest $request)
    {
        $file_name=$request->get('file_name');
        $destinationPath=public_path('../storage/logs/'.$file_name.".log");

        $content = file_get_contents($destinationPath);
        return response()->json([
            'data' => $content
            ]);
    }

}
