<?php

namespace App\Http\Controllers;

use App\Action;
use App\Claim_note;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Import_field;
use App\Statuscode;
use App\User;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class NewClaimsController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => 'get_new_claims']);
    }


    public function get_new_claims(LoginRequest $request){
        try {
            $searchValue = $request->get('createsearch');

            $claim_datas = Import_field::whereNull('followup_work_order')->where('claim_Status', Null)->orWhere('claim_Status', 'Ready')->orderBy('created_at', 'desc');
            
            if(isset($searchValue['dos']) && $searchValue['dos']['startDate'] != null)
            {
                $dos_sart_date = Carbon::createFromFormat('Y-m-d', $searchValue['startDate'])->startOfDay();
                $dos_end_date = Carbon::createFromFormat('Y-m-d', $searchValue['endDate'])->endOfDay();

                $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

            }

            if(isset($searchValue['age_filter']) && $searchValue['age_filter'] != null)
            {
                $search_age = $searchValue['age_filter'];
                if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                }
                if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                }
                if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                }
                if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                }
                if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                }
                if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_datas->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                }
            }

            if(isset($searchValue['claim_no']) && !empty($searchValue['claim_no']))
            {
                $claim_datas->where('claim_no', $searchValue['claim_no']);
            }
            
            if(isset($searchValue['acct_no']) && !empty($searchValue['acct_no']))
            {
                $claim_datas->where('acct_no', $searchValue['acct_no']);
            }

            if(isset($searchValue['patient_name']) && !empty($searchValue['patient_name']))
            {
                $claim_datas->where('patient_name', 'LIKE', '%' . $searchValue['patient_name'] . '%');
            }

            if(isset($searchValue['responsibility']) && !empty($searchValue['responsibility']))
            {
                $claim_datas->where('responsibility', 'LIKE', '%' . $searchValue['responsibility'] . '%');
            }
            
            if(isset($searchValue['total_ar']) && !empty($searchValue['total_ar']))
            {
                $OriginalString = trim($searchValue['total_ar']);
                $tot_ar = explode("-",$OriginalString);
                $min_tot_ar = $tot_ar[0] - 1.00;
                $max_tot_ar = $tot_ar[1];

                $claim_datas->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
            }

            if(isset($searchValue['rendering_provider']) && !empty($searchValue['rendering_provider']))
            {
                $claim_datas->where('rendering_prov', 'LIKE', '%' . $searchValue['rendering_provider'] . '%');
            }

            if(isset($searchValue['payer_name']) && !empty($searchValue['payer_name']))
            {
                $claim_datas->where('prim_ins_name', 'LIKE', '%' . $searchValue['payer_name'] . '%');
                $claim_datas->orWhere('sec_ins_name', 'LIKE', '%' . $searchValue['payer_name'] . '%');
                $claim_datas->orWhere('ter_ins_name', 'LIKE', '%' . $searchValue['payer_name'] . '%');
            }

            if(isset($searchValue['date']) && $searchValue['date']['startDate'] != null)
            {
                $search_date = $searchValue['date'];
                $created_start_date = Carbon::createFromFormat('Y-m-d', $search_date['startDate'])->startOfDay();
                $created_end_date = Carbon::createFromFormat('Y-m-d', $search_date['endDate'])->endOfDay();

                $claim_datas->where(DB::raw('DATE(import_fields.created_at)'), '>=', $created_start_date)->where(DB::raw('DATE(import_fields.created_at)'), '<=', $created_end_date);
            

            }

            if(isset($searchValue['bill_submit_date']) && $searchValue['bill_submit_date']['startDate'] != null)
            {
                $search_submit_date = $searchValue['bill_submit_date'];
                $bill_start_date = Carbon::createFromFormat('Y-m-d', $search_submit_date['startDate'])->startOfDay();
                $bill_end_date = Carbon::createFromFormat('Y-m-d', $search_submit_date['endDate'])->endOfDay();

                $claim_datas->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);

            }

            if(isset($searchValue['denial_code']) && !empty($searchValue['denial_code']))
            {
                $claim_datas->where('denial_code', $searchValue['denial_code']);
            }

            $claim_data = $claim_datas->get();
            $claim_counts = $claim_datas->count();
            if(isset($claim_data)){
                $claim_data = $this->arrange_claim_datas($claim_data);
                foreach ($claim_data as $key => $value) {
                    $dos = strtotime($claim_data[$key]['dos']);
                    $dobs = $claim_data[$key]['dos'];
            
                    if (!empty($dos) && $dos != 0000 - 00 - 00) {
                      $claim_data[$key]['dos'] = date('m-d-Y', $dos);
                    }
            
                    if ($dos == 0000 - 00 - 00) {
                      $claim_data[$key]['dos'] = 01 - 01 - 1970;
                    }
            
                    $dob = strtotime($claim_data[$key]['dob']);
            
                    if (!empty($dob)) {
                      $claim_data[$key]['dob'] = date('m-d-Y', $dob);
                    }
            
                    $admit_date = strtotime($claim_data[$key]['admit_date']);
            
                    if (!empty($admit_date)) {
                      $claim_data[$key]['admit_date'] = date('m-d-Y', $admit_date);
                    }
            
                    $discharge_date = strtotime($claim_data[$key]['discharge_date']);
            
                    if (!empty($discharge_date)) {
            
                      $claim_data[$key]['discharge_date'] = date('m-d-Y', $discharge_date);
                    }
            
                    if ($value['status_code'] == null) {
                      $claim_data[$key]['status_code'] = "NA";
                    } else {
                      $status_code = Statuscode::where('id', $value['status_code'])->get();
                      $claim_data[$key]['status_code'] = $status_code[0]['status_code'] . "-" . $status_code[0]['description'];
                    }
            
                    $assigned_data = Action::where('claim_id', $claim_data[$key]['claim_no'])->orderBy('created_at', 'desc')->first();
            
                    $from = DateTime::createFromFormat('m/d/Y', date('m/d/Y', strtotime($dobs)));
            
                    $to = date('d/m/Y');
                    $to = new DateTime;
                    $age = $to->diff($from);
            
                    $claim_data[$key]['age'] = $age->days;
                    $claim_data[$key]['touch'] = Claim_note::where('claim_id', $value['claim_no'])->count();
                  }
            }

            return response()->json([
                'data'  => isset($claim_data) ? $claim_data : null,
                'total' => $claim_counts
              ]);

        } catch (Exception $e) {
            Log::debug('New Claims Error' . $e->getMessage());
        }

    }

    protected function arrange_claim_datas($claim_data)
    {
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
            }
        }
        return $claim_data;
    }
}
