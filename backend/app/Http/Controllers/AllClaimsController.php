<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Import_field;
use App\Statuscode;
use App\Claim_note;
use App\Action;
use App\User;
use Illuminate\Support\Facades\Log;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AllClaimsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['all_claim_list']]);
    }

    public function all_claim_list(LoginRequest $request)
    {
        $page_no = $request->get('page_no');
        $page_count = $request->get('count');
        $sort_data = $request->get('filter');
        $action = $request->get('sort_type');
        $sorting_name = $request->get('sorting_name');
        $sorting_method = $request->get('sorting_method');
        $searchValue = $request->get('createsearch');
        $search = $request->get('search');

        // dd($searchValue['claim_no']);

        $total_count = 0;
        $claim_data = null;
        $claim_count = 0;
        $audit = [];

        if ($searchValue == null && $search != 'search') {
            if (($action == null || $action == 'null') && $sorting_method == null && $searchValue == null) {
                $skip = ($page_no - 1) * $page_count;
                $end = $page_count;
                // DB::enableQueryLog();
                // $claim_data = Import_field::orderBy('created_at', 'desc')->offset($skip)->limit($end)->get();
                $claim_data = Import_field::leftJoin('claim_histories', 'import_fields.claim_no', '=', 'claim_histories.claim_id')
                              ->select('import_fields.*', 'claim_histories.claim_state', DB::raw('max(claim_histories.id) as max_id'), 'claim_histories.created_at as created_ats')
                              ->groupBy('claim_histories.claim_id')
                              ->orderByDesc('max_id')
                              ->offset($skip)->limit($end)
                              ->get();
                // $log = DB::getQueryLog();
                // dd($log);
                // log::debug(print_r($claim_data,true));
                // dd($claim_data);
                $current_total = $claim_data->count();

                $selected_claim_data = Import_field::orderBy('created_at', 'desc')->get();
                $selected_count = $selected_claim_data->count();

                $claim_data = $this->arrange_claim_datas($claim_data);
                $claim_count = Import_field::orderBy(
                    'id',
                    'asc'
                );
                $claim_count = $claim_count->count();
            } elseif ($sorting_method != null && $action == null && $searchValue == null) {
                $skip = ($page_no - 1) * $page_count;
                $end = $page_count;
                if ($sorting_name == true) {
                    //dd('2');
                  $claim_data = Import_field::leftJoin('claim_histories', 'import_fields.claim_no', '=', 'claim_histories.claim_id')
                              ->select('import_fields.*', 'claim_histories.claim_state', DB::raw('max(claim_histories.id) as max_id'), 'claim_histories.created_at as created_ats')
                              ->groupBy('claim_histories.claim_id')
                              ->orderByDesc('max_id')
                              ->offset($skip)->limit($end)
                              ->get();
                    // $claim_data = Import_field::offset($skip)->limit($end)->get();
                    $claim_count = Import_field::orderBy(
                        'id',
                        'desc'
                    );
                    $claim_count = $claim_count->count();
                    $current_total = $claim_data->count();
                } else if ($sorting_name == false) {
                    // $claim_data = Import_field::offset($skip)->limit($end)->get();
                    $claim_data = Import_field::leftJoin('claim_histories', 'import_fields.claim_no', '=', 'claim_histories.claim_id')
                                ->select('import_fields.*', 'claim_histories.claim_state', DB::raw('max(claim_histories.id) as max_id'), 'claim_histories.created_at as created_ats')
                                ->groupBy('claim_histories.claim_id')
                                ->orderByDesc('max_id')
                                ->offset($skip)->limit($end)
                                ->get();
                    $claim_count = Import_field::orderBy(
                        'id',
                        'asc'
                    );
                    $claim_count = $claim_count->count();
                    $current_total = $claim_data->count();
                }
                $claim_data = $this->arrange_claim_datas($claim_data);
                $selected_claim_data = Import_field::orderBy('created_at', 'desc')->get();
                $selected_count = $selected_claim_data->count();
            } elseif ($sorting_method == 'null' && $action != "null" && $searchValue == null) {
                $skip = ($page_no - 1) * $page_count;
                $end = $page_count;
                if ($sort_data == true) {
                    // $claim_data = Import_field::orderBy($action, 'desc')->offset($skip)->limit($end)->get();
                    $claim_data = Import_field::leftJoin('claim_histories', 'import_fields.claim_no', '=', 'claim_histories.claim_id')
                                  ->select('import_fields.*', 'claim_histories.claim_state', DB::raw('max(claim_histories.id) as max_id'), 'claim_histories.created_at as created_ats')
                                  ->groupBy('claim_histories.claim_id')
                                  ->orderByDesc($action)
                                  ->offset($skip)->limit($end)
                                  ->get();
                    $claim_count = Import_field::orderBy(
                        'id',
                        'desc'
                    );
                    $claim_count = $claim_count->count();
                    $current_total = $claim_data->count();
                } else if ($sort_data == false) {
                    // $claim_data = Import_field::orderBy($action, 'asc')->offset($skip)->limit($end)->get();
                    $claim_data = Import_field::leftJoin('claim_histories', 'import_fields.claim_no', '=', 'claim_histories.claim_id')
                                  ->select('import_fields.*', 'claim_histories.claim_state', DB::raw('max(claim_histories.id) as max_id'), 'claim_histories.created_at as created_ats')
                                  ->groupBy('claim_histories.claim_id')
                                  ->orderByAsc($action)
                                  ->offset($skip)->limit($end)
                                  ->get();
                    $claim_count = Import_field::orderBy(
                        'id',
                        'asc'
                    );
                    $claim_count = $claim_count->count();
                    $current_total = $claim_data->count();
                }
                $claim_data = $this->arrange_claim_datas($claim_data);
                $selected_claim_data = Import_field::orderBy('created_at', 'desc')->get();
                $selected_count = $selected_claim_data->count();
            }
        }

        if ($searchValue != null &&  $search == 'search') {
            $skip = ($page_no - 1) * $page_count;
            $end = $page_count;
            // DB::enableQueryLog();
            $claim_data = Import_field::orderBy('id', 'asc');
            $claim_count = Import_field::orderBy('id', 'asc');
            $selected_claim_data = Import_field::orderBy('id', 'asc');
            if (!empty($searchValue['claim_no']) && isset($searchValue['claim_no'])) {

                $search_claim_no = $searchValue['claim_no'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('claim_no', '=',  $search_claim_no)->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=',  $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                }

                if ($action != 'null' && $action != null && empty($sorting_name)) {

                    $claim_data->where('claim_no', '=',  $search_claim_no)->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                } else if ($sort_data == false && $search == 'search'  && $action != null) {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                }

                if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null) {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                } else if ($sorting_name == false && $sort_data == null && $search == 'search') {

                    $claim_data->where('claim_no', '=', $search_claim_no)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_no', '=', $search_claim_no);

                    $selected_claim_data->where('claim_no', '=', $search_claim_no);
                }
            }

            if (!empty($searchValue['acc_no']) && isset($searchValue['acc_no'])) {
                $search_acc_no = $searchValue['acc_no'];
                if ($action == 'null' && $action != null) {
                    $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {
                    $claim_data->where('acct_no', $search_acc_no)->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {
                    $claim_data->where('acct_no', $search_acc_no)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                    $claim_data->where('acct_no', $search_acc_no)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {

                    $claim_data->where('acct_no', $search_acc_no)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {
                    $claim_data->where('acct_no', $search_acc_no)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where('acct_no', $search_acc_no);
                    $selected_claim_data->where('acct_no', $search_acc_no);
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('acct_no', $search_acc_no);

                    $selected_claim_data->where('acct_no', $search_acc_no);
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('acct_no', $search_acc_no)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('acct_no', $search_acc_no);

                    $selected_claim_data->where('acct_no', $search_acc_no);
                }
            }

            if (!empty($searchValue['dos']) && $searchValue['dos']['startDate'] != null) {
                $search_dos = $searchValue['dos'];
                $create_sart_date = date('Y-m-d', strtotime($search_dos['startDate']));
                $create_end_date = date('Y-m-d', strtotime($search_dos['endDate']));

                if ($create_sart_date == $create_end_date) {
                    $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
                    $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate'] . "+ 1 day"));
                } elseif ($create_sart_date != $create_end_date) {
                    $dos_sart_date = date('Y-m-d', strtotime($search_dos['startDate'] . "+ 1 day"));
                    $dos_end_date = date('Y-m-d', strtotime($search_dos['endDate']));
                }

                if ($action == 'null' && $action != null) {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                }

                if ($action != 'null' && $action != null && empty($sorting_name)) {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                } else if ($sort_data == false && $search == 'search'  && $action != 'null' && $action != null) {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                }

                if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                } else if ($sorting_name == false && $sort_data == null && $search == 'search' && $sorting_name != 'null') {

                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);

                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);

                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $dos_sart_date)->where(DB::raw('DATE(import_fields.dos)'), '<=', $dos_end_date);
                }
            }

            if(!empty($searchValue['age_filter']) && $searchValue['age_filter'] != null) {
                $search_age = $searchValue['age_filter'];
        
                if ($action == 'null' && $action != null) {
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
        
                if ($action != 'null' && $action != null && empty($sorting_name)) {
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
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                  if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                  {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                }
        
        
                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {
                  if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                  {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  
        
                } else if ($sort_data == false && $search == 'search'  && $action != 'null' && $action != null) {
                  if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                  {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($action, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                }
        
                if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {
                  if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                  {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                } else if ($sorting_name == false && $sort_data == null && $search == 'search' && $sorting_name != 'null') {
                  if($search_age['from_age'] == 0 && $search_age['to_age'] == 30)
                  {
                    $last_thirty = Carbon::now()->subDay($search_age['to_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>', $last_thirty);
                  }
                  if($search_age['from_age'] == 31 && $search_age['to_age'] == 60)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 61 && $search_age['to_age'] == 90)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 91 && $search_age['to_age'] == 120)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 121 && $search_age['to_age'] == 180)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                  if($search_age['from_age'] == 181 && $search_age['to_age'] == 365)
                  {
                    $to_age = Carbon::now()->subDay($search_age['to_age']);
                    $from_age = Carbon::now()->subDay($search_age['from_age']);
                    $claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                    $claim_count->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                    $selected_claim_data->where(DB::raw('DATE(import_fields.dos)'), '>=', $to_age)->where(DB::raw('DATE(import_fields.dos)'), '<=', $from_age);
                  }
                }
              }

            // if(!isset($searchValue['claim_status']))
            // {
            //   $searchValue['claim_status'] = null;
            // }
            if (!empty($searchValue['payer_name'] && isset($searchValue['payer_name']))) {
              $search_payer_name = $searchValue['payer_name'];
              if ($action == 'null' && $action != null) {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
              }
      
              if ($action != 'null' && $action == null && empty($sorting_name)) {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
              }
      
              if ($sort_data == true && $search == null && $sorting_name == 'null') {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
              } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
              }
      
              if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
              } else if ($sort_data == false && $search == 'search'  && $action != 'null') {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
              }
      
              if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
              } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {
                $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
                $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('sec_ins_name', 'LIKE', '%' . $search_payer_name . '%');
      
                $claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
                $claim_count->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
                $selected_claim_data->orWhere('ter_ins_name', 'LIKE', '%' . $search_payer_name . '%');
              }
            }
            if (!empty($searchValue['claim_status'] && isset($searchValue['claim_status']))) {
                $search_claim_status = $searchValue['claim_status'];
                if ($action == 'null' && $action != null) {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                }

                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {

                    $claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');

                    $selected_claim_data->where('claim_Status', 'LIKE', '%' . $search_claim_status . '%');
                }
            }

            if (!empty($searchValue['patient_name'] && isset($searchValue['patient_name']))) {
                $search_patient_name = $searchValue['patient_name'];
                if ($action == 'null' && $action != null) {

                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                }
                //dd($sort_data); echo "</br>"; false sort_type_close
                // print_r($action); echo "</br>"; exit(); patient_name sort_code

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');

                    $selected_claim_data->where('patient_name', 'LIKE', '%' . $search_patient_name . '%');
                }
            }

            if (!empty($searchValue['responsibility'] && isset($searchValue['responsibility']))) {
                $search_responsibility = $searchValue['responsibility'];
                if ($action == 'null' && $action != null) {

                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->offset($skip)->limit($end);


                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->offset($skip)->limit($end);


                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');

                    $selected_claim_data->where('responsibility', 'LIKE', '%' . $search_responsibility . '%');
                }
            }

            if (!empty($searchValue['total_ar'] && isset($searchValue['total_ar']))) {
              $search_responsibility = $searchValue['total_ar'];
              $OriginalString = trim($searchValue['total_ar']);
              $tot_ar = explode("-",$OriginalString);
              
              $min_tot_ar = $tot_ar[0] - 1.00;
              $max_tot_ar = $tot_ar[1];
      
              if ($action == 'null' && $action != null) {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              }
      
              if ($action != 'null' && $action == null && empty($sorting_name)) {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              }
      
              if ($sort_data == true && $search == null && $sorting_name == 'null') {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              }
      
              if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              } else if ($sort_data == false && $search == 'search'  && $action != 'null') {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              }
      
              if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {
                $claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar])->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);
                $claim_count->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
                $selected_claim_data->whereBetween('total_ar', [$min_tot_ar, $max_tot_ar]);
              }
            }

            if (!empty($searchValue['denial_code']) && isset($searchValue['denial_code'])) {

              $search_denial_code = $searchValue['denial_code'];
      
              if ($action == 'null' && $action != null) {
                $claim_data->where('denial_code', '=',  $search_denial_code)->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=',  $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              }
      
              if ($action != 'null' && $action != null && empty($sorting_name)) {
                $claim_data->where('denial_code', '=',  $search_denial_code)->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              }
      
              if ($sort_data == true && $search == null && $sorting_name == 'null') {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              }
      
              if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              } else if ($sort_data == false && $search == 'search'  && $action != null) {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              }
      
              if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null) {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              } else if ($sorting_name == false && $sort_data == null && $search == 'search') {
                $claim_data->where('denial_code', '=', $search_denial_code)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                $claim_count->where('denial_code', '=', $search_denial_code);
                $selected_claim_data->where('denial_code', '=', $search_denial_code);
              }
            }
      
            if (!empty($searchValue['bill_submit_date']) && $searchValue['bill_submit_date']['startDate'] != null) {
              $search_submit_date = $searchValue['bill_submit_date'];
      
              $bill_start_date = Carbon::createFromFormat('Y-m-d', $search_submit_date['startDate'])->startOfDay();
              $bill_end_date = Carbon::createFromFormat('Y-m-d', $search_submit_date['endDate'])->endOfDay();
      
              if ($action == 'null' && $action != null) {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              }
      
              if ($action != 'null' && $action != null && empty($sorting_name)) {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              }
      
              if ($sort_data == true && $search == null && $sorting_name == 'null') {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              }
      
              if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($action, 'asc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              } else if ($sort_data == false && $search == 'search'  && $action != 'null' && $action != null) {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($action, 'desc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              }
      
              if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              } else if ($sorting_name == false && $sort_data == null && $search == 'search' && $sorting_name != 'null') {
                $claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);
                $claim_count->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
                $selected_claim_data->where(DB::raw('DATE(import_fields.billed_submit_date)'), '>=', $bill_start_date)->where(DB::raw('DATE(import_fields.billed_submit_date)'), '<=', $bill_end_date);
              }
            }

            if (!empty($searchValue['prim_ins_name']) && isset($searchValue['prim_ins_name'])) {

                $search_prim_ins_name = $searchValue['prim_ins_name'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');

                    $selected_claim_data->where('prim_ins_name', 'LIKE', '%' . $search_prim_ins_name . '%');
                }
            }

            if (!empty($searchValue['prim_pol_id']) && isset($searchValue['prim_pol_id'])) {
                $search_prim_pol_id = $searchValue['prim_pol_id'];


                if ($action == 'null' && $action != null) {

                    $claim_data->where('prim_pol_id',  $search_prim_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('prim_pol_id', $search_prim_pol_id)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('prim_pol_id', $search_prim_pol_id);

                    $selected_claim_data->where('prim_pol_id', $search_prim_pol_id);
                }
            }

            if (!empty($searchValue['sec_ins_name']) && isset($searchValue['sec_ins_name'])) {
                $search_sec_ins_name = $searchValue['sec_ins_name'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                }

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');

                    $selected_claim_data->where('sec_ins_name', 'LIKE', '%' . $search_sec_ins_name . '%');
                }
            }

            if (!empty($searchValue['sec_pol_id']) && isset($searchValue['sec_pol_id'])) {
                $search_sec_pol_id = $searchValue['sec_pol_id'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                }
                //dd($sort_data); echo "</br>"; false sort_type_close
                // print_r($action); echo "</br>"; exit(); sec_pol_id sort_code

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('sec_pol_id', $search_sec_pol_id)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('sec_pol_id', $search_sec_pol_id);

                    $selected_claim_data->where('sec_pol_id', $search_sec_pol_id);
                }
            }

            if (!empty($searchValue['ter_ins_name']) && isset($searchValue['ter_ins_name'])) {
                $search_ter_ins_name = $searchValue['ter_ins_name'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->offset($skip)->limit($end);


                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                }
                //dd($sort_data); echo "</br>"; false sort_type_close
                // print_r($action); echo "</br>"; exit(); ter_ins_name sort_code

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%')->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');

                    $selected_claim_data->where('ter_ins_name', 'LIKE', '%' . $search_ter_ins_name . '%');
                }
            }

            if (!empty($searchValue['ter_pol_id']) && isset($searchValue['ter_pol_id'])) {
                $search_ter_pol_id = $searchValue['ter_pol_id'];

                if ($action == 'null' && $action != null) {

                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                }

                if ($action != 'null' && $action == null && empty($sorting_name)) {


                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->offset($skip)->limit($end);


                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                }

                if ($sort_data == true && $search == null && $sorting_name == 'null') {

                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                }


                if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {


                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                } else if ($sort_data == false && $search == 'search'  && $action != 'null') {

                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                }
                //dd($sort_data); echo "</br>"; false sort_type_close
                // print_r($action); echo "</br>"; exit(); ter_pol_id sort_code

                if ($sorting_method == true && $sort_data == null && $search == 'search' && $action == null && !empty($sorting_name)) {

                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($sorting_name, 'asc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                } else if ($sorting_method == false && $sort_data == null && $search == 'search' && !empty($sorting_name)) {


                    $claim_data->where('ter_pol_id', $search_ter_pol_id)->orderBy($sorting_name, 'desc')->offset($skip)->limit($end);

                    $claim_count->where('ter_pol_id', $search_ter_pol_id);

                    $selected_claim_data->where('ter_pol_id', $search_ter_pol_id);
                }
            }

            // if (!empty($searchValue['file_id']) && isset($searchValue['file_id'])) {

            //     $search_file_id = $searchValue['file_id'];

            //     if ($action == 'null' && $action != null) {

            //         $claim_data->where('file_upload_id', '=',  $search_file_id)->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=',  $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     }

            //     if ($action != 'null' && $action != null && empty($sorting_name)) {

            //         $claim_data->where('file_upload_id', '=',  $search_file_id)->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     }

            //     if ($sort_data == true && $search == null && $sorting_name == 'null') {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     } else if ($sort_data == false && $search == null  && $sorting_name == 'null') {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     }


            //     if ($sort_data == true && $search == 'search' && $sort_data != null && $action != 'null' && $action != null) {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($action, 'asc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     } else if ($sort_data == false && $search == 'search'  && $action != null) {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($action, 'desc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     }

            //     if ($sorting_name == true && $sort_data == null && $search == 'search' && $action == null) {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($sorting_method, 'asc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     } else if ($sorting_name == false && $sort_data == null && $search == 'search') {

            //         $claim_data->where('file_upload_id', '=', $search_file_id)->orderBy($sorting_method, 'desc')->offset($skip)->limit($end);

            //         $claim_count->where('file_upload_id', '=', $search_file_id);

            //         $selected_claim_data->where('file_upload_id', '=', $search_file_id);
            //     }
            // }

            $claim_data = $claim_data->get();
            $claim_data = $this->arrange_claim_datas($claim_data);
            // dd(DB::getQueryLog());
            $current_total = $claim_data->count();

            $claim_count = $claim_count->count();


            $selected_claim_data = $selected_claim_data->get();

            $selected_count = $selected_claim_data->count();
        }



        if (isset($claim_data)) {
            foreach ($claim_data as $key => $value) {
                $dos = strtotime($claim_data[$key]['dos']);

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


                $claim_data[$key]['touch'] = Claim_note::where('claim_id', $value['claim_no'])->count();
            }
        }

        return response()->json([
            'data'  => $claim_data,
            'total' => $claim_count,
            'current_total' => isset($current_total) ? $current_total : 0,
            'skip' => isset($skip) ? $skip : 0,
            'selected_claim_data' => isset($selected_claim_data) ? $selected_claim_data : 0,
        ]);
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
