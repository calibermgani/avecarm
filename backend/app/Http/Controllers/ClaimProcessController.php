<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Address_flag;
use App\Profile;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Validator;
use JWTFactory;
use JWTAuth;
use App\Import_field;
use DB;
use Record_claim_history;
use App\Statuscode;
use App\Workorder_field;
use App\Workorder_user_field;
use App\Action;
use App\Process_note;
use App\Claim_note;
use App\Qc_note;
use App\Client_note;
use App\Claim_history;
use Illuminate\Support\Facades\Log;

class ClaimProcessController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create_followup', 'reassign_calim', 'check_edit_val', 'get_note_details', 'check_notes_update', 'get_claim_status', 'claims_order_list']]);
    }
    public function create_followup(LoginRequest $request)
    {
        // dd($request->all());
        $user_id = $request->get('user_id');
        $status_data = $request->get('status_code');
        $claim_data = $request->get('claim_det');
        // dd($claim_data);
        $followup_type = $request->get('followup_type');
        $claim_status = null;
        $audit_err_code = $request->get('audit_err_code');

        if (($followup_type == "claim_create") || $followup_type == "proess_create") {

            if (@$status_data['closed'] == true) {
                $claim_closed = 1;
                $state = 9;
            } else {
                // if($status_data['status_code']['description'] == "CA-Client Assistances")
                // {
                //     $claim_status="Client Assistance";
                //     $state=7;
                // }
                // else if($status_data['status_code']['description'] == "RCM-RCM Team")
                // {
                //     $claim_status="RCM Team";
                //     $state=8;
                // }
                // else if($status_data['status_code']['description'] == "RCM-Billing Assistance")
                // {
                //     $claim_status="RCM Team";
                //     $state=8;
                // }
                // else if($status_data['status_code']['id']==19)
                // {

                //       $claim_status="Completed";
                // }
                // else{
                $claim_status = "Audit";
                $state = 4;
                //}

                $claim_closed = 0;
            }


            $type = "Followup";

            $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['followup_work_order'])->get();

            $work_order_data_id = $claim_data['followup_work_order'];
        } else if ($followup_type == 'create_qcnotes' || $followup_type == "proess_create" || $followup_type == "qcupdate") {

            if (@$status_data['closed'] == true) {
                $claim_closed = 1;
                $state = 9;

                if ($status_data['status_code']['description'] == "Client Assistance") {
                    $claim_status = "Client Assistance";
                    $claim_status_code = 2;
                    //$state=7;
                } else if ($status_data['status_code']['description'] == "CA-Client Assistance") {
                    $claim_status = "Client Assistance";
                    $claim_status_code = 2;
                    //$state=7;
                } else if ($status_data['status_code']['description'] == "RCM") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    //$state=8;
                } else if ($status_data['status_code']['description'] == "RCM-RCM") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    //$state=8;
                } else if ($status_data['status_code']['description'] == "RCM-RCM Team") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    //$state=8;
                } else if ($status_data['status_code']['description'] == "RCM-Billing Assistance") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    //$state=8;
                } else {
                    //dd($status_data['status_code']['description']);
                    $claim_status = "Audited";
                    $claim_status_code = 0;
                    //$state=6;
                }
            } else {
                if ($status_data['status_code']['description'] == "Client Assistance") {
                    $claim_status = "Client Assistance";
                    $claim_status_code = 2;
                    $state = 7;
                } else if ($status_data['status_code']['description'] == "CA-Client Assistance") {
                    $claim_status = "Client Assistance";
                    $claim_status_code = 2;
                    $state = 7;
                } else if ($status_data['status_code']['description'] == "RCM") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    $state = 8;
                } else if ($status_data['status_code']['description'] == "RCM-RCM") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    $state = 8;
                } else if ($status_data['status_code']['description'] == "RCM-RCM Team") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    $state = 8;
                } else if ($status_data['status_code']['description'] == "RCM-Billing Assistance") {
                    $claim_status = "RCM Team";
                    $claim_status_code = 1;
                    $state = 8;
                } else {
                    //dd($status_data['status_code']['description']);
                    $claim_status = "Audited";
                    $claim_status_code = 0;
                    $state = 6;
                }
                $claim_closed = 0;
            }





            $type = "Audit";

            //dd($claim_data['audit_work_order']);
            if ($claim_data['audit_work_order'] != null) {
                $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['audit_work_order'])->get();
                $work_order_data_id = $claim_data['audit_work_order'];
            } else {
                $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['followup_work_order'])->get();
                $work_order_data_id = $claim_data['followup_work_order'];
            }
            // ->where('user_id', $user_id)

        } else if ($followup_type == 'create_client_notes' || $followup_type == "proess_create") {
            if (@$status_data['closed'] == true) {
                $claim_closed = 1;
                $state = 9;
            } else {
                // $status_code= Statuscode::where('id', $status_data['status_code']['id'])->get();
                // ->where('user_id', $user_id)
                $claim_closed = 0;
            }
            $claim_status = "CA Completed";
            $work_order_data_id = $claim_data['ca_work_order'];
            $state = 3;
            $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['ca_work_order'])->get();
            $type = "CA";
        } else if ($followup_type == 'rcm_create' || $followup_type == "proess_create") {

            if (@$status_data['closed'] == true) {
                $claim_closed = 1;
                $state = 9;
            } else {
                $claim_closed = 0;
            }
            $claim_status = "RCM Completed";
            $state = 3;
            $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['rcm_work_order'])->get();
            // ->where('user_id', $user_id)
            $work_order_data_id = $claim_data['rcm_work_order'];
            $type = "RCM";
        }

        //Completed status code Functionality Def.
        // if($status_data['status_code']['id']==19)
        // {

        //     $claim_status="Completed";
        // }

        if ($claim_closed == 0) {
            if (is_array(@$status_data['followup_date']) == false) {
                $date = @$status_data['followup_date'];
            } else {
                $date = date('Y-m-d', strtotime(@$status_data['followup_date']['day'] . '-' . @$status_data['followup_date']['month'] . '-' . @$status_data['followup_date']['year']));
            }
        }

        if ($type == "Followup") {

            if ($claim_closed == 1) {
                $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                    'claim_Status'          =>  'Closed',
                    // 'status_code'           =>  $status_data['status_code']['id'],
                    // 'substatus_code'        =>  $status_data['sub_status_code'],
                    // 'followup_associate'    =>  '',
                    // 'followup_date'         =>  $date,
                    'claim_closing'         =>  $claim_closed,
                    'updated_at'            =>  date('Y-m-d H:i:s'),
                    'closed_claim_date'     =>  date('Y-m-d')
                ));
            } else {
                $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                    'claim_Status'          =>  $claim_status,
                    'status_code'           =>  $status_data['status_code']['id'],
                    'substatus_code'        =>  $status_data['sub_status_code']['id'],
                    'followup_associate'    =>  $user_id,
                    'followup_date'         =>  $date,
                    'claim_closing'         =>  $claim_closed,
                    'updated_at'            =>  date('Y-m-d H:i:s')
                ));
            }
        } else {
            if ($claim_closed == 1) {
                $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                    'claim_Status'          =>  'Closed',
                    'status_code'           =>  $status_data['status_code']['id'],
                    'substatus_code'        =>  $status_data['sub_status_code']['id'],
                    // 'followup_associate'    =>  $user_id,
                    // 'followup_date'         =>  $date,
                    'claim_closing'         =>  $claim_closed,
                    'updated_at'            =>  date('Y-m-d H:i:s'),
                    'closed_claim_date'     =>  date('Y-m-d')
                ));
            } else {

                // $assigned_to=User::where('user_name', $claim_data['assigned_by'])->first();

                //print_r($assigned_to['role_id']); echo "</br>"; exit();

                // print_r($user_id); echo "</br>";
                // dd($status_data['associates']['id']);

                // print_r($claim_data['followup_associate']); echo "</br>";

                // dd($status_data['associates']['id'] );

                if ($status_data['associates']['id'] == $claim_data['followup_associate']) {
                    $action_type = 2;
                    if ($claim_status_code == 1 || $claim_status_code == 2) {
                        if(isset($audit_err_code) && $audit_err_code != null && $audit_err_code == '4') {         // clarification code this place write,
                            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                                'claim_Status'          =>  'Auditing',
                                'status_code'           =>  $status_data['status_code']['id'],
                                'substatus_code'        =>  $status_data['sub_status_code']['id'],
                                'followup_associate'    =>  $status_data['associates']['id'],
                                'followup_date'         =>  $date,
                                'claim_closing'         =>  $claim_closed,
                                'updated_at'            =>  date('Y-m-d H:i:s'),
                                // 'assigned_to'       => $status_data['associates']['id'],
                            ));
                        }else {
                            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                                'claim_Status'          =>  $claim_status,
                                'status_code'           =>  $status_data['status_code']['id'],
                                'substatus_code'        =>  $status_data['sub_status_code']['id'],
                                'followup_associate'    =>  $status_data['associates']['id'],
                                'followup_date'         =>  $date,
                                'claim_closing'         =>  $claim_closed,
                                'updated_at'            =>  date('Y-m-d H:i:s'),
                                // 'assigned_to'       => $status_data['associates']['id'],
                            ));
                        }

                    } else {
                        if(isset($audit_err_code) && $audit_err_code != null && $audit_err_code == '4') {         // clarification code this place write,
                            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                                'claim_Status'          =>  'Auditing',
                                'status_code'           =>  $status_data['status_code']['id'],
                                'substatus_code'        =>  $status_data['sub_status_code']['id'],
                                'followup_associate'    =>  $status_data['associates']['id'],
                                'followup_date'         =>  $date,
                                'claim_closing'         =>  $claim_closed,
                                'updated_at'            =>  date('Y-m-d H:i:s'),
                                'assigned_to'       => $status_data['associates']['id'],
                            ));
                        }else {
                            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                                'claim_Status'          =>  'Assigned',
                                'status_code'           =>  $status_data['status_code']['id'],
                                'substatus_code'        =>  $status_data['sub_status_code']['id'],
                                'followup_associate'    =>  $status_data['associates']['id'],
                                'followup_date'         =>  $date,
                                'claim_closing'         =>  $claim_closed,
                                'updated_at'            =>  date('Y-m-d H:i:s'),
                                'assigned_to'       => $status_data['associates']['id'],
                            ));
                        }
                    }


                    $action = Action::create([
                        'claim_id'          => $claim_data['claim_no'],
                        'action_id'         => $claim_data['audit_work_order'],
                        'action_type'       => $action_type,
                        'assigned_to'       => $status_data['associates']['id'],
                        'assigned_by'       => $user_id,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'created_by'        => $user_id,
                        'status'            => 'Active'
                    ]);
                } else if ($status_data['associates']['id'] != $claim_data['followup_associate']) {
                    $action_type = 2;
                    $work_order_type = 2;
                    $claim_status = 'Assigned';
                    $claim_state = 5;
                    $users = User::where('role_id', array(4, 3, 2))->pluck('id');
                    $assigned_to = User::where('user_name', $claim_data['assigned_to'])->first();

                    $action = Action::create([
                        'claim_id'          => $claim_data['claim_no'],
                        'action_id'         => $claim_data['audit_work_order'],
                        'action_type'       => $action_type,
                        'assigned_to'       => $status_data['associates']['id'],
                        'assigned_by'       => $user_id,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'created_by'        => $user_id,
                        'status'            => 'Active'
                    ]);

                    //$claim_history_data = [];


                    // $data_history= DB::table('claim_histories')->insert(array(
                    //     "claim_id" => $claim_data['claim_no'], 
                    //     "claim_state" => $claim_state, 
                    //     "assigned_by" => $user_id, 
                    //     "assigned_to" => $status_data['associates']['id']

                    // ));


                    $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                        'claim_Status'          =>  'Assigned',
                        'status_code'           =>  $status_data['status_code']['id'],
                        'substatus_code'        =>  $status_data['sub_status_code']['id'],
                        'followup_associate'    =>  $status_data['associates']['id'],
                        'followup_date'         =>  $date,
                        'claim_closing'         =>  $claim_closed,
                        'assigned_to'           =>  $status_data['associates']['id'],
                        'updated_at'            =>  date('Y-m-d H:i:s')
                    ));

                    // dd($update_claim);


                }
            }
        }

        $claim_history_data[0] = ["claim_no" => $claim_data['claim_no'], "state" => $state, "assigned_by" => $user_id, "assigned_to" => (isset($status_data['associates']['id']) ? $status_data['associates']['id'] : null)];





        $data = Record_claim_history::create_history($claim_history_data);

        // Change Work Order Progress //Manager Working on claims Bug FIx********

        // $work_order_data= Workorder_user_field::where('work_order_id', $claim_data['work_order'])->where('user_id', $user_id)->get();
        //Temp_sol
        $test = $work_order_data;
        // dd($test);
        //Move claims to completed stats
        if (count($work_order_data) != 0 && $user_id == $work_order_data[0]['user_id']) {
            $decoded_value = json_decode($work_order_data[0]['cliam_no'], true);
            $dec_1 = $decoded_value;

            //Checking with Work Order


            if ($dec_1 != Null) {
                if (in_array($claim_data['claim_no'], $dec_1)) {
                    $position = array_search($claim_data['claim_no'], $decoded_value);
                    array_splice($decoded_value, $position, 1);
                    $encoded = json_encode($decoded_value);

                    if ($work_order_data[0]['completed_claim'] == null || $work_order_data[0]['completed_claim'] == '') {
                        $encode_data = [];
                        array_push($encode_data, $claim_data['claim_no']);
                        $complete_encoded = json_encode($encode_data);
                    } else {
                        $completed_decoded = json_decode($work_order_data[0]['completed_claim'], true);
                        array_push($completed_decoded, $claim_data['claim_no']);
                        $complete_encoded = json_encode($completed_decoded);
                    }

                    $update_completed = DB::table('workorder_user_fields')->where('id', $work_order_data[0]['id'])->update(array(
                        'cliam_no'                  =>  $encoded,
                        'completed_claim'           =>  $complete_encoded
                    ));


                    //Change Work Order status

                    $workorder_audit = $claim_data['audit_work_order'];


                    $work_order_details = Workorder_user_field::where('work_order_id', $work_order_data_id)->get();


                    $followup_work_order_data_id = $claim_data['followup_work_order'];

                    $followup_work_order_details = Workorder_user_field::where('work_order_id', $followup_work_order_data_id)->get();


                    //dd($work_order_details);

                    // if($work_order_data_id != $claim_data['followup_work_order']){

                    // }

                    //print_r('work_order_details' . $work_order_details); echo "</br>";
                    $wo_completion = 1;

                    if ($type == "Followup") {

                        // print_r('followup'); echo "</br>";
                        foreach ($work_order_details as $wo_detail) {
                            $decode_values = json_decode($wo_detail['cliam_no']);
                            //print_r($decode_values); echo "</br>";
                            if (count($decode_values) != 0) {
                                //print_r($decode_values); echo "</br>";
                                $wo_completion = 0;
                            }
                        }

                        // if($wo_completion == 1)
                        // {
                        //    // dd('Completed');
                        //     $wo_status="Completed"; 
                        // }
                        // else{
                        //dd('InProgress');
                        $wo_status = "InProgress";
                        // }    
                    } elseif ($claim_closed == 1) {

                        foreach ($work_order_details as $wo_detail) {
                            $decode_values = json_decode($wo_detail['cliam_no']);
                            //print_r($decode_values); echo "</br>";
                            if (count($decode_values) != 0) {
                                //print_r($decode_values); echo "</br>";
                                $wo_completion = 0;
                            }
                        }

                        if ($wo_completion == 1) {
                            // dd('Completed');
                            $wo_status = "Completed";
                        } else {
                            //dd('InProgress');
                            $wo_status = "InProgress";
                        }

                        if (!empty($followup_work_order_details)) {

                            foreach ($followup_work_order_details as $wo_detail) {
                                $decode_values = json_decode($wo_detail['cliam_no']);
                                //print_r($decode_values); echo "</br>";
                                if (count($decode_values) != 0) {
                                    //print_r($decode_values); echo "</br>";
                                    $wo_completion = 0;
                                }
                            }

                            if ($wo_completion == 1) {
                                //print_r('Completed'); echo "</br>";
                                $wo_status = "Completed";
                            } else {
                                // print_r('InProgress'); echo "</br>";
                                $wo_status = "InProgress";
                            }

                            Workorder_field::where('id', $followup_work_order_data_id)
                                ->update(['status' => $wo_status]);
                        }
                    } elseif ($claim_closed == 0) {
                        foreach ($work_order_details as $wo_detail) {
                            $decode_values = json_decode($wo_detail['cliam_no']);
                            //print_r($decode_values); echo "</br>";
                            if (count($decode_values) != 0) {
                                //print_r($decode_values); echo "</br>";
                                $wo_completion = 0;
                            }
                        }

                        if ($wo_completion == 1) {
                            // dd('Completed');
                            $wo_status = "Completed";
                        } else {
                            //dd('InProgress');
                            $wo_status = "InProgress";
                        }

                        if (!empty($followup_work_order_details)) {

                            foreach ($followup_work_order_details as $wo_detail) {
                                $decode_values = json_decode($wo_detail['cliam_no']);
                                //print_r($decode_values); echo "</br>";
                                if (count($decode_values) != 0) {
                                    //print_r($decode_values); echo "</br>";
                                    $wo_completion = 0;
                                }
                            }

                            if ($wo_completion == 1) {
                                //print_r('Completed'); echo "</br>";
                                $wo_status = "Completed";
                            } else {
                                // print_r('InProgress'); echo "</br>";
                                $wo_status = "InProgress";
                            }

                            Workorder_field::where('id', $followup_work_order_data_id)
                                ->update(['status' => $wo_status]);
                        }
                    }

                    Workorder_field::where('id', $work_order_data_id)
                        ->update(['status' => $wo_status]);
                }
            }
        } else {
            //Auto assign to manager/TL
            if ($type == 'Followup') {
                $action_type = 1;
                $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['followup_work_order'])->get();

                if (count($work_order_data) != 0) {
                    $decoded_value = json_decode($work_order_data[0]['cliam_no'], true);
                    $dec_1 = $decoded_value;
                    //Checking with Work Order
                    if (sizeof($dec_1) != 0) {
                        // foreach($dec_1 as $claim_no)
                        // {
                        if (in_array($claim_data['claim_no'], $dec_1)) {

                            $this->wo_modify($decoded_value, $claim_data, $work_order_data, $user_id, 'complete');
                        }
                        // }

                    }
                }

                // $update_claim_data=DB::table('import_fields')->where('claim_no',$claim_data['claim_no'])->update(array(
                //     // 'assigned_to'           =>  $assigned_data[0]['assigned_by'],
                //     'followup_work_order'   =>  NULL,
                //     'updated_at'            =>  date('Y-m-d H:i:s')
                //     ));
            } else if ($type == 'Audit') {
                $action_type = 2;
                $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['audit_work_order'])->where('user_id', $user_id)->get();
                if (count($work_order_data) != 0) {
                    $decoded_value = json_decode($work_order_data[0]['cliam_no'], true);
                    $dec_1 = $decoded_value;
                    //Checking with Work Order
                    if ($dec_1 != Null) {
                        if (in_array($claim_data['claim_no'], $dec_1)) {
                            $this->wo_audit_update($decoded_value, $claim_data, $work_order_data, $user_id);
                        }
                    }
                }


                // $update_claim_data=DB::table('import_fields')->where('claim_no',$claim_data['claim_no'])->update(array(
                //     // 'assigned_to'           =>  $assigned_data[0]['assigned_by'],
                //     'audit_work_order'      =>  NULL,
                //     'updated_at'            =>  date('Y-m-d H:i:s')
                //     ));
            } else if ($type == 'ca') {
                $claim_state = 5;
            } else if ($type == 'rcm') {
                $claim_state = 8;
            }

            if (empty($work_order_data)) {
                $update_claim_data = DB::table('actions')->where('claim_id', $claim_data['claim_no'])->where('status', 'Active')->update(array(
                    'status'          =>  'Active'
                ));
            } else {
                $update_claim_data = DB::table('actions')->where('claim_id', $claim_data['claim_no'])->where('status', 'Active')->update(array(
                    'status'          =>  'Inactive'
                ));
            }
            if (empty($work_order_data)) {
                $action = Action::create(
                    [
                        'claim_id'          => $claim_data['claim_no'],
                        'action_id'         => $work_order_data[0]['work_order_id'],
                        'action_type'       => $action_type,
                        'assigned_to'       => $user_id,
                        'assigned_by'       => $user_id,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'created_by'        => $user_id,
                        'status'            => 'Active'
                    ]
                );
            } else {
                $action = Action::create(
                    [
                        'claim_id'          => $claim_data['claim_no'],
                        'action_id'         => 1,
                        'action_type'       => $action_type,
                        'assigned_to'       => $claim_data['followup_associate'],
                        'assigned_by'       => $user_id,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'created_by'        => $user_id,
                        'status'            => 'Active'
                    ]
                );
            }
        }


        //                 else{
        //                    if($type=="Audit")
        //                    {

        //                     $update_claim=DB::table('import_fields')->where('claim_no',$claim_data['claim_no'])->update(array(
        //                         'audit_work_order'      =>  NULL,
        //                         'updated_at'            =>  date('Y-m-d H:i:s')
        //                         ));                      }

        //                         $update_claim=DB::table('actions')->where('claim_id',$claim_data['claim_no'])->update(array(
        //                             'status'          =>  'Inactive'
        //                             ));


        //  $action=Action::create(
        //     [
        //         'claim_id'          => $claim_data['claim_no'],
        //         'action_type'       => NULL,
        //         'assigned_to'       => $user_id,
        //         'assigned_by'       => $user_id, 
        //         'created_at'        => date('Y-m-d H:i:s'),
        //         'created_by'        => $user_id,
        //         'status'            => 'Active'
        //         ]);

        //                 }


        return response()->json([
            'message'   => $update_claim,
            'type'      => $type,
            'clm'       => $test
        ]);
    }

    public function reassign_calim(LoginRequest $request)
    {
        $user_id = $request->get('user_id');
        $claim_data = $request->get('claim_data');
        $type = $request->get('type');
        $assigned_data = Action::where('assigned_to', $user_id)->where('status', 'Active')->get();

        if ($type == 'followup') {
            $action_type = 1;
            $claim_state = 3;
            $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['followup_work_order'])->where('user_id', $user_id)->get();
            $decoded_value = json_decode($work_order_data[0]['cliam_no'], true);
            $dec_1 = $decoded_value;
            //Checking with Work Order
            if ($dec_1 != Null) {
                if (in_array($claim_data['claim_no'], $dec_1)) {
                    $this->wo_modify(
                        $decoded_value,
                        $claim_data,
                        $work_order_data,
                        $assigned_data[0]['assigned_by'],
                        'reassign'
                    );
                }
            }

            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                'assigned_to'           =>  $assigned_data[0]['assigned_by'],
                // 'followup_work_order'   =>  NULL,
                'updated_at'            =>  date('Y-m-d H:i:s')
            ));
        } else if ($type == 'audit') {
            $action_type = 2;
            $claim_state = 5;
            $work_order_data = Workorder_user_field::where('work_order_id', $claim_data['audit_work_order'])->where('user_id', $user_id)->get();
            $decoded_value = json_decode($work_order_data[0]['cliam_no'], true);
            $dec_1 = $decoded_value;
            //Checking with Work Order
            if ($dec_1 != Null) {
                if (in_array($claim_data['claim_no'], $dec_1)) {
                    $this->wo_audit_update($decoded_value, $claim_data, $work_order_data, $assigned_data[0]['assigned_by']);
                }
            }

            $update_claim = DB::table('import_fields')->where('claim_no', $claim_data['claim_no'])->update(array(
                'assigned_to'           =>  $assigned_data[0]['assigned_by'],
                // 'audit_work_order'      =>  NULL,
                'updated_at'            =>  date('Y-m-d H:i:s')
            ));
        } else if ($type == 'ca') {
            $claim_state = 5;
        } else if ($type == 'rcm') {
            $claim_state = 8;
        }
        $claim_history_data[0] = ["claim_no" => $claim_data['claim_no'], "state" => $claim_state, "assigned_by" => $user_id, "assigned_to" => $assigned_data[0]['assigned_by']];
        $data = Record_claim_history::create_history($claim_history_data);

        $update_claim = DB::table('actions')->where('claim_id', $claim_data['claim_no'])->where('assigned_to', $user_id)->update(array(
            'status'          =>  'Inactive'
        ));

        $action = Action::create(
            [
                'claim_id'          => $claim_data['claim_no'],
                'action_id'           => $work_order_data[0]['work_order_id'],
                'action_type'       => $action_type,
                'assigned_to'       => $assigned_data[0]['assigned_by'],
                'assigned_by'       => $user_id,
                'created_at'        => date('Y-m-d H:i:s'),
                'created_by'        => $user_id,
                'status'            => 'Active'
            ]
        );

        return response()->json([
            'message' => $update_claim
        ]);
    }


    private function wo_audit_update($decoded_value, $claim_data, $work_order_data, $user_id)
    {
        $type = "reassign";
        $position = array_search($claim_data['claim_no'], $decoded_value);
        array_splice($decoded_value, $position, 1);
        $encoded = json_encode($decoded_value);

        if ($work_order_data[0]['completed_claim'] == null || $work_order_data[0]['completed_claim'] == '') {
            $encode_data = [];
            array_push($encode_data, $claim_data['claim_no']);
            $complete_encoded = json_encode($encode_data);
        } else {
            $completed_decoded = json_decode($work_order_data[0]['completed_claim'], true);
            array_push($completed_decoded, $claim_data['claim_no']);
            $complete_encoded = json_encode($completed_decoded);
        }

        // $update_completed=DB::table('workorder_user_fields')->where('id',$work_order_data[0]['id'])->update(array(
        //     'cliam_no'                  =>  $encoded,
        //     'completed_claim'           =>  $complete_encoded
        //     )); 


        // Check if the workorder is already worked by TL/Manager

        $wo_details_count = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->where('user_id', $user_id)->count();

        if ($wo_details_count != 0) {
            $wo_details = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->where('user_id', $user_id)->get();

            if ($type == 'reassign') {

                $decode = json_decode($wo_details[0]['cliam_no'], true);
                array_push($decode, $claim_data['claim_no']);
                $update_completed = DB::table('workorder_user_fields')->where('id', $wo_details[0]['id'])->update(array(
                    'cliam_no'           =>  json_encode($decode)
                ));
            } else if ($type == 'complete') {
                $decode = json_decode($wo_details[0]['completed_claim'], true);
                array_push($decode, $claim_data['claim_no']);

                $update_completed = DB::table('workorder_user_fields')->where('id', $wo_details[0]['id'])->update(array(
                    'completed_claim'           =>  json_encode($decode)
                ));
            }
        } else {
            $clais = [];
            array_push($clais, $claim_data['claim_no']);

            if ($type == 'reassign') {
                $create_record = Workorder_user_field::create(
                    [
                        'work_order_id'              => $work_order_data[0]['work_order_id'],
                        'user_id'                    => $user_id,
                        'cliam_no'                   => json_encode($clais),
                        'completed_claim'            => '[]'
                    ]
                );
            } else if ($type == 'complete') {
                $create_record = Workorder_user_field::create(
                    [
                        'work_order_id'              => $work_order_data[0]['work_order_id'],
                        'user_id'                    => $user_id,
                        'cliam_no'                   => '[]',
                        'completed_claim'            => json_encode($clais)
                    ]
                );
            }
        }



        //Change Work Order status

        $work_order_details = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->get();
        // dd($work_order_details);
        $wo_completion = 1;

        foreach ($work_order_details as $wo_detail) {
            $decode_values = json_decode($wo_detail['cliam_no']);
            if (count($decode_values) != 0) {
                $wo_completion = 0;
            }
        }

        if ($wo_completion == 1) {
            $wo_status = "Completed";
        } else {
            $wo_status = "InProgress";
        }


        $update = Workorder_field::where('id', $work_order_data)
            ->update(['status' => $wo_status]);


        return $update;
    }



    private function wo_modify($decoded_value, $claim_data, $work_order_data, $user_id, $type)
    {
        // dd($decoded_value,$claim_data,$work_order_data,$user_id,$type);
        $position = array_search($claim_data['claim_no'], $decoded_value);
        $spliced = array_splice($decoded_value, $position, 1);
        // dd($spliced);
        $encoded = json_encode($decoded_value);
        $encode_spliced = json_encode($spliced);

        if ($work_order_data[0]['completed_claim'] == null || $work_order_data[0]['completed_claim'] == '') {
            $encode_data = [];
            array_push($encode_data, $claim_data['claim_no']);
            $complete_encoded = json_encode($encode_data);
        } else {
            $completed_decoded = json_decode($work_order_data[0]['completed_claim'], true);
            array_push($completed_decoded, $claim_data['claim_no']);
            $complete_encoded = json_encode($completed_decoded);
        }

        $update_completed = DB::table('workorder_user_fields')->where('id', $work_order_data[0]['id'])->update(array(
            'cliam_no'                  =>  $encoded
        ));

        //Check if the workorder is already worked by TL/Manager
        $wo_details_count = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->where('user_id', $user_id)->count();


        if ($wo_details_count != 0) {
            $wo_details = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->where('user_id', $user_id)->get();

            if ($type == 'reassign') {

                $decode = json_decode($wo_details[0]['cliam_no'], true);
                array_push($decode, $claim_data['claim_no']);
                $update_completed = DB::table('workorder_user_fields')->where('id', $wo_details[0]['id'])->update(array(
                    'cliam_no'           =>  json_encode($decode)
                ));
            } else if ($type == 'complete') {
                $decode = json_decode($wo_details[0]['completed_claim'], true);
                array_push($decode, $claim_data['claim_no']);

                $update_completed = DB::table('workorder_user_fields')->where('id', $wo_details[0]['id'])->update(array(
                    'completed_claim'           =>  json_encode($decode)
                ));
            }
        } else {
            $clais = [];
            array_push($clais, $claim_data['claim_no']);

            if ($type == 'reassign') {
                $create_record = Workorder_user_field::create(
                    [
                        'work_order_id'              => $work_order_data[0]['work_order_id'],
                        'user_id'                    => $user_id,
                        'cliam_no'                   => json_encode($clais),
                        'completed_claim'            => '[]'
                    ]
                );
            } else if ($type == 'complete') {
                $create_record = Workorder_user_field::create(
                    [
                        'work_order_id'              => $work_order_data[0]['work_order_id'],
                        'user_id'                    => $user_id,
                        'cliam_no'                   => '[]',
                        'completed_claim'            => json_encode($clais)
                    ]
                );
            }
        }





        //Change Work Order status

        $work_order_details = Workorder_user_field::where('work_order_id', $work_order_data[0]['work_order_id'])->get();

        $wo_completion = 1;

        foreach ($work_order_details as $wo_detail) {
            $decode_values = json_decode($wo_detail['cliam_no']);
            if (count($decode_values) != 0) {
                $wo_completion = 0;
            }
        }

        if ($wo_completion == 1) {
            $wo_status = "Completed";
        } else {
            $wo_status = "InProgress";
        }


        $update = Workorder_field::where('id', $work_order_data[0]['work_order_id'])
            ->update(['status' => $wo_status]);


        return $update;
    }














    public function check_edit_val(LoginRequest $request)
    {
        $claim = $request->get('claim_data');
        $type = $request->get('type');
        $edit_validity = false;

        if ($type == 'followup') {
            $curr_data = Claim_note::where('claim_id', $claim['claim_no'])->orderBy('created_at', 'desc')->first();
            if ($curr_data) {
                $comapare_data = Qc_note::where('claim_id', $claim['claim_no'])->where('created_at', '>', $curr_data['created_at'])->get();

                if (count($comapare_data) == 0) {
                    $edit_validity = true;
                }
            }
            //dd($claim);
        } elseif ($type == 'audit') {
            $curr_data = Qc_note::where('claim_id', $claim['claim_no'])->orderBy('created_at', 'desc')->first();

            if ($curr_data) {
                $comapare_data = Claim_note::where('claim_id', $claim['claim_no'])->where('created_at', '>', $curr_data['created_at'])->get();

                if (count($comapare_data) == 0) {
                    $edit_validity = true;
                }
            }
        }
        return response()->json([
            'edit_val'  =>  $edit_validity,
            'note_id'   =>  $curr_data,
            'claims'    =>  $claim
        ]);
    }

    public function check_notes_update(LoginRequest $request)
    {
        $claim = $request->get('claim');
        $type = $request->get('note_type');
        $note_data = $request->get('note_data');

        $is_updated = false;
        $count_change = false;

        // if($type=='claim_notes')
        // {
        $qc_note_count = Qc_note::where('claim_id', $claim['claim_no'])->count();
        if ($qc_note_count == @$note_data['qc_note']['count']) {
            $qc_note = Qc_note::where('id', @$note_data['qc_note']['data']['id'])->where('updated_at', @$note_data['qc_note']['data']['updated_at'])->count();
            if ($qc_note == 0 && $qc_note_count != 0) {
                $is_updated = true;
            }
        } else {
            $is_updated = true;
            $count_change = true;
        }
        // }
        // else if($type=='qc_notes')
        // {

        if ($is_updated == false) {
            $claim_note_count = Claim_note::where('claim_id', $claim['claim_no'])->count();
            if ($claim_note_count == @$note_data['claim_note']['count']) {
                $claim_note = Claim_note::where('id', @$note_data['claim_note']['data']['id'])->where('updated_at', @$note_data['claim_note']['data']['updated_at'])->count();
                if ($claim_note == 0 && $claim_note_count != 0) {
                    $is_updated = true;
                }
            } else {
                $is_updated = true;
                $count_change = true;
            }
        }

        if ($is_updated == false) {
            $process_note_count = Process_note::where('claim_id', $claim['claim_no'])->count();
            if ($process_note_count == @$note_data['process']['count']) {
                $process_note = Process_note::where('id', @$note_data['process']['data']['id'])->where('updated_at', @$note_data['process']['data']['updated_at'])->count();
                if ($process_note == 0 && $process_note_count != 0) {
                    $is_updated = true;
                }
            } else {
                $is_updated = true;
                $count_change = true;
            }
        }


        if ($is_updated == false) {
            $client_note_count = Client_note::where('claim_id', $claim['claim_no'])->count();
            if ($client_note_count == @$note_data['client']['count']) {
                $client_note = Client_note::where('id', @$note_data['client']['data']['id'])->where('updated_at', @$note_data['client']['data']['updated_at'])->count();
                if ($client_note == 0 && $client_note_count != 0) {
                    $is_updated = true;
                }
            } else {
                $is_updated = true;
                $count_change = true;
            }
        }



        // }


        return response()->json([
            'updated'  =>  $is_updated,
            'cc'       =>  $count_change
        ]);
    }

    public function get_note_details(LoginRequest $request)
    {
        $claim = $request->get('claim');

        $claim_note['count'] = Claim_note::where('claim_id', $claim)->count();
        $claim_note['data'] = Claim_note::where('claim_id', $claim)->orderBy('created_at', 'desc')->first();

        $qc_note['count'] = Qc_note::where('claim_id', $claim)->count();
        $qc_note['data'] = Qc_note::where('claim_id', $claim)->orderBy('created_at', 'desc')->first();

        $process['count'] = Process_note::where('claim_id', $claim)->count();
        $process['data'] = Process_note::where('claim_id', $claim)->orderBy('created_at', 'desc')->first();

        $client['count'] = Client_note::where('claim_id', $claim)->count();
        $client['data'] = Client_note::where('claim_id', $claim)->orderBy('created_at', 'desc')->first();


        return response()->json([
            'claim_note'  =>  $claim_note,
            'qc_note'     =>  $qc_note,
            'process'     =>  $process,
            'client'      =>  $client
        ]);
    }

    public function get_claim_status(LoginRequest $request)
    {
        $claim = $request->get('claim');
        $claim_change = false;
        $claim_count = Import_field::where('claim_no', $claim['claim_no'])->where('updated_at', $claim['updated_at'])->count();

        if ($claim_count == 0) {
            $claim_change = true;
        }
        return response()->json([
            'status'  =>  $claim_change
        ]);
    }


    public function claims_order_list(LoginRequest $request)
    {
    }
}
