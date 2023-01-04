<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['api','SessionHandler']
], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('getPermissions', 'AuthController@getPermissions');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('register', 'Userregister@register');
    Route::post('validatemail', 'AuthController@validatemail');
    Route::post('getroles', 'Userregister@getroles');
    Route::post('create_roles', 'RoleController@create_roles');
    Route::post('update_roles', 'RoleController@update_roles');
    Route::post('validateusername', 'Userregister@validateusername');
    Route::post('getprofile', 'ProfileController@getprofile');
    Route::post('setimage', 'ProfileController@setimage');
    Route::post('updateprofile', 'ProfileController@updateprofile');
    Route::post('upload', 'ImportController@upload');
    Route::post('get_upload_table_page', 'ImportController@get_upload_table_page');
    Route::post('getfile', 'ImportController@getfile');
    Route::post('getfields', 'SettingsController@getfields');
    Route::post('setsetting', 'SettingsController@setsetting');
    // Route::post('get_settingsearch', 'SettingsController@get_settingsearch');
    Route::post('template', 'ImportController@template');
    Route::post('checktoken', 'AuthController@verifytoken');
    Route::post('createpractice', 'PracticeController@createpractice');
    Route::post('createclaim', 'ImportController@createclaim');
    Route::post('updatemismatch', 'ImportController@updatemismatch');
    Route::post('updateingnore', 'ImportController@updateingnore');
    Route::post('overwrite', 'ImportController@overwrite');
    Route::post('overwrite_all', 'ImportController@overwrite_all');
    Route::post('getclaim_details', 'CreateworkorderController@getclaim_details');

    //my seting by muhammed
    Route::post('team_claims', 'CreateworkorderController@team_claims');

    Route::post('process_note', 'CreateworkorderController@process_note');
    Route::post('getnotes', 'CreateworkorderController@getnotes');
    Route::post('claim_note', 'CreateworkorderController@claim_note');
    Route::post('qc_note', 'CreateworkorderController@qc_note');
    Route::post('create_category', 'SettingsController@create_category');
    Route::post('get_category', 'SettingsController@get_category');
    Route::post('create_questions', 'SettingsController@create_questions');
    Route::post('update_category', 'SettingsController@update_category');
    Route::post('update_questions', 'SettingsController@update_questions');
    Route::post('create_followup', 'CreateworkorderController@create_followup');
    Route::post('get_followup', 'CreateworkorderController@get_followup');
    Route::post('get_associates', 'CreateworkorderController@get_associates');
    Route::post('create_workorder', 'CreateworkorderController@create_workorder');
    Route::post('check_claims', 'CreateworkorderController@check_claims');
    Route::post('get_table_page', 'ImportController@get_table_page');
    Route::post('get_audit_table_page', 'ImportController@get_audit_table_page');
    Route::post('get_related_calims', 'ImportController@get_related_calims');
    Route::post('create_statuscode', 'SettingsController@create_statuscode');
    Route::post('get_status_codes', 'SettingsController@get_status_codes');
    Route::post('create_substatus_code', 'SettingsController@create_substatus_code');
    Route::post('update_status_code', 'SettingsController@update_status_code');
    Route::post('create_followup_data', 'ClaimProcessController@create_followup');
    Route::post('get_audit_claim_details', 'AuditController@get_audit_claim_details');
    Route::post('get_auditors', 'AuditController@get_auditors');
    // Route::post('create_audit_workorder', 'AuditController@create_audit_workorder');
    Route::post('get_workorder', 'CreateworkorderController@get_workorder');
    
    Route::post('get_workorder_details', 'CreateworkorderController@get_workorder_details');
    Route::post('get_ca_claims', 'Client_assistanceController@get_ca_claims');
    Route::post('get_user_list', 'Client_assistanceController@get_user_list');
    // Route::post('create_ca_workorder', 'Client_assistanceController@create_ca_workorder');
    Route::post('client_note', 'CreateworkorderController@client_note');
    Route::post('get_client_notes', 'CreateworkorderController@get_client_notes');
    Route::post('fetch_export_data', 'Client_assistanceController@fetch_export_data');
    Route::post('get_rcm_claims', 'RcmController@get_rcm_claims');

    Route::post('get_rcm_claims_sorting', 'RcmController@get_rcm_claims_sorting');
    
    Route::post('get_rcm_team_list', 'RcmController@get_rcm_team_list');
    // Route::post('create_rcm_workorder', 'RcmController@create_rcm_workorder');
    Route::post('fetch_claim_export_data', 'ImportController@fetch_export_data');
    
    Route::post('fetch_wo_export_data', 'CreateworkorderController@fetch_wo_export_data');
    Route::post('fetch_rcm_export_data', 'RcmController@fetch_export_data');
    Route::post('fetch_audit_export_data', 'AuditController@fetch_export_data');
    Route::post('fetch_followup_export_data', 'CreateworkorderController@fetch_export_data');
    Route::post('doc_name_validity', 'DocumentController@doc_name_validity');
    Route::post('upload_document_file', 'DocumentController@upload_file');
    Route::post('get_document_list', 'DocumentController@get_document_list');
    Route::post('download_doc_file', 'DocumentController@download_doc_file');
    Route::post('delete_doc_file', 'DocumentController@delete_doc_file');
    Route::post('get_line_items', 'ImportController@get_line_items');
    Route::post('reassign_calim', 'ClaimProcessController@reassign_calim');
    Route::post('check_edit_val', 'ClaimProcessController@check_edit_val');
    Route::post('check_notes_update', 'ClaimProcessController@check_notes_update');
    Route::post('get_note_details', 'ClaimProcessController@get_note_details');
    Route::post('get_claim_status', 'ClaimProcessController@get_claim_status');
    Route::post('get_users_list', 'Userregister@get_users_list');
    Route::post('update_user_details', 'Userregister@update_user_details');
    Route::post('get_root_cause', 'SettingsController@get_root_cause');
    Route::post('create_root_cause', 'SettingsController@create_root_cause');
    Route::post('get_error_type', 'SettingsController@get_error_type');
    Route::post('create_error_type', 'SettingsController@create_error_type');
    Route::post('get_audit_codes', 'AuditController@get_audit_codes');
    Route::post('auto_assign_claims', 'AuditController@auto_assign_claims');
    Route::get('get_practice_stats', 'SettingsController@get_practice_stats');
    Route::post('update_prac_settings', 'SettingsController@update_prac_settings');
    Route::post('get_work_order_details', 'NotificationController@get_work_order_details');
    Route::post('delete_upload_file', 'ImportController@delete_upload_file');
    Route::post('process_upload_file', 'ImportController@process_upload_file');
    Route::post('get_claim_graph_stats', 'GraphController@get_claim_graph_stats');
    Route::post('get_detailed', 'GraphController@fetch_assoc_detail');
    Route::post('get_claim_table_stats', 'GraphController@get_claim_table_stats');
    Route::post('get_summary_details', 'GraphController@get_summary_details');
    Route::post('get_prod_qual', 'GraphController@get_prod_qual');
    Route::get('get_month_details', 'GraphController@get_month_details');
    Route::post('process_weekly_data', 'GraphController@process_weekly_data');
    Route::post('createVendor', 'AdminController@createVendor');
    Route::get('getVendor', 'AdminController@getVendor');
    Route::post('updateVendor', 'AdminController@updateVendor');
    Route::post('getPractices', 'PracticeController@getPractices');
    Route::post('selectPractice', 'PracticeController@selectPractice');
    Route::get('getLogs', 'AdminController@getLogs');
    Route::POST('viewLog', 'AdminController@viewLog');
    Route::get('getPracticesList', 'Userregister@getPracticesList');
    Route::POST('get_audit_graph', 'GraphController@get_audit_graph');
	
	Route::post('getSummaryDetails', 'DashboardController@getSummaryDetails');

    Route::post('get_file_ready_count','ImportController@get_file_ready_count');
	
	/* 
	Author : selvakumar
	Date : Feb-06-2020
	Desc : Handling Dashboard page

	*/
	
	Route::get('dashboard','DashboardController@dashboard');
	Route::post('reassign','DashboardController@reassignManager');
	Route::post('closeClaims','DashboardController@closeClaims');
    Route::post('closedClaims', 'DashboardController@closedClaims');

    Route::post('template_edit', 'DashboardController@template_edit');

    Route::post('insurance_name_list', 'DashboardController@insurance_name_list');
    Route::post('get_insurance', 'DashboardController@get_insurance');
    Route::post('update_followup_template', 'DashboardController@update_followup_template');
	
    Route::post('getclaim_details_reassign', 'DashboardController@getclaim_details_reassign');

    Route::post('followup_process_notes_delete', 'CreateworkorderController@followup_process_notes_delete');

    Route::post('audit_process_notes_delete', 'CreateworkorderController@audit_process_notes_delete');

    Route::post('closed_followup_process_notes_delete', 'CreateworkorderController@closed_followup_process_notes_delete');

    Route::post('reasigned_followup_process_notes_delete', 'CreateworkorderController@reasigned_followup_process_notes_delete');

    Route::post('closed_audit_process_notes_delete', 'CreateworkorderController@closed_audit_process_notes_delete');

    Route::post('claims_order_list', 'ClaimProcessController@claims_order_list');

    Route::post('getclaim_details_order_list', 'CreateworkorderController@getclaim_details_order_list');

    Route::post('getclaim_details_order_list', 'CreateworkorderController@getclaim_details_order_list');

    Route::post('audit_assigned_order_list', 'AuditController@audit_assigned_order_list');

    Route::post('claims_tooltip', 'DashboardController@claims_tooltip');

    route::post('get_claimno', 'DashboardController@get_claimno');

    route::post('get_audit_claimno', 'DashboardController@get_audit_claimno');

    route::post('get_rcm_claimno', 'DashboardController@get_rcm_claimno');

    route::post('get_client_claimno', 'DashboardController@get_client_claimno');
    
    route::post('get_buyer', 'ReportController@get_buyer');    

    route::post('get_report_claims', 'ReportController@get_report_claims');

    route::post('report_search', 'ReportController@report_search');
	
	/* Report Export Route */
	Route::post('report_export_claims', 'ReportController@report_export_claims');

    Route::post('fetch_create_claims_export_data', 'ExportController@fetch_create_claims_export_data');

    Route::post('fetch_followup_claims_export_data', 'ExportController@fetch_followup_claims_export_data');

    Route::post('fetch_audit_claims_export_data', 'ExportController@fetch_audit_claims_export_data'); 

    Route::post('fetch_billing_claims_export_data', 'ExportController@fetch_billing_claims_export_data'); 

    Route::post('fetch_client_claims_export_data', 'ExportController@fetch_client_claims_export_data'); 

    Route::post('fetch_work_order_export_data', 'ExportController@fetch_work_order_export_data');

    Route::post('fetch_create_claims_export_data_pdf', 'ExportController@fetch_create_claims_export_data_pdf');

    Route::post('fetch_create_claims_export_data_pdf', 'ExportController@fetch_create_claims_export_data_pdf');

    Route::post('fetch_followup_claims_export_data_pdf', 'ExportController@fetch_followup_claims_export_data_pdf');

    Route::post('fetch_audit_claims_export_data_pdf', 'ExportController@fetch_audit_claims_export_data_pdf');

    Route::post('fetch_billing_claims_export_data_pdf', 'ExportController@fetch_billing_claims_export_data_pdf');

    Route::post('fetch_client_claims_export_data_pdf', 'ExportController@fetch_client_claims_export_data_pdf');

    Route::post('fetch_work_order_export_data_pdf', 'ExportController@fetch_work_order_export_data_pdf');

    Route::post('deletetemplate', 'DashboardController@deletetemplate');

    Route::post('get_setting_importsearch', 'SettingsController@get_setting_importsearch');

    Route::post('getAlertNotification', 'DashboardController@getAlertNotification');

    
    Route::post('update_auto_close_claims', 'ImportController@updateAutoClose');
    Route::post('assigned_claim_list', 'AssignedListController@assigned_claim_list');
    Route::post('create_error_parameters', 'SettingsController@create_error_parameters');
    Route::post('create_fyi_parameters', 'SettingsController@create_fyi_parameters');
    Route::post('get_error_param_codes', 'AuditController@get_error_param_codes');
    Route::post('get_fyi_param_codes', 'AuditController@get_fyi_param_codes');
});