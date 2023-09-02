<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Validator;
use JWTFactory;
use JWTAuth;
use Config;
use App\User;
use App\Import_field;
use App\Qc_note;
use Carbon\Carbon;
use App\Claim_note;
use App\Action;
use Illuminate\Support\Facades\DB;
use Record_claim_history;
use App\Statuscode;
use App\Sub_statuscode;
use App\Error_type;
use App\Root_cause;
use App\User_work_profile;
use DateTime;
use App\Claim_history;
use App\Workorder_field;
use App\Workorder_user_field;
use App\Models\ErrorParameter;
use App\Models\FYIParameter;
use App\Models\ParentParameter;
use App\Models\SubParameter;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;



class AuditController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['get_audit_claim_details', 'get_auditors', 'create_audit_workorder', 'fetch_export_data', 'get_audit_codes', 'auto_assign_claims', 'audit_assigned_order_list', 'auto_assigned', 'get_error_param_codes', 'get_fyi_param_codes', 'get_sub_error_param_codes', 'change_auditor']]);
  }

  //Get Audit Claim Details
  public function get_audit_claim_details(LoginRequest $request)
  {
    $user_id = $request->get('user_id');
    $page_no = $request->get('page_no');
    $page_count = $request->get('count');
    $claim_type = $request->get('type');
    $sort_type = $request->get('sort_type');
    //dd($sort_type); // claim_no
    $sort_data = $request->get('sort_code');
    //dd($sort_data); //false

    $sorting_name = $request->get('sorting_name');
    $sorting_method = $request->get('sorting_method');

    $aignSearchValue = $request->get('assign_claim_searh');
    $closedSearchValue = $request->get('closed_claim_searh');

    if ($closedSearchValue != null) {
      $search_acc_no = $closedSearchValue['acc_no'];
      $search_claim_no = $closedSearchValue['claim_no'];
      $search_claim_note = $closedSearchValue['claim_note'];
      $search_dos = $closedSearchValue['dos'];
      $search_insurance = $closedSearchValue['insurance'];
      $search_patient_name = $closedSearchValue['patient_name'];
      $search_prim_ins_name = $closedSearchValue['prim_ins_name'];
      $search_prim_pol_id = $closedSearchValue['prim_pol_id'];
      $search_sec_ins_name = $closedSearchValue['sec_ins_name'];
      $search_sec_pol_id = $closedSearchValue['sec_pol_id'];
      $search_ter_ins_name = $closedSearchValue['ter_ins_name'];
      $search_ter_pol_id = $closedSearchValue['ter_pol_id'];
      $search_total_ar = $closedSearchValue['total_ar'];
      $search_total_charge = $closedSearchValue['total_charge'];
      $search_status_code = $closedSearchValue['status_code'];
      $search_rendering_provider = $closedSearchValue['rendering_provider'];
      $search_denial_code = $closedSearchValue['denial_code'];
      $search_bill_submit_date = $closedSearchValue['bill_submit_date'];
    }


    if ($aignSearchValue != null) {
      $search_acc_no = $aignSearchValue['acc_no'];
      $search_claim_no = $aignSearchValue['claim_no'];
      $search_claim_note = $aignSearchValue['claim_note'];
      $search_dos = $aignSearchValue['dos'];
      $search_insurance = $aignSearchValue['insurance'];
      $search_patient_name = $aignSearchValue['patient_name'];
      $search_prim_ins_name = $aignSearchValue['prim_ins_name'];
      $search_prim_pol_id = $aignSearchValue['prim_pol_id'];
      $search_sec_ins_name = $aignSearchValue['sec_ins_name'];
      $search_sec_pol_id = $aignSearchValue['sec_pol_id'];
      $search_ter_ins_name = $aignSearchValue['ter_ins_name'];
      $search_ter_pol_id = $aignSearchValue['ter_pol_id'];
      $search_total_ar = $aignSearchValue['total_ar'];
      $search_total_charge = $aignSearchValue['total_charge'];
      $search_status_code = $aignSearchValue['status_code'];
      $search_rendering_provider = $aignSearchValue['rendering_provider'];
      $search_denial_code = $aignSearchValue['denial_code'];
      $search_bill_submit_date = $aignSearchValue['bill_submit_date'];
      $search_responsibility = $aignSearchValue['responsibility'];
      $search_date = $aignSearchValue['date'];
      $search_age = $aignSearchValue['age_filter'];
    }

    //search_claim_no

    $search = $request->get('search');

    $total_count = 0;
    $skip = ($page_no - 1) * $page_count;
    $end = $page_count;

    $op_data = [];
    $present_data = Config::get('fields.data');
    $field_data = [];
    foreach ($present_data as $key => $value) {
      $field_data[$value] = $key;
    }
    $op_data['fields'] = $field_data;
    $user_role = User::where('id', $user_id)->first();
    $claim_count = 0;
    /* if($user_role['role_id'] == 5 || $user_role['role_id'] == 3 || $user_role['role_id'] == 2)
        { */


    $worked = [];
    $pending = [];
    $clams = [];
    $assign = [];
    $closed = [];

    $claims = Action::where('assigned_to', $user_id)->where('action_type', 2)->where('status', 'Active')->get();
    //dd($claims);
    foreach ($claims as $active) {
      $date = date('Y-m-d', strtotime($active['created_at']));



      $allocated = Claim_history::where('claim_id', $active['claim_id'])->whereIN('claim_state', [4, 6, 7, 8, 9])->where('created_at', '>=', $date)->count();

      if ($allocated > 0) {
        array_push($worked, $active['claim_id']);
      } else {
        array_push($pending, $active['claim_id']);
      }
      // ->orderBy('created_at', 'desc')->distinct('claim_id')
    }




    if ($claim_type == "wo") {
      // dd('datas1');
      $users = User::where('role_id', '4')->pluck('id');
      $claims = Action::whereIN('assigned_to', $users)->distinct('claim_id')->pluck('claim_id');

      // $claim_count= Action::whereIN('assigned_to', $users)->distinct('claim_id')->pluck('claim_id');
      // $claim_count=sizeof($claim_count);
      // $claim_data= Import_field::whereIN('claim_no',$claims)->orwhere('claim_Status','Audit')->where('claim_Status','!=','Completed')->distinct('claim_no')->offset($skip)->limit($end)->get();\
      // $claim_count=Import_field::whereIN('claim_no',$claims)->orwhere('claim_Status','Audit')->where('claim_Status','!=','Completed')->distinct('claim_no')->count();
      $claim_data = Import_field::where(function ($query) use ($claims) {
        $query->whereIN('claim_no', $claims)
          ->orwhere('claim_Status', 'Audit')
          ->orwhere('claim_Status', 'RCM Completed')
          ->orwhere('claim_Status', 'CA Completed')
          ->orwhere('claim_Status', 'CA Assigned')
          ->orwhere('claim_Status', 'Closed');
      })->distinct('claim_no')->offset($skip)->limit($end)->get();

      foreach ($claim_data as $key => $claim_datas) {
        $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
        $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

        $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
        $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
      }

      // ->where('claim_Status','!=','Completed')

      $claim_count = Import_field::where(function ($query) use ($claims) {
        $query->whereIN('claim_no', $claims)
          ->orwhere('claim_Status', 'Audit')
          ->orwhere('claim_Status', 'RCM Completed')
          ->orwhere('claim_Status', 'CA Completed')
          ->orwhere('claim_Status', 'CA Assigned')
          ->orwhere('claim_Status', 'Closed');
      })->distinct('claim_no')->count();
      // ->where('claim_Status','!=','Completed')

    } else if ($claim_type == "completed") {
      // dd('datas2');
      $claimInfo = Claim_history::orderBy('id', 'desc')->get()->unique('claim_id')->toArray();

      foreach ($claimInfo as $claimList) {

        if (isset($claimList) && $claimList['claim_state'] == 9 &&  $claimList['assigned_by'] == $user_id)
          array_push($closed, $claimList['claim_id']);
      }

      if ($closedSearchValue == null) {

        if ($claim_type == "completed"  && $sort_type == null && $sorting_name == null) {
          $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
          })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
          })->whereIN('claim_no', $closed)->where('claim_closing', 1)->offset($skip)->limit($end)->get();

          /** Developer : Sathish
           *  Date : 29/12/2022
           *  Purpose : To get Status and Sub Status Code
           */
          foreach ($claim_data as $key => $claim_datas) {
            $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
            $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

            $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
            $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
          }
          /** End */

          $current_total = $claim_data->count();
        } elseif ($claim_type == "completed"  && $sort_type == 'null' && $sorting_name == 'null') {
          $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
          })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
          })->whereIN('claim_no', $closed)->where('claim_closing', 1)->offset($skip)->limit($end)->get();
          foreach ($claim_data as $key => $claim_datas) {
            $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
            $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

            $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
            $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
          }

          $current_total = $claim_data->count();
        } elseif ($claim_type == "completed"  && $sort_type == 'null' && $sorting_method == true && empty($sorting_name)) {
          $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
          })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
          })->whereIN('claim_no', $closed)->where('claim_closing', 1)->offset($skip)->limit($end)->get();

          /** Developer : Sathish
           *  Date : 29/12/2022
           *  Purpose : To get Status and Sub Status Code
           */
          foreach ($claim_data as $key => $claim_datas) {
            $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
            $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

            $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
            $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
          }
          /** End */

          $current_total = $claim_data->count();
        }

        if ($claim_type == "completed"  && $sort_type == null && $sorting_name != 'null' && !empty($sorting_name)) {

          if ($sorting_method == true) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                                claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                              AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
              $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
            })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
              $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
            })->whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end)->get();

            /** Developer : Sathish
             *  Date : 29/12/2022
             *  Purpose : To get Status and Sub Status Code
             */
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            /** End */

            $current_total = $claim_data->count();
          } else if ($sorting_method == false) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                                claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                              AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
              $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
            })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
              $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
            })->whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end)->get();

            /** Developer : Sathish
             *  Date : 29/12/2022
             *  Purpose : To get Status and Sub Status Code
             */
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            /** End */

            $current_total = $claim_data->count();
          }
        }



        $selected_claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->get();

        if ($sort_type != 'null' && $sorting_name == 'null') {
          if ($sort_data == true) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                                claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                              AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
              $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
            })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
              $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
            })->whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy($sort_type, 'desc')->offset($skip)->limit($end)->get();

            /** Developer : Sathish
             *  Date : 29/12/2022
             *  Purpose : To get Status and Sub Status Code
             */
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            /** End */

            $current_total = $claim_data->count();
          } else if ($sort_data == false) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                                claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                              AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
              $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
            })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
              $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
            })->whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy($sort_type, 'asc')->offset($skip)->limit($end)->get();

            /** Developer : Sathish
             *  Date : 29/12/2022
             *  Purpose : To get Status and Sub Status Code
             */
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            /** End */
            $current_total = $claim_data->count();
          }
        }

        $claim_count = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->count();
      }

      if ($closedSearchValue != null) {

        $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                          claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        })->whereIN('claim_no', $closed)->where('claim_closing', 1);


        $claim_count = Import_field::leftjoin(DB::raw("(SELECT
                          claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        })->whereIN('claim_no', $closed)->where('claim_closing', 1);

        $selected_claim_data = Import_field::leftjoin(DB::raw("(SELECT
                          claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                              claim_histories.claim_id,claim_histories.created_at as created_ats
                            FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                            ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        })->whereIN('claim_no', $closed)->where('claim_closing', 1);


        if (!empty($search_claim_no)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('claim_no', $search_claim_no)->offset($skip)->limit($end);


            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('claim_no', $search_claim_no)->offset($skip)->limit($end);


            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); claim_no sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }
        }

        if (!empty($search_dos) && $search_dos['startDate'] != null) {

          $closed_sart_date = date('Y-m-d', strtotime($search_dos['startDate']));
          $closed_end_date = date('Y-m-d', strtotime($search_dos['endDate']));

          if ($closed_sart_date == $closed_end_date) {
            $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
            $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate'] . "+ 1 day"));
          } elseif ($closed_sart_date != $closed_end_date) {
            $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
            $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate']));
          }

          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);


            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);


            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); dos sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }
        }

        if (!empty($search_acc_no)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);


            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);


            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); acct_no sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }
        }

        if (!empty($search_patient_name)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); patient_name sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }
        }

        if (!empty($search_total_charge)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('total_charges', '=', $search_total_charge)->offset($skip)->limit($end);


            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('total_charges', '=', $search_total_charge)->offset($skip)->limit($end);


            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); total_charges sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }
        }

        if (!empty($search_total_ar)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('total_ar', '=', $search_total_ar)->offset($skip)->limit($end);


            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('total_ar', '=', $search_total_ar)->offset($skip)->limit($end);


            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); search_total_ar sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('total_ar', '=', $search_total_ar)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_ar', '=', $search_total_ar);

            $selected_claim_data->where('total_ar', '=', $search_total_ar);
          }
        }

        if (!empty($search_date) && $search_date['startDate'] != null) {
          $closed_sart_date = date('Y-m-d', strtotime($search_date['startDate']));
          $closed_end_date = date('Y-m-d', strtotime($search_date['endDate']));

          if ($closed_sart_date == $closed_end_date) {
            $created_start_date = date('Y-m-d', strtotime($search_date['startDate'] . "+ 1 day"));
            $created_end_date = date('Y-m-d', strtotime($search_date['endDate'] . "+ 1 day"));
          } elseif ($closed_sart_date != $closed_end_date) {
            $created_start_date = date('Y-m-d', strtotime($search_date['startDate'] . "+ 1 day"));
            $created_end_date = date('Y-m-d', strtotime($search_date['endDate']));
          }

          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }
        }

        if (!empty($search_claim_note)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->offset($skip)->limit($end);


            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->offset($skip)->limit($end);


            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); claim_note sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }
        }

        if (!empty($search_prim_ins_name)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); prim_ins_name sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }
        }

        if (!empty($search_prim_pol_id)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); prim_pol_id sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }
        }

        if (!empty($search_sec_ins_name)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); sec_ins_name sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }
        }

        if (!empty($search_sec_pol_id)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); sec_pol_id sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }
        }

        if (!empty($search_ter_ins_name)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); ter_ins_name sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }
        }

        if (!empty($search_status_code) && $search_status_code['id'] != null) {
          $status_code = $search_status_code['id'];
          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('status_code', $status_code)->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }
        }

        if (!empty($search_denial_code)) {
          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('denial_code', $search_denial_code)->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }
        }

        if (!empty($search_bill_submit_date) && $search_bill_submit_date['startDate'] != null) {

          $assign_sart_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate']));
          $assign_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate']));

          if ($assign_sart_date == $assign_end_date) {
            $bill_start_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate'] . "+ 1 day"));
            $bill_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate'] . "+ 1 day"));
          } elseif ($assign_sart_date != $assign_end_date) {
            $bill_start_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate'] . "+ 1 day"));
            $bill_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate']));
          }

          if ($sort_type == null && $sort_data == null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }
        }

        if (!empty($search_ter_pol_id)) {


          if ($sort_type == null && $sort_data == null && empty($sorting_name)) {

            //dd('prasath');

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }


          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {


            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); ter_pol_id sort_code

          if ($sorting_method == true && $sort_data == null && $search == 'search' && $sort_type == null && !empty($sorting_name)) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }
        } else {
          if (!empty($sorting_name)) {

            if ($sorting_method == true) {
              $claim_data->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->orderBy($sorting_name, 'desc');
              $selected_claim_data->orderBy($sorting_name, 'desc');
            } else if ($sorting_method == false) {
              $claim_data->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->orderBy($sorting_name, 'asc');
              $selected_claim_data->orderBy($sorting_name, 'asc');
            }
          }
        }

        $claim_data = $claim_data->get();

        /** Developer : Sathish
         *  Date : 29/12/2022
         *  Purpose : To get Status and Sub Status Code
         */
        foreach ($claim_data as $key => $claim_datas) {
          $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
          $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

          $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
          $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
        }
        /** End */

        $current_total = $claim_data->count();

        $claim_count = $claim_count->count();

        $selected_claim_data = $selected_claim_data->get();

        $selected_count = $selected_claim_data->count();
      }
    } else if ($claim_type == "allocated") {
      // dd('datas3');
      $claimInfo = Claim_history::orderBy('id', 'desc')->get()->unique('claim_id')->toArray();
      $claimQcInfos = Qc_note::where('error_type', '[4]')->orderBy('id', 'desc')->get(['claim_id'])->unique('claim_id')->toArray();

      foreach ($claimInfo as $claimList) {
        if (isset($claimList) && $claimList['claim_state'] == 5 && $claimList['assigned_to'] == $user_id)
          array_push($assign, $claimList['claim_id']);
      }
      foreach($claimQcInfos as $claimQcInfo){
        if(isset($claimQcInfo))
        array_push($assign, $claimQcInfo['claim_id']);
      }

      if ($aignSearchValue == null) {

        if ($claim_type == "allocated"  && $sort_type == 'null' && $sorting_name == 'null') {
          // DB::enableQueryLog();
          $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                        })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories 
                        GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id) as claim_histories"), function ($join) {
                          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                        })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')
                        ->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->offset($skip)->limit($end)->get();

          // $claim_data = Import_field::leftJoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
          //               AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), 
          //               'claim_notes.claim_id', '=', 'import_fields.claim_no')
          //               ->leftJoin(DB::raw("(SELECT claim_histories.claim_id, claim_histories.created_at as created_ats FROM claim_histories 
          //               WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id) as claim_histories"), 
          //               'claim_histories.claim_id', '=', 'import_fields.claim_no')
          //               ->leftJoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')
          //               ->whereIn('claim_no', $assign)
          //               ->where('claim_closing', '!=', 1)
          //               ->offset($skip)
          //               ->limit($end)
          //               ->get();
          // $quries = DB::getQueryLog();
          // dd($quries);
          foreach ($claim_data as $key => $claim_datas) {
            $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
            $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

            $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
            $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
          }

          $current_total = $claim_data->count();
        } elseif ($claim_type == "allocated"  && $sort_type == 'null' && $sorting_method == true && empty($sorting_name)) {
          $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                        })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats
                        FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                        ) as claim_histories"), function ($join) {
                          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                        })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->offset($skip)->limit($end)->get();

          foreach ($claim_data as $key => $claim_datas) {
            $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
            $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

            $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
            $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
          }

          $current_total = $claim_data->count();
        }

        if ($claim_type == "allocated"  && $sort_type == 'null' && $sorting_name != 'null' && !empty($sorting_name)) {
          
          if ($sorting_method == true) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE claim_notes.deleted_at IS NULL
                          AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats FROM claim_histories 
                          WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id) as claim_histories"), function ($join) {
                            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end)->get();
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }

            $current_total = $claim_data->count();
          } else if ($sorting_method == false) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                          AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats FROM claim_histories 
                          WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                          ) as claim_histories"), function ($join) {
                            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end)->get();

            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            $current_total = $claim_data->count();
          }

          $current_total = $claim_data->count();
        }

        $selected_claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->get();

        if ($sort_type != 'null' && $sorting_name == 'null') {
          if ($sort_data == true) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                          AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats FROM claim_histories 
                          WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                          ) as claim_histories"), function ($join) {
                            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy($sort_type, 'desc')->offset($skip)->limit($end)->get();
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            $current_total = $claim_data->count();
          } else if ($sort_data == false) {
            $claim_data = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                          AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
                            $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin(DB::raw("(SELECT claim_histories.claim_id,claim_histories.created_at as created_ats FROM claim_histories 
                          WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id) as claim_histories"), function ($join) {
                            $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
                          })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy($sort_type, 'asc')->offset($skip)->limit($end)->get();
            foreach ($claim_data as $key => $claim_datas) {
              $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
              $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

              $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
              $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
            }
            $current_total = $claim_data->count();
          }
        }

        $claim_count = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->count();
      }


      if ($aignSearchValue != null) {

        $claim_data = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                                  claim_histories.claim_id,claim_histories.created_at as created_ats
                                FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                                ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        // })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orWhere('qc_notes.error_type', '[4]');
        })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1);

        $claim_count = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                                  claim_histories.claim_id,claim_histories.created_at as created_ats
                                FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                                ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        // })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orWhere('qc_notes.error_type', '[4]');
      })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1);

        $selected_claim_data = Import_field::leftjoin(DB::raw("(SELECT
                              claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                            AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT
                                  claim_histories.claim_id,claim_histories.created_at as created_ats
                                FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id
                                ) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        // })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orWhere('qc_notes.error_type', '[4]');
        })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1);


        if (!empty($search_claim_no)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('claim_no', $search_claim_no)->offset($skip)->limit($end);


            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('claim_no', $search_claim_no)->offset($skip)->limit($end);


            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); claim_no sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('claim_no', $search_claim_no)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claim_no', $search_claim_no);

            $selected_claim_data->where('claim_no', $search_claim_no);
          }
        }

        if (!empty($search_dos) && $search_dos['startDate'] != null) {

          $assign_sart_date = date('Y-m-d', strtotime($search_dos['startDate']));
          $assign_end_date = date('Y-m-d', strtotime($search_dos['endDate']));

          if ($assign_sart_date == $assign_end_date) {
            $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
            $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate'] . "+ 1 day"));
          } elseif ($assign_sart_date != $assign_end_date) {
            $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
            $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate']));
          }

          if ($sort_type == null && $sort_data == null) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);


            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);


            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); dos sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
          }
        }

        if (!empty($search_acc_no)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);


            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);


            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); acct_no sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('acct_no', $search_acc_no);

            $selected_claim_data->where('acct_no', $search_acc_no);
          }
        }

        if (!empty($search_patient_name)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); patient_name sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

            $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
          }
        }

        if (!empty($search_responsibility)) {
          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
            $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
          }
        }

        if (!empty($search_total_charge)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('total_charges', '=', $search_total_charge)->offset($skip)->limit($end);


            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('total_charges', '=', $search_total_charge)->offset($skip)->limit($end);


            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); total_charges sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('total_charges', '=', $search_total_charge)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('total_charges', '=', $search_total_charge);

            $selected_claim_data->where('total_charges', '=', $search_total_charge);
          }
        }

        if (!empty($search_date) && $search_date['startDate'] != null) {
          $closed_sart_date = date('Y-m-d', strtotime($search_date['startDate']));
          $closed_end_date = date('Y-m-d', strtotime($search_date['endDate']));

          if ($closed_sart_date == $closed_end_date) {
            $created_start_date = date('Y-m-d', strtotime($search_date['startDate'] . "+ 1 day"));
            $created_end_date = date('Y-m-d', strtotime($search_date['endDate'] . "+ 1 day"));
          } elseif ($closed_sart_date != $closed_end_date) {
            $created_start_date = date('Y-m-d', strtotime($search_date['startDate'] . "+ 1 day"));
            $created_end_date = date('Y-m-d', strtotime($search_date['endDate']));
          }

          if ($sort_type == null && $sort_data == null) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
            $selected_claim_data->where(DB::raw('DATE(claim_histories.created_ats)'), '>=', $created_start_date)->where(DB::raw('DATE(claim_histories.created_ats)'), '<=', $created_end_date);
          }
        }

        if (!empty($search_total_ar)) {
          $OriginalString = trim($search_total_ar);
          $tot_ar = explode("-",$OriginalString);
          
          $min_tot_ar = $tot_ar[0] - 1.00;
          $max_tot_ar = $tot_ar[1];
          if ($sort_type == null && $sort_data == null) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          }
          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
          }
        }

        if (!empty($search_rendering_provider)) {

          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          }
          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          }
          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          }
          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          }
          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
            $selected_claim_data->where('rendering_prov', 'LIKE', '%' . $search_rendering_provider . '%');
          }
        }
  
        if (!empty($search_claim_note)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->offset($skip)->limit($end);


            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->offset($skip)->limit($end);


            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); claim_note sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');

            $selected_claim_data->where('claims_notes', 'LIKE', '%' . $search_claim_note . '%');
          }
        }

        if (!empty($search_age)) {
          if ($sort_type == null && $sort_data == null) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
          }else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
            {
              $last_thirty = Carbon::now()->subDay($search_age['to_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
            }
            if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }
            if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
            {
              $to_age = Carbon::now()->subDay($search_age['to_age']);
              $from_age = Carbon::now()->subDay($search_age['from_age']);
              $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
              $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
            }

          }
        }

        if (!empty($search_prim_ins_name)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); prim_ins_name sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

            $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
          }
        }

        if (!empty($search_prim_pol_id)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); prim_pol_id sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');

            $selected_claim_data->where('prim_pol_id', 'LIKE', '%' . $search_prim_pol_id . '%');
          }
        }

        if (!empty($search_sec_ins_name)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); sec_ins_name sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

            $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
          }
        }

        if (!empty($search_sec_pol_id)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); sec_pol_id sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');

            $selected_claim_data->where('sec_pol_id', 'LIKE', '%' . $search_sec_pol_id . '%');
          }
        }

        if (!empty($search_ter_ins_name)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); ter_ins_name sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

            $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
          }
        }

        if (!empty($search_status_code) && $search_status_code['id'] != null) {
          $status_code = $search_status_code['id'];
          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('status_code', $status_code)->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('status_code', $status_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('status_code', $status_code)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('status_code', $status_code);
            $selected_claim_data->where('status_code', $status_code);
          }
        }

        if (!empty($search_denial_code)) {
          if ($sort_type == null && $sort_data == null) {
            $claim_data->where('denial_code', $search_denial_code)->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where('denial_code', $search_denial_code)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where('denial_code', $search_denial_code);
            $selected_claim_data->where('denial_code', $search_denial_code);
          }
        }

        if (!empty($search_bill_submit_date) && $search_bill_submit_date['startDate'] != null) {

          $assign_sart_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate']));
          $assign_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate']));

          if ($assign_sart_date == $assign_end_date) {
            $bill_start_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate'] . "+ 1 day"));
            $bill_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate'] . "+ 1 day"));
          } elseif ($assign_sart_date != $assign_end_date) {
            $bill_start_date = date('Y-m-d', strtotime($search_bill_submit_date['startDate'] . "+ 1 day"));
            $bill_end_date = date('Y-m-d', strtotime($search_bill_submit_date['endDate']));
          }

          if ($sort_type == null && $sort_data == null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sort_type, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {
            $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
            $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
            $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
          }
        }

        if (!empty($search_ter_pol_id)) {


          if ($sort_type == null && $sort_data == null) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }

          if ($sort_data == 'null' && $sort_type == 'null' && empty($sorting_name)) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->offset($skip)->limit($end);


            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }

          if ($sort_data == true && $search == null && $sorting_name == 'null') {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }

          if ($sort_data == true && $search == 'search' && $sort_data != null && $sort_type != 'null' && $sort_type != null) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sort_data == false && $search == 'search'  && $sort_type != null) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sort_type, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }
          //dd($sort_data); echo "</br>"; false sort_type_close
          // print_r($sort_type); echo "</br>"; exit(); ter_pol_id sort_code

          if ($sorting_method == true && $sort_data == 'null' && $search == 'search' && $sort_type == 'null' && !empty($sorting_name)) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          } else if ($sorting_method == false && $sort_data == 'null' && $search == 'search' && !empty($sorting_name)) {

            $claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

            $claim_count->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');

            $selected_claim_data->where('ter_pol_id', 'LIKE', '%' . $search_ter_pol_id . '%');
          }
        } else {
          if (!empty($sorting_name)) {
            if ($sorting_method == true) {
              $claim_data->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
              $claim_count->orderBy($sorting_name, 'desc');
              $selected_claim_data->orderBy($sorting_name, 'desc');
            } else if ($sorting_method == false) {
              $claim_data->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
              $claim_count->orderBy($sorting_name, 'asc');
              $selected_claim_data->orderBy($sorting_name, 'asc');
            }
          }
        }


        // DB::enableQueryLog();
        $claim_data = $claim_data->get();
        // $query = DB::getQueryLog();
        // dd($query);


        /** Developer : Sathish
         *  Date: 29/12/2022
         *  Purpose : To Get Status and Substatus Name
         */
        foreach ($claim_data as $key => $claim_datas) {
          $getStatusCode = Statuscode::where('id', $claim_datas['status_code'])->first();
          $claim_data[$key]['statuscode'] = $getStatusCode->status_code ? $getStatusCode->status_code : 'NA';

          $getSubStatusCode = Sub_statuscode::where('id', $claim_datas['substatus_code'])->first();
          $claim_data[$key]['substatuscode'] = $getSubStatusCode->status_code ? $getSubStatusCode->status_code : 'NA';
        }

        $current_total = $claim_data->count();

        $claim_count = $claim_count->count();

        $selected_claim_data = $selected_claim_data->get();

        $selected_count = $selected_claim_data->count();
      }
    }


    if ($claim_count != 0) {


      foreach ($claim_data as $key => $value) {

        // $claim_data[$key]['created_ats'] = date('m/d/Y', strtotime($claim_data[$key]['created_ats']));

        // $dos = strtotime($claim_data[$key]['dos']);

        // if (!empty($dos) && $dos != 0000 - 00 - 00 && $dos != 01 - 01 - 1970) {
        //   $claim_data[$key]['dos'] = date('m-d-Y', $dos);
        // }

        // if ($dos == 0000 - 00 - 00) {
        //   $claim_data[$key]['dos'] = 01 - 01 - 1970;
        // }

        // if ($dos == 01 - 01 - 1970) {
        //   $claim_data[$key]['dos'] = 01 - 01 - 1970;
        // }

        $dob = $claim_data[$key]['dos'];

        $from = DateTime::createFromFormat('m/d/Y', date('m/d/Y', strtotime($dob)));

        $to = date('d/m/Y');
        $to = new DateTime;
        $age = $to->diff($from);

        $claim_data[$key]['age'] = $age->days;
        
        $getExecutiveDate = Claim_history::where('claim_state', 4)->where('claim_id', $value['claim_no'])->latest()->select('created_at')->first();
        $claim_data[$key]['executive_work_date'] = $getExecutiveDate->created_at ? date('m/d/Y', strtotime($getExecutiveDate->created_at)) : null;


        $claim_data[$key]['touch'] = Claim_note::where('claim_id', $claim_data[$key]['claim_no'])->count();
        $assigned_data = Action::where('claim_id', $claim_data[$key]['claim_no'])->orderBy('created_at', 'desc')->first();
        if ($assigned_data != null) {
          $assigned_to = User::where('id', $assigned_data['assigned_to'])->pluck('firstname');
          $assigned_by = User::where('id', $assigned_data['assigned_by'])->pluck('firstname');


          $assignedTo_size = sizeOf($assigned_to);
          $assignedBy_size = sizeOf($assigned_by);

          $claim_data[$key]['assigned_to'] = $assignedTo_size ? $assigned_to[0] : 'NA';
          $claim_data[$key]['assigned_by'] = $assignedBy_size ? $assigned_by[0] : 'NA';
          $claim_data[$key]['created'] = date('m/d/Y', strtotime($assigned_data['created_at']));
          //   $claim_data[$key]['followup_date'] = date('m/d/Y', strtotime( $claim_data[$key]['followup_date']));
          $date_format[0] = (int)date('d', strtotime($claim_data[$key]['followup_date']));
          $date_format[1] = (int)date('m', strtotime($claim_data[$key]['followup_date']));
          $date_format[2] = (int)date('Y', strtotime($claim_data[$key]['followup_date']));

          $claim_data[$key]['followup_date'] = $date_format;
        }
      }
    }
    $op_data['datas'] = $claim_data;
    return response()->json([
      'data' => $op_data,
      'count' => $claim_count,
      'odata' => $claim_data,
      'claims' => $claim_type,
      'cls'  => $pending,
      'current_total' => $current_total,
      'skip' => $skip,
      'selected_claim_data' => $selected_claim_data
    ]);
  }

  //Get Auditor Details
  public function get_auditors(LoginRequest $request)
  {
    $users = User::whereIn('role_id', [2, 3, 4])->select('id', 'firstname', 'lastname')->get();
    $i = 0;
    foreach ($users as $auditor) {
      $claim_count = Import_field::where('assigned_to', $auditor['id'])->where('claim_Status', 'Auditing')->count();
      $users[$i]['assigned_nos'] = $claim_count;
      $assign_limit = User_work_profile::where('user_id', $auditor['id'])->orderBy('id', 'desc')->first();
      $users[$i]['assigned_claims'] = $claim_count;
      $users[$i]['assign_limit'] = $assign_limit['claim_assign_limit'] ? $assign_limit['claim_assign_limit'] : 1;
      $i++;
      //dd($assign_limit); echo "<br>";
    } //exit();

    return response()->json([
      'data' => $users
    ]);
  }





  public function fetch_export_data(LoginRequest $request)
  {

    $filter = $request->get('filter');
    $status_code = $request->get('status');
    $user_id = $request->get('user');


    /*The Values of User role and status code must be changed */

    //   $status_code= Statuscode::where('description','like', '%' . 'RCM Team' . '%')->get();

    $user_role = User::where('id', $user_id)->pluck('role_id');

    if ($user_role[0] == 5 || $user_role[0] == 3 || $user_role[0] == 2) {
      $claim_data = Import_field::where('claim_Status', 'Audit')->orWhere('claim_Status', 'Auditing')->orderBy('id', 'asc')->get();
    } else {
      $claim_data = Import_field::where('assigned_to', $user_id)->where('claim_Status', 'Auditing')->orderBy('id', 'desc')->get();
    }

    foreach ($claim_data as $key => $claim) {
      $dob = $claim_data[$key]['created_at'];
      // $claim_age = Carbon::parse($dob)->age;
      $claim_age = $dob->diff(Carbon::now())->format('%d');
      $claim_data[$key]['claim_age'] = $claim_age;
      if ($claim['status_code'] == null) {
        $claim_data[$key]['status_code'] = "NA";
      } else {
        $status_code = Statuscode::where('id', $claim['status_code'])->get();
        $claim_data[$key]['status_code'] = $status_code[0]['status_code'] . "-" . $status_code[0]['description'];
      }

      $assigned_data = Action::where('claim_id', $claim_data[$key]['claim_no'])->orderBy('created_at', 'desc')->first();

      $assigned_to = User::where('id', $assigned_data['assigned_to'])->pluck('firstname');
      $assigned_by = User::where('id', $assigned_data['assigned_by'])->pluck('firstname');
      $claim_data[$key]['assigned_to_name'] = $assigned_to[0];
      $claim_data[$key]['assigned_by_name'] = $assigned_by[0];
      $claim_data[$key]['assigned_date'] = date('d/m/Y', strtotime($assigned_data['created_at']));
      $claim_data[$key]['touch'] = Claim_note::where('claim_id', $claim['claim_no'])->count();
    }

    return response()->json([
      'data'  => $claim_data
    ]);
  }

  public function get_audit_codes(LoginRequest $request)
  {
    $id = $request->get('id');

    $root_states = Root_cause::all();
    $error_type = Error_type::all();

    return response()->json([
      'root_states'  => $root_states,
      'err_types'  => $error_type
    ]);
  }

  public function auto_assign_claims(LoginRequest $request)
  {
    $id = $request->get('id');
    $users = User::where('role_id', '1')->pluck('id');
    //dd($users);
    $user_det = User_work_profile::whereIN('user_id', $users)->get();
    //dd($user_det);
    $sectable_nos = 0;
    $res = [];
    $j = 0;
    foreach ($user_det as $user) {
      //dd($user['id']);
      $result = [];
      $claim_worked = Import_field::where('followup_work_order', $user['id'])->get()->toArray();

      //dd($claim_worked);

      // dd($claim_worked);
      // print_r($user['id']); echo "</br>";

      $claim_worked_nos = sizeof($claim_worked);

      // dd($claim_worked_nos);

      if ($claim_worked_nos != 0 && $user['caller_benchmark'] != null) {
        $benchmark = $user['caller_benchmark'];
        $percentInDecimal = $benchmark / 100;
        //dd($benchmark);
        //dd($percentInDecimal);

        $sectable_nos = $percentInDecimal * $claim_worked_nos;
        if (round($sectable_nos) > 0) {
          // dd(round($sectable_nos));

          $test = round($sectable_nos);

          $random_arrays = array_rand($claim_worked, round($sectable_nos));

          //dd($random_arrays);
          $op_array = [];

          if (!is_array($random_arrays)) {
            array_push($op_array, $claim_worked[$random_arrays]);
          } else {
            foreach ($random_arrays as $rand) {
              array_push($op_array, $claim_worked[$rand]);
            }
          }


          // dd($op_array);


          $result['id'] = $user['id'];
          $result['nos'] = round($sectable_nos);
          $result['bm'] = $user['caller_benchmark'];
          $result['wnos'] = $claim_worked_nos;
          $result['workable_claims'] = $claim_worked;
          $result['work_claims'] = $op_array;

          array_push($res, $result);
        }
      }
    }

    return response()->json([
      'data'  => $res
    ]);
  }


  public function auto_assigned(LoginRequest $request)
  {

    $user_id = $request->get('user_id');
    $claim_id = $request->get('claim_id');

    $workorder_det = $request->get('work');
    $claim_det = $request->get('claim');
    $wo_type = $request->get('type');
    // dd($wo_type);
    $size = 0;

    $users = User::whereIN('role_id', [3, 4])->pluck('id');
    //dd($user);

    foreach ($users as $key => $user) {
      $claim_istory[$key]['id'] = $user;
      $claim_istory[$key]['count'] = Claim_history::where('assigned_to', $user)->where('claim_state', 5)->orderBy('id', 'DESC')->groupBy('assigned_by')->count();

      //$count[$key] = (isset($query[0]))?$query[0]->claim_id:0;
    }


    array_multisort(array_column($claim_istory, 'count'), SORT_ASC, $claim_istory);

    $assigned_id = $claim_istory[0]['id'];

    if ($wo_type == 'followup') {
      $action_type = 1;
      $work_order_type = 1;
      $claim_status = 'Assigned';
      $claim_state = 3;
      $users = User::where('role_id', array(1, 3, 2))->pluck('id');
    } else if ($wo_type == 'audit') {
      $action_type = 2;
      $work_order_type = 2;
      $claim_status = 'Auditing';
      $claim_state = 5;
      $users = User::where('role_id', array(4, 3, 2))->pluck('id');
    } else if ($wo_type == 'client_assistance') {
      $action_type = 3;
      $work_order_type = 3;
      $claim_status = 'CA Assigned';
      $claim_state = 7;
      $users = User::where('role_id', array(6, 3, 2))->pluck('id');
    } else if ($wo_type == 'rcm_team') {
      $action_type = 4;
      $work_order_type = 4;
      $claim_status = 'RCM Assigned';
      $claim_state = 8;
      $users = User::whereIN('role_id', array(7, 3, 2))->pluck('id');
    }

    $date = date('Y-m-d', strtotime($workorder_det['due_date']['day'] . '-' . $workorder_det['due_date']['month'] . '-' . $workorder_det['due_date']['year']));

    $workorder = Workorder_field::create([
      'work_order_name'       => $workorder_det['workorder_name'],
      'work_order_type'       => $work_order_type,
      'due_date'              => $date,
      'status'                => $claim_status,
      'priority'              => $workorder_det['priority'],
      'work_notes'            => $workorder_det['wo_notes'],
      'created_by'            => $user_id
    ]);


    $claim_assign = Workorder_user_field::create([
      'work_order_id'              => $workorder['id'],
      'user_id'                    => $assigned_id,
      'cliam_no'                   => json_encode($claim_id)
    ]);


    $claim_data = [];
    $i = 1;


    foreach ($claim_id as $claim) {
      $update_claim = DB::table('actions')->where('claim_id', $claim)->whereIn('assigned_to', $users)->update(array(
        'status'          =>  'Inactive'
      ));


      $action = Action::create([
        'claim_id'          => $claim,
        'action_id'         => $workorder['id'],
        'action_type'       => $action_type,
        'assigned_to'       => $assigned_id,
        'assigned_by'       => $user_id,
        'created_at'        => date('Y-m-d H:i:s'),
        'created_by'        => $user_id,
        'status'            => 'Active'
      ]);


      $Claim_history = Claim_history::create([
        'claim_id'          => $claim,
        'claim_state'       => $claim_state,
        'assigned_to'       => $assigned_id,
        'assigned_by'       => $user_id,
        'created_at'        => date('Y-m-d H:i:s')
      ]);


      if ($work_order_type == 1) {
        $update_claim = DB::table('import_fields')->where('claim_no', $claim)->update(array(
          'claim_Status'          =>  $claim_status,
          'assigned_to'           =>  $assigned_id,
          'followup_work_order'   =>  $workorder['id']
        ));
      } else if ($work_order_type == 2) {
        $update_claim = DB::table('import_fields')->where('claim_no', $claim)->update(array(
          'claim_Status'          =>  $claim_status,
          'assigned_to'           =>  $assigned_id,
          'audit_work_order'      =>  $workorder['id']
        ));
      } else if ($work_order_type == 3) {
        $update_claim = DB::table('import_fields')->where('claim_no', $claim)->update(array(
          'claim_Status'          =>  $claim_status,
          'assigned_to'           =>  $assigned_id,
          'ca_work_order'         =>  $workorder['id']
        ));
      } else if ($work_order_type == 4) {
        $update_claim = DB::table('import_fields')->where('claim_no', $claim)->update(array(
          'claim_Status'          =>  $claim_status,
          'assigned_to'           =>  $assigned_id,
          'rcm_work_order'        =>  $workorder['id']
        ));
      }



      $claim_data[$i] = ["claim_no" => $claim, "state" => $claim_state, "assigned_by" => $user_id, "assigned_to" => $assigned_id];
      $i++;
    }


    $size = sizeof($claim_data);

    return response()->json([
      'data'  => $claim_data
    ]);
  }

  public function audit_assigned_order_list(LoginRequest $request)
  {
    $user_id = $request->get('user_id');
    $page_no = $request->get('page_no');
    $page_count = $request->get('count');
    $claim_type = $request->get('claim_type');
    $sort_type = $request->get('sort_type');
    $sort_data = $request->get('sort_data');

    //dd($claim_type);
    $total_count = 0;
    $skip = ($page_no - 1) * $page_count;
    $end = $page_count;
    $op_data = [];
    $present_data = Config::get('fields.data');
    $field_data = [];
    foreach ($present_data as $key => $value) {
      $field_data[$value] = $key;
    }
    $op_data['fields'] = $field_data;
    $user_role = User::where('id', $user_id)->first();
    $claim_count = 0;
    /* if($user_role['role_id'] == 5 || $user_role['role_id'] == 3 || $user_role['role_id'] == 2)
        { */


    $worked = [];
    $pending = [];
    $clams = [];
    $assign = [];
    $closed = [];

    $claims = Action::where('assigned_to', $user_id)->where('action_type', 2)->where('status', 'Active')->get();
    //dd($claims);
    foreach ($claims as $active) {
      $date = date('Y-m-d', strtotime($active['created_at']));



      $allocated = Claim_history::where('claim_id', $active['claim_id'])->whereIN('claim_state', [4, 6, 7, 8, 9])->where('created_at', '>=', $date)->count();

      if ($allocated > 0) {
        array_push($worked, $active['claim_id']);
      } else {
        array_push($pending, $active['claim_id']);
      }
      // ->orderBy('created_at', 'desc')->distinct('claim_id')
    }


    if ($claim_type == "allocated") {
      $claimInfo = Claim_history::orderBy('id', 'desc')->get()->unique('claim_id')->toArray();

      foreach ($claimInfo as $claimList) {
        if (isset($claimList) && $claimList['claim_state'] == 5 && $claimList['assigned_to'] == $user_id)
          array_push($assign, $claimList['claim_id']);
      }

      if ($sort_type == 'claim_no') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_no', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_no', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'dos') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('dos', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('dos', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'acct_no') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('acct_no', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('acct_no', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'patient_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('patient_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('patient_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'rendering_prov') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('rendering_prov', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('rendering_prov', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'responsibility') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('responsibility', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('responsibility', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'prim_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('prim_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('prim_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'sec_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('sec_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('sec_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'ter_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('ter_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('ter_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'total_charges') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('total_charges', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('total_charges', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'total_ar') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('total_ar', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('total_ar', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'claim_Status') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_Status', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_Status', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'claim_note') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_note', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('claim_note', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'assigned_to') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('assigned_to', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->orderBy('assigned_to', 'asc')->offset($skip)->limit($end)->get();
        }
      }



      $claim_count = Import_field::whereIN('claim_no', $assign)->count();
    } else if ($claim_type == "completed") {
      $claimInfo = Claim_history::orderBy('id', 'desc')->get()->unique('claim_id')->toArray();

      foreach ($claimInfo as $claimList) {

        if (isset($claimList) && $claimList['claim_state'] == 9 &&  $claimList['assigned_by'] == $user_id)
          array_push($closed, $claimList['claim_id']);
      }

      if ($sort_type == 'claim_no') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_no', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_no', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'dos') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('dos', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('dos', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'acct_no') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('acct_no', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('acct_no', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'patient_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('patient_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('patient_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'rendering_prov') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('rendering_prov', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('rendering_prov', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'responsibility') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('responsibility', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('responsibility', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'prim_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('prim_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('prim_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'sec_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('sec_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('sec_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'ter_ins_name') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('ter_ins_name', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('ter_ins_name', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'total_charges') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('total_charges', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('total_charges', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'total_ar') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('total_ar', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('total_ar', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'claim_Status') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_Status', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_Status', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'claim_note') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_note', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('claim_note', 'asc')->offset($skip)->limit($end)->get();
        }
      } else if ($sort_type == 'assigned_to') {
        if ($sort_data == true) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('assigned_to', 'desc')->offset($skip)->limit($end)->get();
        } else if ($sort_data == false) {
          $claim_data = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->orderBy('assigned_to', 'asc')->offset($skip)->limit($end)->get();
        }
      }


      $claim_count = Import_field::whereIN('claim_no', $closed)->where('claim_closing', 1)->count();
    }


    if ($claim_count != 0) {


      foreach ($claim_data as $key => $value) {

        $dob = $claim_data[$key]['dos'];

        $from = DateTime::createFromFormat('m/d/Y', date('m/d/Y', strtotime($dob)));

        $to = date('d/m/Y');
        $to = new DateTime;
        $age = $to->diff($from);

        $claim_data[$key]['age'] = $age->days;



        $claim_data[$key]['touch'] = Claim_note::where('claim_id', $claim_data[$key]['claim_no'])->count();
        $assigned_data = Action::where('claim_id', $claim_data[$key]['claim_no'])->orderBy('created_at', 'desc')->first();
        if ($assigned_data != null) {
          $assigned_to = User::where('id', $assigned_data['assigned_to'])->pluck('firstname');
          $assigned_by = User::where('id', $assigned_data['assigned_by'])->pluck('firstname');


          $assignedTo_size = sizeOf($assigned_to);
          $assignedBy_size = sizeOf($assigned_by);

          $claim_data[$key]['assigned_to'] = $assignedTo_size ? $assigned_to[0] : 'NA';
          $claim_data[$key]['assigned_by'] = $assignedBy_size ? $assigned_by[0] : 'NA';
          $claim_data[$key]['created'] = date('d/m/Y', strtotime($assigned_data['created_at']));
          //   $claim_data[$key]['followup_date'] = date('m/d/Y', strtotime( $claim_data[$key]['followup_date']));
          $date_format[0] = (int)date('d', strtotime($claim_data[$key]['followup_date']));
          $date_format[1] = (int)date('m', strtotime($claim_data[$key]['followup_date']));
          $date_format[2] = (int)date('Y', strtotime($claim_data[$key]['followup_date']));

          $claim_data[$key]['followup_date'] = $date_format;
        }
      }
    }
    $op_data['datas'] = $claim_data;
    return response()->json([
      'data' => $op_data,
      'count' => $claim_count,
      'odata' => $claim_data,
      'claims' => $claim_type,
      'cls'  => $pending
    ]);
  }



  //Create Audit Work Order
  // public function create_audit_workorder(LoginRequest $request)
  // {
  //     $user_id=$request->get('id');    
  //     $workorder_det=$request->get('assign_data');    
  //     $claim_data;
  //     $i=1;
  //     foreach($workorder_det as $detail)
  //     {
  //         $auditor=$detail['auditor'];
  //         $claims=$detail['claims'];
  //         foreach($claims as $claim)
  //         {
  //             $update_claim=DB::table('import_fields')->where('claim_no',$claim)->update(array(
  //                 'claim_Status'          => 'Auditing',
  //                 'assigned_to'           =>  $auditor
  //                 ));
  //                 $claim_data[$i] = ["claim_no" => $claim, "state" => 5, "assigned_by" => $user_id, "assigned_to" => $auditor];
  //                 $i++;    
  //                 }
  //                 }
  //                 $data= Record_claim_history::create_history($claim_data);
  //                  return response()->json([
  //                      'data' => $workorder_det
  //                      ]);
  //                      }


  public function get_error_param_codes(LoginRequest $request)
  {

    $id = $request->get('id');
    $error_param_type = ParentParameter::where('status', 1)->where('id', '<>', 5)->get();
    $fyi_param = ParentParameter::where('status', 1)->where('id', 5)->get();
    // $sub_error_param_type= ErrorParameter::select('id','error_sub_parameter')->where('status', 1)->get();

    return response()->json([
      'err_param_types'  => $error_param_type,
      'fyi_param_types'  => $fyi_param,
      //'sub_err_param_types'  => $sub_error_param_type,
    ]);
  }

  public function get_fyi_param_codes(LoginRequest $request)
  {
    $id = $request->get('id');
    $fyi_param_type = FYIParameter::select('id', 'fyi_parameter')->where('status', 1)->get();
    $fyi_sub_param_type = FYIParameter::select('id', 'fyi_sub_parameter')->where('status', 1)->get();

    return response()->json([
      'fyi_param_types'  => $fyi_param_type,
      'fyi_sub_param_types'  => $fyi_sub_param_type,
    ]);
  }


  public function get_sub_error_param_codes(LoginRequest $request)
  {
    $parent_id = $request->get('parent_id');
    $getSubParamCode = SubParameter::where('parent_id', $parent_id)->get();

    if ($getSubParamCode) {
      return Response::json(['status' => '200', 'sub_param_datas' => $getSubParamCode]);
    } else {
      return Response::json(['status' => '400', 'sub_param_datas' => $getSubParamCode]);
    }
  }

  public function change_auditor(LoginRequest $request)
  {
    try {
      $user_id = $request->get('user_id');
      $new_user_id = $request->get('new_user_id');
      $audit_mgr_id = $request->get('audit_mgr_id');
      $practice_dbid = $request->get('practice_dbid');

      $assign = [];

      $claimInfo = Claim_history::orderBy('id', 'desc')->get()->unique('claim_id')->toArray();
      $claimQcInfos = Qc_note::where('error_type', '[4]')->orderBy('id', 'desc')->get(['claim_id'])->unique('claim_id')->toArray();

      foreach ($claimInfo as $claimList) {
        if (isset($claimList) && $claimList['claim_state'] == 5 && $claimList['assigned_to'] == $user_id) {
          array_push($assign, $claimList['claim_id']);
        }
      }
      foreach ($claimQcInfos as $claimQcInfo) {
        if (isset($claimQcInfo)) {
          array_push($assign, $claimQcInfo['claim_id']);
        }
      }



      if (isset($user_id) && $user_id != "null") {
        // $audit_datas = Import_field::select('claim_no')
        //                 ->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')
        //                 ->leftjoin('claim_histories', 'claim_histories.claim_id', '=', 'import_fields.claim_no')
        //                 ->whereIn('claim_Status', ['Audit', 'Auditing'])
        //                 ->where('claim_histories.assigned_to', $user_id)
        //                 ->groupby('claim_histories.claim_id')
        //                 ->get();
        $claim_datas = Import_field::leftjoin(DB::raw("(SELECT claim_notes.claim_id,claim_notes.content as claims_notes FROM claim_notes WHERE  claim_notes.deleted_at IS NULL
                        AND claim_notes.id IN (SELECT MAX(id) FROM claim_notes GROUP BY claim_notes.claim_id) GROUP BY claim_notes.claim_id ) as claim_notes"), function ($join) {
          $join->on('claim_notes.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin(DB::raw("(SELECT claim_histories.claim_id, claim_histories.claim_state, claim_histories.assigned_by AS assign_by, claim_histories.assigned_to AS assign_to, claim_histories.created_at as created_ats FROM claim_histories WHERE claim_histories.id IN (SELECT MAX(id) FROM claim_histories 
                        GROUP BY claim_histories.claim_id) GROUP BY claim_histories.claim_id) as claim_histories"), function ($join) {
          $join->on('claim_histories.claim_id', '=', 'import_fields.claim_no');
        })->leftjoin('qc_notes', 'import_fields.claim_no', '=', 'qc_notes.claim_id')
        ->whereIN('claim_no', $assign)->where('claim_closing', '!=', 1)->get();

        // dd($claim_datas);

        if (isset($new_user_id) && $new_user_id != 'null') {
          foreach ($claim_datas as $claim_data) {
            $audit_change = Claim_history::where('assigned_to', $user_id)->update([
              'assigned_by' => $audit_mgr_id,
              'assigned_to' => $new_user_id,
              'previous_auditor_id' => $claim_data['assign_to'],
              'previous_audit_mgr_id' => $claim_data['assign_by'],
            ]);
          }
          return response()->json([
            'status' => 200,
            'message' => "New Auditor Set Successfully"
          ]);
        } else {
          return response()->json([
            'status' => 204,
            'message' => "new user id not has been set"
          ]);
        }
      }
    } catch (Exception $e) {
      Log::debug('Change Auditor Error : ' . $e->getMessage());
      throw new Exception('Change Auditor Error : ' . $e->getMessage());
    }
  }

} 

