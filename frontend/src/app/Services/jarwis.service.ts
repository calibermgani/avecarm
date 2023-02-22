import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import 'rxjs/add/observable/forkJoin';
import {Observable} from "rxjs/Observable";
import { map, filter, switchMap } from 'rxjs/operators';
import { forkJoin } from 'rxjs';
import { environment } from 'src/environments/environment';
import * as _ from 'lodash';

@Injectable({
  providedIn: 'root'
})
export class JarwisService {
  // private baseUrl = 'http://:8000/api';
 // private baseUrl = 'http://localhost:8000/api';
  //private baseUrl = 'http://127.0.0.1:8000/api';
  private baseUrl = `${environment.apiUrl}`;
  //private baseUrl = 'http://35.226.72.203/avecarm/backend/public/index.php/api';
  constructor(private http: HttpClient) {
  }


common_url(url,data)
{
  if(localStorage.getItem('role') != 'Admin' )
  {
    data['practice_dbid']=localStorage.getItem('practice_id');
  }

//console.log("I=P",data);

  return this.http.post(`${this.baseUrl}/`+url, data);
}



login(data) {
    return this.http.post(`${this.baseUrl}/login`, data);
      //  let response= this.common_url('login',data);

  //  return response;

  }

register(data,id) {
  data={form_data:data,user_id:id};
  // console.log(data);
    return this.http.post(`${this.baseUrl}/register`, data);
  }

validatemail(data) {
  	return this.http.post(`${this.baseUrl}/validatemail`, data);
  }

getrole() {
	return this.http.post(`${this.baseUrl}/getroles`,{});
}

validateusername(data)
{
  	return this.http.post(`${this.baseUrl}/validateusername`, data);
}

validateUsername(data,data2)
{
  data={data,id:data2};
  return this.http.post(`${this.baseUrl}/validateusername`, data);
}

// getclaims(data)
// {
//   //data={token:data2,id:data};
//   return this.http.post(`${this.baseUrl}/getclaims`, data);
// }

upload(formdata)
{
	console.log(formdata);
  // let data = {formData:formdata, practice_dbid : localStorage.getItem('practice_id')};



  return this.http.post(`${this.baseUrl}/upload`,formdata);

  // let data=formdata;
  // let response= this.common_url('upload',data);

  // return response;
}

uploadcloseclaim(formvalues){
  console.log(formvalues);
  return this.http.post(`${this.baseUrl}/update_auto_close_claims`,formvalues);
}

getprofile(data)
{
  data = {id: data};
  return this.http.post(`${this.baseUrl}/getprofile`, data);
}

  setimage(data,dataid)
  {
    data = {image: data,id: dataid};
    return this.http.post(`${this.baseUrl}/setimage`, data);
  }

updateprofile(data,dataid)
{
 data={data,id: dataid};
 return this.http.post(`${this.baseUrl}/updateprofile`, data);
}

getfile(data)
{
  data={id:data,practice_dbid:localStorage.getItem('practice_id')};
  return this.http.post(`${this.baseUrl}/getfile`, data,{responseType: 'blob'});
}

getfields(data, searchValue)
{
  data={set:data, searchValue:searchValue, practice_dbid : localStorage.getItem('practice_id')};
  return this.http.post(`${this.baseUrl}/getfields`,data);
}

setsetting(data)
{
  data={set:data, practice_dbid : localStorage.getItem('practice_id')};
  return this.http.post(`${this.baseUrl}/setsetting`,data);
}
template() {
	let data = {practice_dbid : localStorage.getItem('practice_id')};
	return this.http.post(`${this.baseUrl}/template`,data);
}

createpractice(data,data1)
{
  data={uid:data1,data:data};

  return this.http.post(`${this.baseUrl}/createpractice`,data);
}

createnewclaims(data,data1,id)
{
  data={claim:data,file:data1,user_id:id};
  // console.log("Create_dta",data);
  // return this.http.post(`${this.baseUrl}/createclaim`,data);

  let response= this.common_url('createclaim',data);

  return response;
}

mismatch(data)
{
  data={info:data};
return this.http.post(`${this.baseUrl}/updatemismatch`,data);
}

overwrite(data, user_id)
{
  data={info:data, user_id:user_id};
// return this.http.post(`${this.baseUrl}/overwrite`,data);

let response= this.common_url('overwrite',data);

return response;

}

overwrite_all(data, user_id)
{
  data={info:data, user_id:user_id};
  // return this.http.post(`${this.baseUrl}/overwrite_all`,data);

  let response= this.common_url('overwrite_all',data);

return response;

}
getdata(claims,id,reassign){
  let data={user_id:id,claim_no:claims,reassign:reassign}
  let response= this.common_url('reassign',data);
return response;
}
get_closed_claims(claims,id){
  let data={user_id:id,claim_no:claims}
  let response= this.common_url('closedClaims',data);
return response;
}
getclaim_details(id,page,page_count,type,sort_code,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search)
{
  console.log(search);
  let data={user_id:id,page_no:page,count:page_count,claim_type:type,sort_code:sort_code,sort_type:sort_type,sorting_name:sorting_name,sorting_method:sorting_method,assign_claim_searh:assign_claim_searh,reassign_claim_searh:reassign_claim_searh,closed_claim_searh:closed_claim_searh,search:search}
  // return this.http.post(`${this.baseUrl}/getclaim_details`, data);
  let response= this.common_url('getclaim_details',data);

  return response;
}
// getclaim_details_sort(id,page,page_count,type,sort_code,sort_type)
// {
//   let data={user_id:id,page_no:page,count:page_count,claim_type:type,sort_code:sort_code,sort_type:sort_type}
//   // return this.http.post(`${this.baseUrl}/getclaim_details`, data);
//   let response= this.common_url('getclaim_details',data);

//   return response;
// }

getclaim_details_reasign(id,page,page_count,type)
{
  let data={user_id:id,page_no:page,count:page_count,claim_type:type}
  // return this.http.post(`${this.baseUrl}/getclaim_details`, data);
  let response= this.common_url('getclaim_details_reassign',data);

  return response;
}

process_note(id,notes,data,func_type, claim_status)
{
  data={userid:id,process_note:notes,claim_det:data,func:func_type, claim_status:claim_status};
  // return this.http.post(`${this.baseUrl}/process_note`, data);

  let response= this.common_url('process_note',data);
  return response;
}

getnotes(data)
{
  data={claimid:data};
  // return this.http.post(`${this.baseUrl}/getnotes`, data);
  let response= this.common_url('getnotes',data);
  return response;

}

claim_note(id,notes,data,func_type)
{
  data={userid:id,claim_note:notes,claim_det:data,func:func_type};
  // return this.http.post(`${this.baseUrl}/claim_note`, data);

  let response= this.common_url('claim_note',data);
  return response;
}


qc_note(id,notes,data,func_type)
{
  data={userid:id,qc_note:notes,claim_det:data,func:func_type};
  //console.log(data);
  // return this.http.post(`${this.baseUrl}/qc_note`, data);
  let response= this.common_url('qc_note',data);
  return response;
}

create_category(data,id)
{
  data={data:data,id:id}
  // return this.http.post(`${this.baseUrl}/create_category`, data);
  let response= this.common_url('create_category',data);
  return response;
}

get_category(data)
{
  data={id:data};
  // return this.http.post(`${this.baseUrl}/get_category`, data);
  let response= this.common_url('get_category',data);
  return response;
}
create_questions(data,id,userid)
{
  data={data:data,cat_id:id,user_id:userid};
  // return this.http.post(`${this.baseUrl}/create_questions`, data);
  let response= this.common_url('create_questions',data);
  return response;
}

update_category(data,id,data_id)
{
  data={data:data,id:id,upd_id:data_id}
  // return this.http.post(`${this.baseUrl}/update_category`, data);
  let response= this.common_url('update_category',data);
  return response;
}

update_questions(data,userid,id)
{
  data={data:data,upd_id:id,user_id:userid};
  // return this.http.post(`${this.baseUrl}/update_questions`, data);
  let response= this.common_url('update_questions',data);
  return response;
}

create_followup(id,questions,data,claim,category)
{
  data={user_id:id,question_data:questions,form_data:data,claim_no:claim,cat:category};
  let response= this.common_url('create_followup',data);
  return response;
}

update_followup_template(user_id, temp_id,questions,data,claim,category){
  data={user_id:user_id,temp_id:temp_id,question_data:questions,form_data:data,claim_no:claim,cat:category};
  let response = this.common_url('update_followup_template', data);
  return response;
}

get_followup(data)
{
  data={claim_no:data};
  // return this.http.post(`${this.baseUrl}/get_followup`, data);
  let response= this.common_url('get_followup',data);
  return response;
}
get_associates(data)
{
  // return this.http.post(`${this.baseUrl}/get_associates`, data);

 data={userId:data}
         let response= this.common_url('get_associates',data);
   return response;
}

get_associate_name(data){
  data = {user_id:data};
  let response= this.common_url('get_associate_name',data);
   return response;
}
create_workorder(id,data,claim,wo_type)
{
  data={id:id,work:data,claim:claim,type:wo_type};
  // return this.http.post(`${this.baseUrl}/create_workorder`, data);

  let response= this.common_url('create_workorder',data);

  return response;


}
check_claims(data)
{
  data={claim:data}
  // return this.http.post(`${this.baseUrl}/check_claims`, data);
  let response= this.common_url('check_claims',data);

  return response;
}

get_table_page(data,page,page_count,type,sorting_name,sorting_method,createsearch,search)
{
   data={page_no:page,count:page_count,filter:data,sort_type:type,sorting_name:sorting_name,sorting_method:sorting_method,createsearch:createsearch,search:search};
   let response= this.common_url('get_table_page',data);
   return response;
}

get_table_page_sorting(data,page,page_count,type)
{
  data={page_no:page,count:page_count,filter:data,sort_type:type};
   let response= this.common_url('get_table_page_sorting',data);
   return response;
}

get_audit_table_page(data,page,page_count,type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_searh,search)
{
  data={page_no:page,count:page_count,filter:data,sort_type:type,sorting_name:sorting_name,sorting_method:sorting_method,assign_claim_searh:assign_claim_searh,reassign_claim_searh:reassign_claim_searh,closed_claim_searh:closed_claim_searh,audit_claim_searh:audit_claim_searh,search:search};
  let response= this.common_url('get_audit_table_page',data);
  return response;
}


get_related_calims(data,type,user)
{
  data={claim_no:data,type:type,user_id:user};
  let response= this.common_url('get_related_calims',data);
  return response;
}

get_upload_table_page(page,page_count)
{
  let data={page_no:page,count:page_count}
  let response= this.common_url('get_upload_table_page',data);
  return response;
}

create_status_code(data,id)
{
  data={data:data,id:id}
  // return this.http.post(`${this.baseUrl}/create_statuscode`, data);

  let response= this.common_url('create_statuscode',data);
  return response;
}

get_status_codes(data,mod)
{
  data={id:data,module:mod};
  // return this.http.post(`${this.baseUrl}/get_status_codes`, data);

  let response= this.common_url('get_status_codes',data);

  return response;
}
create_sub_status(data,id,status_id)
{
  data={data:data,id:id,status:status_id}
  // return this.http.post(`${this.baseUrl}/create_substatus_code`, data);

  let response= this.common_url('create_substatus_code',data);
  return response;

}
update_status_code(data,id,user)
{
  data={upd_data:data,upd_id:id,user_id:user,type:'statuscode'}
  // return this.http.post(`${this.baseUrl}/update_status_code`, data);

  let response= this.common_url('update_status_code',data);
  return response;
}
update_sub_status_code(data,id,user)
{
  data={upd_data:data,upd_id:id,user_id:user,type:'sub_statuscode'}
  // return this.http.post(`${this.baseUrl}/update_status_code`, data);

  let response= this.common_url('update_status_code',data);
  return response;
}

finish_followup(id,data,claim,type)
{
  data={user_id:id,status_code:data.status,audit_err_code:data.audit_err_code,claim_det:claim,followup_type:type};
  // return this.http.post(`${this.baseUrl}/create_followup_data`, data);
  let response= this.common_url('create_followup_data',data);
  return response;
}

get_audit_claim_details(id,page,page_count,claim_type,sort_code,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search)
{
  let data={user_id:id,page_no:page,count:page_count,type:claim_type,sort_code:sort_code,sort_type:sort_type,sorting_name:sorting_name,sorting_method:sorting_method,assign_claim_searh:assign_claim_searh,reassign_claim_searh:reassign_claim_searh,closed_claim_searh:closed_claim_searh,search};
  // return this.http.post(`${this.baseUrl}/get_audit_claim_details`, data);
  let response= this.common_url('get_audit_claim_details',data);

  return response;
}
public get_auditors(data)
{
   data={user_id:data}
  // return this.http.post(`${this.baseUrl}/get_auditors`, data);

  let response= this.common_url('get_auditors',data);

  return response;
}

// create_audit_workorder(id,data,workorder)
// {
//   data={id:id,assign_data:data,wo_details:workorder};
//   return this.http.post(`${this.baseUrl}/create_audit_workorder`, data);
// }

get_workorder(filter,from,to,wo_type,page_no,sort_type,sort_data,sorting_name,sorting_method,closedClaimsFind,workordersearch,search,)
{
  let data={filter_type:filter,from_date:from,to_date:to,type:wo_type,page:page_no,sort_type:sort_type,sort_data:sort_data,sorting_name:sorting_name,sorting_method:sorting_method,closedClaimsFind:closedClaimsFind,workordersearch:workordersearch,search:search};
  let response= this.common_url('get_workorder',data);
  return response;

}
get_workorder_details(id)
{
  let data={wo_id:id};
  let response= this.common_url('get_workorder_details',data);

  return response;

}
get_ca_claims(id,page,page_count,claim_type,sort_data,sort_type,sorting_name,sorting_method,claim_searh,search)
{
  let data={user_id:id,page_no:page,count:page_count,type:claim_type,sort_data:sort_data,sort_type:sort_type,sorting_name:sorting_name,sorting_method:sorting_method,claim_searh:claim_searh,search:search}
  let response= this.common_url('get_ca_claims',data);

  return response;
}
get_user_list(data)
{
  data={user_id:data}
  return this.http.post(`${this.baseUrl}/get_user_list`, data);
}
// create_ca_workorder(id,data)
// {
//   data={id:id,assign_data:data};
//   return this.http.post(`${this.baseUrl}/create_ca_workorder`, data);
// }
client_notes(id,notes,data,func_type)
{
  data={practice_dbid:localStorage.getItem('practice_id'),userid:id,client_note:notes,claim_det:data,func:func_type};
  return this.http.post(`${this.baseUrl}/client_note`, data);
}
fetch_export_data(filter,s_code,user_id)
{
  let data={filter:filter,status:s_code,user:user_id};
  return this.http.post(`${this.baseUrl}/fetch_export_data`, data);
}
get_rcm_claims(id,page,page_count,sort_data,sort_type,sorting_name,sorting_method,claim_searh,search)
{
  let data={user_id:id,page_no:page,count:page_count,sort_data:sort_data,sort_type:sort_type,sorting_name:sorting_name,sorting_method:sorting_method,claim_searh:claim_searh,search:search}
  let response= this.common_url('get_rcm_claims',data);
  return response;
}

get_rcm_claims_sorting(id,page,page_count, type, filter){
  let data={user_id:id,page_no:page,count:page_count,type:type, filter:filter}
  let response= this.common_url('get_rcm_claims_sorting',data);
  return response;
}

get_rcm_team_list(data)
{
  data={user_id:data}
  return this.http.post(`${this.baseUrl}/get_rcm_team_list`, data);
}

// create_rcm_workorder(id,data)
// {
//   data={id:id,assign_data:data};
//   // console.log(data);
//   return this.http.post(`${this.baseUrl}/create_rcm_workorder`, data);
// }
// get_client_notes(data)
// {
//
//   data={claimid:data};
//   return this.http.post(`${this.baseUrl}/get_client_notes`, data);
// }

get_process_associates(data,claim_no,module)
{
  let res1 = this.get_status_codes(data,module);
        let res2 =  this.get_associates(data);
        // let res3 = this.get_note_details(claim_no)
        return forkJoin([res1, res2]);
}

get_note_details(claim_no)
{
  let data={claim:claim_no}
  // console.log("Notes details i/p",claim)
  // return this.http.post(`${this.baseUrl}/get_note_details`, claim);

  let response= this.common_url('get_note_details',data);

  return response;
}


claim_status_data_fork(data,page,page_count,type,id,sorting_name,sorting_method,createsearch,search)
{
  let status_data= this.get_status_codes(id,'all');
  let page_data=this.get_table_page(data,page,page_count,type,sorting_name,sorting_method,createsearch,search)

  return forkJoin([page_data,status_data]);
}

fetch_calim_export_data(filter,s_code,user_id)
{
    let data={filter:filter,status:s_code,user:user_id};
  return this.common_url('fetch_claim_export_data',data);
}



fetch_wo_export_data(filter,s_code,wo_type,user_id)
{
  let data={filter:filter,status:s_code,user:user_id,wo:wo_type};
  return this.common_url('fetch_claim_export_data',data);
}

fetch_rcm_export_data(filter,s_code,user_id)
{
    let data={filter:filter,status:s_code,user:user_id};
  return this.http.post(`${this.baseUrl}/fetch_rcm_export_data`, data);
}

fetch_audit_export_data(filter,s_code,user_id)
{
    let data={filter:filter,status:s_code,user:user_id};
  return this.http.post(`${this.baseUrl}/fetch_audit_export_data`, data);
}

fetch_followup_export_data(filter,s_code,user_id)
{
    let data={filter:filter,status:s_code,user:user_id};
  return this.http.post(`${this.baseUrl}/fetch_followup_export_data`, data);
}

doc_name_validity(data,type,doc_id)
{
  data={name:data,check_type:type,id:doc_id};
  return this.http.post(`${this.baseUrl}/doc_name_validity`, data);
}

upload_document_file(data)
{
  return this.http.post(`${this.baseUrl}/upload_document_file`, data);

}

get_document_list(page,page_count,searchValue,sort_by,sort_name)
{
  let data={page_no:page,count:page_count,searchValue:searchValue,sort_by:sort_by,sort_name:sort_name};
  // return this.http.post(`${this.baseUrl}/get_document_list`, data);
  let response= this.common_url('get_document_list',data);

  return response;


}
download_doc_file(id,file)
{
  let data={doc_id:id,file_data:file};
  // return this.http.post(`${this.baseUrl}/download_doc_file`, data,{responseType: 'blob'});

  let response= this.common_url('download_doc_file', data);
  return response;
}
delete_doc_file(nos,data,page,count)
{
  data={file_name:data,id:nos,page_no:page,page_count:count};

  let response= this.common_url('delete_doc_file',data);
  return response;
}

get_selected_claim_details_fork(data)
{
  let res1 = this.get_related_calims(data,'claim',null);
  let res2 = this.get_line_items(data);
  return forkJoin([res1, res2]);
}

get_line_items(data)
{
  data={claim_no:data['claim_no']};
// return this.http.post(`${this.baseUrl}/get_line_items`, data);
let response= this.common_url('get_line_items',data);
return response;
}


reassign_calim(claim,id,mod_type)
{
  let data={claim_data:claim,user_id:id,type:mod_type};
// return this.http.post(`${this.baseUrl}/reassign_calim`, data);
let response= this.common_url('reassign_calim',data);
return response;
}

check_edit_val(claim,type)
{
  let data={claim_data:claim,type:type};
// return this.http.post(`${this.baseUrl}/check_edit_val`, data);
let response= this.common_url('check_edit_val',data);
return response;
}

check_notes_update(cliam_data,type,notes)
{
  let data={claim:cliam_data,note_type:type,note_data:notes};
  // console.log("I/p data",data);
  // return this.http.post(`${this.baseUrl}/check_notes_update`, data);
  let response= this.common_url('check_notes_update',data);
  return response;
}

get_users_list(user)
{
  let data={user_id:user,practicedb_id:localStorage.getItem('practice_id')};
  // console.log(data);
  //return this.http.post(`${this.baseUrl}/get_users_list`, data);
  let response= this.common_url('get_users_list', data);
  return response;
}

get_practice_user_list(user)
{
  let data={user_id:user,practicedb_id:localStorage.getItem('practice_id')};
  // console.log(data);
  //return this.http.post(`${this.baseUrl}/get_users_list`, data);
  let response= this.common_url('get_practice_user_list', data);
  return response;
}

// getting user list from aims database //
get_aimsusers_list(user)
{
  let data={token:'1a32e71a46317b9cc6feb7388238c95d',
    department_id:1};
  // console.log(data);
  let response = this.http.post('http://127.0.0.1:8080/api/product_api_v1/get_user_list', data, {headers: {'Content-Type': 'application/json',
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'POST'}});
  console.log(response);
  //let response= this.common_url('get_users_list', data);
  return response;
}


update_user_details(data,dataid,user_id)
{
 data={data,id: dataid,update_id:user_id};
 return this.http.post(`${this.baseUrl}/update_user_details`, data);


}

get_root_cause(data,mod)
{
  data={id:data,module:mod};
  // return this.http.post(`${this.baseUrl}/get_root_cause`, data);
  let response= this.common_url('get_root_cause',data);
  return response;
}

create_root_cause(user_id,data,func){
data={id:user_id,form_data:data,type:func};
// return this.http.post(`${this.baseUrl}/create_root_cause`, data);
let response= this.common_url('create_root_cause',data);
return response;
}

get_error_type(data,mod)
{
  data={id:data,module:mod};
  // return this.http.post(`${this.baseUrl}/get_error_type`, data);
  let response= this.common_url('get_error_type',data);
  return response;
}

create_error_type(user_id,data,func){
data={id:user_id,form_data:data,type:func};
// return this.http.post(`${this.baseUrl}/create_error_type`, data);
let response= this.common_url('create_error_type',data);
return response;
}

getDropDownText(id, object){
  const selObj = _.filter(object, function (o) {
      return (_.includes(id,o.id));
  });
  return selObj;
}

get_audit_codes(data)
{
  data={id:data,practice_dbid : localStorage.getItem('practice_id')};
  // return this.http.post(`${this.baseUrl}/get_audit_codes`, data);
  let response= this.common_url('get_audit_codes',data);
  return response;
}

auto_assign_claims(data)
{
  data={id:data};
  // return this.http.post(`${this.baseUrl}/auto_assign_claims`, data);

  let response= this.common_url('auto_assign_claims',data);
  return response;
}

get_practice_stats()
{
  return this.http.get(`${this.baseUrl}/get_practice_stats`);
}

update_prac_settings(data,id)
{
data={form_data:data,user_id:id};
return this.http.post(`${this.baseUrl}/update_prac_settings`, data);
}

get_work_order_details(id)
{
  let data={user_id:id};
// return this.http.post(`${this.baseUrl}/get_work_order_details`, data);

       let response= this.common_url('get_work_order_details',data);

   return response;
}

delete_upload_file(id,user)
{
  let data={file_id:id,user_id:user}
  // return this.http.post(`${this.baseUrl}/delete_upload_file`, data);
  let response= this.common_url('delete_upload_file',data);

  return response;
}

process_upload_file(id,user)
{
  let data={file_id:id,user_id:user, practice_dbid : localStorage.getItem('practice_id')}
  // return this.http.post(`${this.baseUrl}/process_upload_file`, data);

  let response= this.common_url('process_upload_file',data);

  return response;
}
get_claim_graph_stats(id)
{
  let data={user:id};
  // return this.http.post(`${this.baseUrl}/get_claim_graph_stats`,data);
  let response= this.common_url('get_claim_graph_stats',data);

  return response;
}
get_claim_table_stats(id)
{
  let data={user:id};
  // return this.http.post(`${this.baseUrl}/get_claim_table_stats`,data);

  let response= this.common_url('get_claim_table_stats',data);
    return response;
}
get_detailed(id)
{
  let data={user:id};
  // return this.http.post(`${this.baseUrl}/get_detailed`,data);
  let response= this.common_url('get_detailed',data);
    return response;
}

get_graph_stats_fork(id)
{
  let res1 = this.get_claim_graph_stats(id);
  let res2 = this.get_claim_table_stats(id);
  return forkJoin([res1, res2]);
}

get_summary_details(id)
{
  let data={user:id};
  // return this.http.post(`${this.baseUrl}/get_summary_details`,data);

  let response= this.common_url('get_summary_details',data);
  return response;
}

get_prod_qual(id,days)
{
  let data={user:id, day:days};
  // return this.http.post(`${this.baseUrl}/get_prod_qual`,data);

  let response= this.common_url('get_prod_qual',data);
  return response;
}

get_month_details()
{
  return this.http.get(`${this.baseUrl}/get_month_details`);
}

// get_assigned_claims(id,page,page_count)
// {
//   let data={user_id:id,page_no:page,count:page_count}
//   return this.http.post(`${this.baseUrl}/get_assigned_claims`, data);
// }

fork_user_month_det(id,assoc_id)
{
  let res1 = this.get_detailed(assoc_id);
  let res2 = this.process_weekly_data(assoc_id);
  return forkJoin([res1, res2]);
}

process_weekly_data(assoc_id)
{
  let data={user:assoc_id};
  // return this.http.post(`${this.baseUrl}/process_weekly_data`,data);

  let response= this.common_url('process_weekly_data',data);
  return response;
}

createVendor(form_data,user)
{
  let data={form:form_data,user_id:user};
  return this.http.post(`${this.baseUrl}/createVendor`,data);
}
getVendor()
{
  return this.http.get(`${this.baseUrl}/getVendor`);
}
updateVendor(update_data,update_id,user)
{
  let data={form:update_data,upd_id:update_id,user_id:user};
  return this.http.post(`${this.baseUrl}/updateVendor`,data);
}

getPractices(id)
{
  let data={user:id};
  return this.http.post(`${this.baseUrl}/getPractices`,data);
}

selectPractice(practice,user)
{
let data={prac_id:practice,user_id:user};
return this.http.post(`${this.baseUrl}/selectPractice`,data);

}

get_logs()
{
return this.http.get(`${this.baseUrl}/getLogs`);
}

viewLog(name)
{
  let data = {file_name:name};
return this.http.post(`${this.baseUrl}/viewLog`,data);
}

getPracticesList()
{
return this.http.get(`${this.baseUrl}/getPracticesList`);
}

get_audit_graph(id)
{
  let data={user:id};
  let response= this.common_url('get_audit_graph',data);
  return response;
}

userEdit(id){
  let data={user:id};
  let response= this.common_url('get_user_edit',data);
  return response;
}

createRoles(datas, id){
  let data={form_data:datas,user_id:id};
  let response= this.common_url('create_roles',data);
  return response;
}

updateRoles(datas, id, role_id){
  let data = {form_data:datas,user_id:id, role_id:role_id};
  let response = this.common_url('update_roles', data);
  return response;
}

followup_process_notes_delete(claim_no, id){
  let data = {claim_no:claim_no, user_id:id};
  let response = this.common_url('followup_process_notes_delete', data);
  return response;
}

reasigned_followup_process_notes_delete(claim_no, id){
  let data = {claim_no:claim_no, user_id:id};
  let response = this.common_url('reasigned_followup_process_notes_delete', data);
  return response;
}

closed_followup_process_notes_delete(claim_no, id){
  let data = {claim_no:claim_no, user_id:id};
  let response = this.common_url('closed_followup_process_notes_delete', data);
  return response;
}


audit_process_notes_delete(claim_no, id){
  let data = {claim_no:claim_no, user_id:id};
  let response = this.common_url('audit_process_notes_delete', data);
  return response;
}

closed_audit_process_notes_delete(claim_no, id){
  let data = {claim_no:claim_no, user_id:id};
  let response = this.common_url('closed_audit_process_notes_delete', data);
  return response;
}

template_edit(claim_id, id){
  let data = {claim_id:claim_id, user_id:id};
  let response = this.common_url('template_edit', data);
  return response;
}

insurance_name_list(){
  let data = {all:'data'};
  let response = this.common_url('insurance_name_list', data);
  return response;
}


claims_order_list(type, user_id, sortByAsc){
  let data = {type:type, user_id:user_id, sortByAsc:sortByAsc};
  let response = this.common_url('claims_order_list', data);
  return response;
}

getclaim_details_order_list(user_id, page, page_count, type, sort_data, sort_type){
  let data = {claim_type:type, user_id:user_id, page_no:page,count: page_count, sort_data:sort_data, sort_type:sort_type};
  let response = this.common_url('getclaim_details_order_list', data);
  return response;
}

audit_assigned_order_list(user_id, page, page_count, type, sort_data, sort_type){
  let data = {claim_type:type, user_id:user_id, page_no:page,count: page_count, sort_data:sort_data, sort_type:sort_type};
  let response = this.common_url('audit_assigned_order_list', data);
  return response;
}

claims_tooltip(claim){
  let data = {claim_no:claim};
  let response = this.common_url('claims_tooltip', data);
  return response;
}

get_insurance(user_id){
  let data = {user_id:user_id};
  let response = this.common_url('get_insurance', data);
  return response;
}

get_claimno(claim_no, user_id, claim_type, type){
  let data = {claim_no:claim_no, user_id:user_id, claim_type:claim_type, type:type};
  console.log(data);
  let response = this.common_url('get_claimno', data);
  return response;
}

get_audit_claimno(claim_no, user_id, claim_type, type){
  let data = {claim_no:claim_no, user_id:user_id, claim_type:claim_type, type:type};
  console.log(data);
  let response = this.common_url('get_audit_claimno', data);
  return response;
}

get_rcm_claimno(claim_no, user_id, claim_type){
  let data = {claim_no:claim_no, user_id:user_id, claim_type:claim_type};
  console.log(data);
  let response = this.common_url('get_rcm_claimno', data);
  return response;
}

get_client_claimno(claim_no, user_id, claim_type){
  let data = {claim_no:claim_no, user_id:user_id, claim_type:claim_type};
  console.log(data);
  let response = this.common_url('get_client_claimno', data);
  return response;
}

get_buyer(insurance_name){
  let data = {insurance_name:insurance_name};
  let response = this.common_url('get_buyer', data);
  return response;
}

get_report_claims(page,page_count,search_data,sort_type,type,startTime,endTime,trans_startDate,trans_endDate,dos_startDate,dos_endDate){
  let data = {page:page, page_count:page_count,data:search_data,sort_type:sort_type,type:type,startTime:startTime,endTime:endTime,trans_startDate:trans_startDate,trans_endDate:trans_endDate,dos_startDate:dos_startDate,dos_endDate:dos_endDate};
  console.log(data);
  let response = this.common_url('get_report_claims', data);
  return response;
}

/* Report Export  */

fetch_claims_report_export_data(filter,startTime,endTime,trans_startDate,trans_endDate,dos_startDate,dos_endDate,table_name)
{
  let data={filter:filter,startTime:startTime,endTime:endTime,trans_startDate:trans_startDate,trans_endDate:trans_endDate,dos_startDate:dos_startDate,dos_endDate:dos_endDate,table_name:table_name,  practice_dbid : localStorage.getItem('practice_id')};
  let response = this.common_url('report_export_claims', data);
  return response;
}

fetch_create_claims_export_data(user_id, table_name, search, searchClaims, workordersearch){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims, workordersearch:workordersearch};
  let response = this.common_url('fetch_create_claims_export_data', data);
  return response;
}

fetch_followup_claims_export_data(user_id, table_name, search, searchClaims){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims};
  let response = this.common_url('fetch_followup_claims_export_data', data);
  return response;
}

fetch_audit_claims_export_data(user_id, table_name, search, searchClaims){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims};
  let response = this.common_url('fetch_audit_claims_export_data', data);
  return response;
}

fetch_billing_claims_export_data(user_id, table_name, search, searchClaims){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims};
  let response = this.common_url('fetch_billing_claims_export_data', data);
  return response;
}

fetch_client_claims_export_data(user_id, table_name, search, searchClaims){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims};
  let response = this.common_url('fetch_client_claims_export_data', data);
  return response;
}

fetch_work_order_export_data(user_id, table_name, search, searchClaims){
  let data={user_id:user_id, table_name:table_name, search:search, searchClaims:searchClaims};
  let response = this.common_url('fetch_work_order_export_data', data);
  return response;
}


fetch_create_claims_export_data_pdf(user_id, table_name, search){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_create_claims_export_data_pdf', data);
  return response;
}

fetch_followup_claims_export_data_pdf(user_id, table_name){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_followup_claims_export_data_pdf', data);
  return response;
}

fetch_audit_claims_export_data_pdf(user_id, table_name){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_audit_claims_export_data_pdf', data);
  return response;
}

fetch_billing_claims_export_data_pdf(user_id, table_name){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_billing_claims_export_data_pdf', data);
  return response;
}

fetch_client_claims_export_data_pdf(user_id, table_name){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_client_claims_export_data_pdf', data);
  return response;
}

fetch_work_order_export_data_pdf(user_id, table_name){
  let data={user_id:user_id, table_name:table_name};
  let response = this.common_url('fetch_work_order_export_data_pdf', data);
  return response;
}

claims_closed_claim_search(user_id, searchValue){
  let data={user_id:user_id, searchValue:searchValue};
  let response = this.common_url('claims_closed_claim_search', data);
  return response;
}

updateingnore(upload_id){
  let data={upload_id:upload_id};
  let response = this.common_url('updateingnore', data);
  return response;
}

deletetemplate(delete_id){
  let data={delete_id:delete_id};
  let response = this.common_url('deletetemplate', data);
  return response;
}

getAlertNotification(user_id){
  let data={user_id:user_id};
  let response = this.common_url('getAlertNotification', data);
  //let response_touch = this.common_url('getAlertNotification', data);
  return response;
  //return response_touch;
}

get_setting_importsearch(searchValue){
  let data={searchValue:searchValue};
  let response = this.common_url('get_setting_importsearch', data);
  return response;
}

get_user_role(data){
  let datas={data:data};
  let response = this.common_url('getroles', datas);
  return response;
 // return this.http.post(`${this.baseUrl}/getroles`,{});
}

auto_assigned(user_id, claim_id, work, claim, type){
  let data={user_id:user_id,claim_id:claim_id,work:work,claim:claim,type:type};
  let response = this.common_url('auto_assigned', data);
  return response;
}

view_doc_file(id){
  let data={id:id};
  let response = this.common_url('view_doc_file', data);
  return response;
}

get_file_ready_count(){
  let data = {'practice_dbid':localStorage.getItem('practice_id')};
  let response = this.http.post(`${this.baseUrl}/get_file_ready_count`, data);
  return response;
}

get_error_param_codes(data){
  data={id:data,practice_dbid : localStorage.getItem('practice_id')};
  // return this.http.post(`${this.baseUrl}/get_audit_codes`, data);
  let response= this.common_url('get_error_param_codes',data);
  return response;
}

get_error_sub_param_codes(data,p_id){
  data={id:data,practice_dbid : localStorage.getItem('practice_id'),parent_id:p_id};
  let response= this.common_url('get_sub_error_param_codes',data);
  return response;
}

all_claim_list(data,page,page_count,type,sorting_name,sorting_method,createsearch,search)
{
   data={page_no:page,count:page_count,filter:data,sort_type:type,sorting_name:sorting_name,sorting_method:sorting_method,createsearch:createsearch,search:search};
   let response= this.common_url('all_claim_list',data);
   return response;
}

}

