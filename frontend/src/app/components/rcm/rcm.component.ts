import { Component, OnInit,ViewEncapsulation } from '@angular/core';
import { FormControl, FormGroup, Validators, FormBuilder } from "@angular/forms";
import { JarwisService } from '../../Services/jarwis.service';
import { SetUserService } from '../../Services/set-user.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { FollowupService } from '../../Services/followup.service';
import { NotesHandlerService } from '../../Services/notes-handler.service';
import { ToastrManager } from 'ng6-toastr-notifications';
import { ExportFunctionsService } from '../../Services/export-functions.service';
import { NotifyService } from '../../Services/notify.service';
import { Subscription } from 'rxjs';
import * as moment from 'moment';

@Component({
  selector: 'app-rcm',
  templateUrl: './rcm.component.html',
  styleUrls: ['./rcm.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class RcmComponent implements OnInit {

  createWork = "";

  selecteds: any;
  selectedReAssigin: any;
  selectedClosed: any;
  alwaysShowCalendars: boolean;
  ranges: any = {
    'Today': [moment(), moment()],
    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    'This Month': [moment().startOf('month'), moment().endOf('month')],
    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
  }
  invalidDates: moment.Moment[] = [moment().add(2, 'days'), moment().add(3, 'days'), moment().add(5, 'days')];

  isInvalidDate = (m: moment.Moment) =>  {
    return this.invalidDates.some(d => d.isSame(m, 'day') )
  }

  constructor(private formBuilder: FormBuilder,
    private Jarwis: JarwisService,
    private setus: SetUserService,
    private modalService: NgbModal,
    private follow: FollowupService,
    private notes_hadler:NotesHandlerService,
    public toastr: ToastrManager,
    private export_handler:ExportFunctionsService,
    private notify_service:NotifyService,) {
      this.alwaysShowCalendars = true;
    }

  table_datas:any;
  total_claims:number;
  closeResult : string;
  pages:string;
  loading:boolean;

  subscription : Subscription;

  formdata = new FormData();
  formGroup: FormGroup;
  processNotes: FormGroup;
  clientNotes: FormGroup;
  claimNotes: FormGroup;
  qcNotes: FormGroup;
  workOrder: FormGroup;
  claimsFind: FormGroup;
  tab_load:boolean=false;
  sortByAsc: boolean = true;

  refer_claim_det=[];
  refer_claim_no=[];
  refer_claim_notes=[];
  refer_process_notes=[];
  refer_qc_notes=[];
  main_tab:boolean=true;
  active_tab=[];
  active_refer_claim=[];
  active_refer_process=[];
  active_refer_qc=[];
  active_claim:string[];

  public editnote_value = null;


  qc_notes_data :Array<any> =[];
  qc_notes_data_list=[];
  search;

  public getclaim_details(page,sort_data,sort_type,sorting_name,sorting_method,claim_searh,search)
  {
    this.tab_load=true;
    this.pages=page;
    let page_count=15;

    let searchs = this.search;
    console.log(searchs);
    console.log(sorting_name);

    if(sorting_name == 'null' && searchs != 'search'){
      console.log('first');
      this.Jarwis.get_rcm_claims(this.setus.getId(),page,page_count,sort_data,sort_type,sorting_name,sorting_method,null,search).subscribe(
        data  => this.form_table(data),
        error => this.handleError(error)
      );
    }else if(searchs == 'search'){
      console.log(this.sortByAsc);
      this.Jarwis.get_rcm_claims(this.setus.getId(),page,page_count,sort_data,sort_type,this.sorting_name,this.sortByAsc,this.claimsFind.value,this.search).subscribe(
        data  => this.form_table(data),
        error => this.handleError(error)
      );
    }else{
      console.log('last');
      this.Jarwis.get_rcm_claims(this.setus.getId(),page,page_count,sort_data,sort_type,this.sorting_name,this.sortByAsc,null,search).subscribe(
        data  => this.form_table(data),
        error => this.handleError(error)
      );
    }
  }

  sorting_name;
  public order_list(sort_type,sorting_name,sorting_method,claim_searh,search)
  {
    this.sorting_name = sort_type;

    if(this.sortByAsc==true){
      this.sortByAsc=false;
      this.getclaim_details(this.pages,this.sortByAsc,sort_type,sorting_name,sorting_method,null,search);
    }else{
      this.sortByAsc=true;
      this.getclaim_details(this.pages,this.sortByAsc,sort_type,sorting_name,sorting_method,null,search);
    }
  }

  public claims_filter(page,sort_data,sort_type,sorting_name,sorting_method,search){
    this.search = search;
    this.getclaim_details(this.pages,sort_data,sort_type,sorting_name,sorting_method,this.claimsFind.value,search);
  }

  export_data: Array<any> =[];

  current_total;
  skip;
  total_row;
  skip_row;
  current_row;
  total;
  selected_table_datas;

  public form_table(data)
  {
    // console.log(data)
  this.table_datas=data.data;
  console.log(this.table_datas);
  this.selected_table_datas=data.slected_claim_data;
  this.total_claims=data.count;
  this.tab_load=false;

  this.current_total= data.current_total;
  this.skip = data.skip + 1;

  this.skip_row = this.skip;
  this.current_row = this.skip + this.current_total - 1;
  this.total_row = data.count;
  //  let field_names:Array<any>=['Acct#','Claim#','Patient Name','DOS','Claim Age','Insurance','Billed','AR Due','Sub-Status Code'];
  }

  //Check All function
  public check_all: Array<any> =[];
  public selected_claims=[];
  public selected_claim_nos=[];
  public check_all_assign(page,event)
  {
  if( event.target.checked == true)
  {
    this.check_all[page]=true;
  }
  else{
    this.check_all[page]=false;
  }
  }

  //Manage Selected claims
  assigned_claim_nos:number=0;
  public selected(event,claim,index)
  {
    if(claim == 'all' && event.target.checked == true )
    {
      let selected_table_datas = this.selected_table_datas;
      let claim_nos=this.selected_claim_nos;
      let claim_data= this.selected_claims;
      selected_table_datas.forEach(function (value) {
        let keys = value;
        if(!claim_nos.includes(keys['claim_no']))
        {
          claim_nos.push(keys['claim_no']);
          claim_data.push(keys);
        }
        });
        this.selected_claim_nos=claim_nos;
        this.selected_claims=claim_data;
    }
    else if(claim == 'all' && event.target.checked == false)
    {
      for(let i=0;i<this.selected_table_datas.length;i++)
      {
        let claim=this.selected_table_datas[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }
    else if(event.target.checked == true)
    {
      this.selected_claims.push(this.selected_table_datas[index]);
      this.selected_claim_nos.push(claim);
      }
      else if(event.target.checked == false)
      {
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
        }
        }

          //Open Pop-up
  open(content) {
    this.modalService.open(content, { centered: true,windowClass:'custom-class' }).result.then((result) => {
      this.closeResult = `${result}`;
    }, (reason) => {
      this.closeResult = `${this.getDismissReason()}`;
    });
}

//Modal Dismiss on Clicking Outside the Modal
private getDismissReason() {
  this.clear_notes();
}


  //Get Client details
  users_details :Array<any> = [];
  public get_user_list()
  {
    this.Jarwis.get_rcm_team_list(this.setus.getId()).subscribe(
      data  => this.list_users(data),
      error => this.handleError(error)
      );
  }


  //Assign and List User
  public list_users(data)
  {
    this.users_details=data.data;
  }

  public assigned_claims_details : Array<any> =[];
  claim_assign_type:string=null;
  selected_associates=[];
  //Select Associates for Work Order
  public select_associates(event, id)
  {
    if(event.target.checked == true)
    {
      this.selected_associates.push(id);

    }
    else if(event.target.checked == false)
    {
    let index = this.selected_associates.indexOf(id);
      this.selected_associates.splice(index,1);

    //Reduce Assigned Numbers Unchecked Associates
    let x=this.assigned_claims_details.find(v => v.id == id);
  if(x!=undefined)
  {
    let ind=this.assigned_claims_details.indexOf(x);
    this.assigned_claims_details.splice(ind,1);
    if(x.value!=0)
    {
      this.assigned_claim_nos=this.assigned_claim_nos-Number(x.value);
    }
  }
    }
  }

  //Manual or Automatic Assign
  public assign_type(type)
  {
  this.claim_assign_type=type;
  }

  public associate_error:string;
  public associate_error_handler:string[];
  //Manual Assign Function
  public manual_assign(event,id)
  {
    let check = this.assigned_claims_details.some(function (value) {
      return value.id === id;
    });
    if(event.target.value!=0)
    {
      if(!check)
      {
        this.assigned_claims_details.push({ id:id, value :event.target.value});
        }
        else
        {
          this.assigned_claims_details.find(v => v.id == id).value = event.target.value;
          }
          }
          this.calculate_assigned();
          }

  public assigned_data: Array<any> =[];
  //Calculate Assigned and Unassigned Claims
  public calculate_assigned()
  {
    let total=0;
  for(let i=0;i < this.assigned_claims_details.length;i++)
  {
    total += Number(this.assigned_claims_details[i]['value']);
    this.assigned_data[this.assigned_claims_details[i]['id']]=this.assigned_claims_details[i]['value'];
  }
  this.assigned_claim_nos=total;
  }

  assigned_claim_status:boolean=false;
  public assigned_claim_details:Array<any> =[];
  // public assign_status:Array<any> =[];


  //Assign Claims to Create Work Order
  public assign_claims()
  {
    let selected_claims=this.selected_claim_nos;
    let assigned_details=[];
    let init_value=0;

    this.assigned_claims_details.forEach(function (value) {
      let keys = value;
      let id = keys['id'];
      let value_data = keys['value'];

      let claims_assigned=selected_claims.slice(init_value,Number(init_value)+Number(value_data));
      init_value=value_data;
      assigned_details.push({assigned_to:id,claim_nos:value_data,claims:claims_assigned});
      });

      this.assigned_claim_details=assigned_details;
      this.assigned_claim_status=true;


    // for(let x=0;x<this.assigned_claims_details.length;x++)
    // {
    //   let id = this.assigned_claims_details[x]['id'];
    //   let value = this.assigned_claims_details[x]['value'];
    //   let claims_assigned=selected_claims.splice(0,value);
    //   assigned_details.push({assigned_to:id,claim_nos:value,claims:claims_assigned});
    // }


      // this.Jarwis.create_rcm_workorder(this.setus.getId(),assigned_details).subscribe(
      //   data  => this.handle_workorder_creation(data),
      //   error => console.log(error)
      //   );
  }



  public create_workorder()
  {
      this.Jarwis.create_workorder(this.setus.getId(),this.workOrder.value,this.assigned_claim_details,'rcm_team').subscribe(
        data  => this.handle_workorder_creation(data),
        error => this.handleError(error)
        );
  }


  //Aftermath Work Order creation Handling
  public handle_workorder_creation(data)
  {
    this.toastr.successToastr('Created', 'Work Order')
    this.getclaim_details(1,null,null,null,null,null,null);
    this.claim_assign_type=null;
    this.workOrder.reset();
    this.selected_claim_nos=[];
        this.selected_claims=[];
        this.check_all=[];
        this.assigned_claim_details=[];
        this.assigned_data=[];
  }


  //Work Order Tab Functions*****
  table_fields : string[];

  claim_clicked : string[];
  claim_related : string[];
  process_notes : string[];
  claim_notes : string[];
  qc_notes : string[];
  client_notes:string[];
  user_role:string;
  refer_claim_notes_nos=[];
  refer_process_notes_nos=[];
  refer_qc_notes_nos=[];
  refer_client_notes_nos=[];
  refer_client_notes=[];
  active_refer_client=[];

  //Managing Values displayed in Modal
  public claim_no;
  public claimslection(claim)
  {
    this.claim_no = claim.claim_no;
    this.get_line_items(claim);
    //Clear Previous Claims
    this.clear_refer();
    this.claim_clicked=claim;
    let length=this.table_datas.length;
    this.claim_related=[];
    for(let i=0;i<this.table_datas.length;i++)
    {
      let related_length=this.claim_related.length;
      length= length-1;
      if(related_length<3)
      {
        if(this.table_datas[length]['acct_no'] == claim.acct_no && this.table_datas[length]['claim_no'] != claim.claim_no )
        {
        this.claim_related.push(this.table_datas[length]);
        }
      }
    }
    this.send_calim_det('footer_data');
    this.loading=true;
    this.getnotes(this.claim_clicked);
    this.processNotesDelete(this.claim_no);
  }

processNotesDelete(data){
  // this.Jarwis.process_notes_delete(data, this.setus.getId()).subscribe(
  //   data  => this.handleResponseProcess(data),
  //   error => this.handleError(error)
  // );
}

handleResponseProcess(data){
  this.getnotes(this.claim_clicked);
}
  refer_claim_editable='false';
  claim_status;
  claim_nos;
  type;
  //Refer Claim Clicked Action

  //Refer Claim
  public referclaim(claim)
  {

  claim = claim;

  this.claim_nos = claim.claim_no;

  this.claim_status = claim.claim_Status;
  this.Jarwis.get_rcm_claimno(this.claim_nos, this.setus.getId(), this.claim_status).subscribe(
    data  => this.handleClaimNo(data),
    error => this.handleError(error)
  );

  if(this.refer_claim_no.indexOf(claim['claim_no']) < 0 )
  {
    this.refer_claim_det.push(claim);
    this.refer_claim_no.push(claim['claim_no']);
    this.Jarwis.getnotes(claim).subscribe(
      data  => this.refer_notes(data,claim.claim_no),
      error => this.handleError(error)
    );
  }
  else
  {
    this.selected_tab(claim['claim_no']);
  }
  this.send_calim_det('footer_data');
  }

  assigned_datas;

  public handleClaimNo(data){
    this.assigned_datas = data.claim_count;
    console.log(this.assigned_datas);
    this.refer_claim(this.assigned_datas);
  }

  refer_claim(assigned_datas){
    if(assigned_datas == true ){
        this.refer_claim_editable = 'true';
    }else if(assigned_datas == false ){
      this.refer_claim_editable = 'false';
    }
  }

  //Display Reference Notes
  public refer_notes(data,claimno)
  {
    this.refer_claim_notes_nos.push(claimno);
    this.refer_claim_notes.push(data.data.claim);

    this.refer_process_notes_nos.push(claimno);
    this.refer_process_notes.push(data.data.process);

    this.refer_qc_notes_nos.push(claimno);
    this.refer_qc_notes.push(data.data.qc);

    this.refer_client_notes_nos.push(claimno);
    this.refer_client_notes.push(data.data.client);


    let index_claim= this.refer_claim_notes_nos.indexOf(claimno);
    let index_process= this.refer_process_notes_nos.indexOf(claimno);
    let index_qc= this.refer_qc_notes_nos.indexOf(claimno);
    let index_client= this.refer_client_notes_nos.indexOf(claimno);

    this.active_refer_claim=  this.refer_claim_notes[index_claim];
    this.active_refer_process=this.refer_process_notes[index_process];
    this.active_refer_qc=this.refer_qc_notes[index_qc];
    this.active_refer_client=this.refer_client_notes[index_client];
    this.main_tab=false;
    this.active_claim=claimno;
    this.send_calim_det('footer_data');
  }

  //Update Notes in Related Claims
  public update_refer_notes(data,type,claimno)
  {
    let index_up_qc= this.refer_qc_notes_nos.indexOf(claimno);
    let index_up_process = this.refer_process_notes_nos.indexOf(claimno);
    let index_up_claim=this.refer_claim_notes_nos.indexOf(claimno);
    if(type=='processnotes')
    {
      if(index_up_process==undefined)
      {
        this.refer_process_notes_nos.push(claimno);
        this.refer_process_notes.push(data.data);
        index_up_process = this.refer_process_notes_nos.indexOf(claimno);
      }
      else{
        this.refer_process_notes[index_up_process]=data.data;
      }
    // this.refer_process_notes[claimno]=data.data;
    }
    else if(type=='claimnotes')
    {
      if(index_up_claim==undefined)
      {
        this.refer_claim_notes_nos.push(claimno);
        this.refer_claim_notes.push(data.data);
        index_up_claim=this.refer_claim_notes_nos.indexOf(claimno);
      }
      else{
        this.refer_claim_notes[index_up_claim]=data.data;
      }
    // this.refer_claim_notes[claimno]=data.data;
    }
    else if(type=='qcnotes')
    {

    if(index_up_qc==undefined)
    {
      this.refer_qc_notes_nos.push(claimno);
      this.refer_qc_notes.push(data.data);
      index_up_qc= this.refer_qc_notes_nos.indexOf(claimno);
    }
    else{
  this.refer_qc_notes[index_up_qc]=data.data;
    }
      // this.refer_qc_notes[claimno]=data.data;
    }
    this.active_refer_claim= this.refer_claim_notes[index_up_claim];
    this.active_refer_process=this.refer_process_notes[index_up_process];
    this.active_refer_qc=this.refer_qc_notes[index_up_qc];
  }

  //Focus on Selected Tab
  public selected_tab(claimno)
  {
  if(claimno == 'maintab')
  {
    this.main_tab=true;
    this.active_claim=[];
  }
  else{
    let index_qc= this.refer_qc_notes_nos.indexOf(claimno);
    let index_process = this.refer_process_notes_nos.indexOf(claimno);
    let index_claim=this.refer_claim_notes_nos.indexOf(claimno);
    let index_client=this.refer_claim_notes_nos.indexOf(claimno);

    this.active_refer_claim= this.refer_claim_notes[index_claim];
    this.active_refer_process=this.refer_process_notes[index_process];
    this.active_refer_qc=this.refer_qc_notes[index_qc];
    this.active_refer_client=this.refer_client_notes[index_client];
    this.main_tab=false;
    this.active_claim=claimno;
  }
  this.send_calim_det('footer_data');
  }

  //Close Refer Tab
  public close_tab(claim_no)
  {
    let index=this.refer_claim_det.indexOf(claim_no);
    let list_index=this.refer_claim_no.indexOf(claim_no.claim_no)
    this.refer_claim_det.splice(index, 1);
    this.refer_claim_no.splice(list_index, 1);
    this.main_tab=true;
    this.active_claim=[];
    this.send_calim_det('footer_data');
    this.get_line_items(this.claim_clicked);
  }

  //Clear Tabs Details
  public clear_refer()
  {
    this.main_tab=true;
    this.active_claim=[];
    this.refer_claim_det=[];
    this.refer_claim_no=[];
  }

  //Save Notes
  public savenotes(type)
  {
    let claim_id=[];
    if(this.active_claim.length != 0)
    {
      let index= this.refer_claim_no.indexOf(this.active_claim);
      claim_id=this.refer_claim_det[index];
      }
      else{
        claim_id=this.claim_clicked;
        }
        if(type=='processnotes')
        {
          // this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],claim_id,'processcreate').subscribe(
          //   data  => this.display_notes(data,type),
          //   error => this.handleError(error)
          //   );
            }
            else if(type=='claimnotes')
            {
              this.Jarwis.claim_note(this.setus.getId(),this.claimNotes.value['claim_notes'],claim_id,'claim_create').subscribe(
                data  => this.display_notes(data,type),
                error => this.handleError(error)
                );
                }
                else if(type=='qcnotes')
                {
                  // console.log("SEE_CLAIM",claim_id,this.claim_clicked);
                  // this.Jarwis.qc_note(this.setus.getId(),this.qcNotes.value['qc_notes'],claim_id,'create_qcnotes').subscribe(
                  //   data  => this.display_notes(data,type),
                  //   error => this.handleError(error)
                  //   );
                  this.qc_notes_data.push({notes:this.qcNotes.value['qc_notes'],id:claim_id['claim_no']});
                  this.qc_notes_data_list.push(claim_id['claim_no']);
                  // this.notes_hadler.set_notes(this.setus.getId(),this.qcNotes.value['qc_notes'],claim_id,'create_qcnotes');
                  this.send_calim_det('footer_data');
                    }
                    }

  //Update Displayed Notes
  public display_notes(data,type)
  {
    if(this.active_claim.length != 0)
    {
      this.update_refer_notes(data,type,this.active_claim)
      }
      else
      {
        if(type=='processnotes')
        {
          this.process_notes=data.data;
          }
          else if(type=='claimnotes')
          {
            this.claim_notes=data.data;
            }
            else if(type=='qcnotes')
            {
              this.qc_notes=data.data;
              }
              else if(type=='All')
              {
                this.process_notes=data.data.process;
                this.claim_notes=data.data.claim;
                this.qc_notes=data.data.qc;
                this.client_notes=data.data.client;
                }
                }
                this.loading=false;
                this.processNotes.reset();
                this.claimNotes.reset();
                this.qcNotes.reset();
                }

  //Get Notes
  public getnotes(claim)
  {
    this.process_notes=[];
    this.claim_notes=[];
    this.qc_notes=[];
    this.client_notes=[];
    let type='All';
    this.Jarwis.getnotes(claim).subscribe(
      data  => this.display_notes(data,type),
      error => this.handleError(error)
    );
  }

  //Edit Notes
  edit_noteid:number;
  edit_note_value: string[];
  initial_edit:boolean=false;
  public editnotes(type,value,id)
  {
    if(type=='qc_notes_init')
    {
      let qc_data=this.qc_notes_data.find(x => x.id == id['claim_no']);
      this.editnote_value=qc_data.notes;
      this.edit_noteid=id;
      this.initial_edit=true;
    }
    else{
      this.editnote_value=value;
      this.edit_noteid=id;
      this.initial_edit=false;
    }

  }

  //Update Notes
  public updatenotes(type)
  {
    if(this.initial_edit==true)
    {
      // this.notes_hadler.set_notes(this.setus.getId(),this.qcNotes.value['qc_notes'],this.edit_noteid,'create_qcnotes');

      // this.qc_notes_data[this.edit_noteid['claim_no']]=this.qcNotes.value['qc_notes'];

      this.qc_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.qcNotes.value['qc_notes'];


      this.initial_edit=false;
      this.send_calim_det('footer_data');
    }
    else{
      if(type=='processnotes')
      {
      // this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'processupdate').subscribe(
      //   data  => this.display_notes(data,type),
      //   error => this.handleError(error)
      // );
      }
      else if(type=='claimnotes')
      {
        this.Jarwis.claim_note(this.setus.getId(),this.claimNotes.value['claim_notes'],this.edit_noteid,'claimupdate').subscribe(
          data  => this.display_notes(data,type),
          error => this.handleError(error)
        );
      }
      else if(type=='qcnotes')
      {
        this.Jarwis.qc_note(this.setus.getId(),this.qcNotes.value['qc_notes'],this.edit_noteid,'qcupdate').subscribe(
          data  => this.display_notes(data,type),
          error => this.handleError(error)
        );
      }

    }
    this.editnote_value=null;
  }

  //Clear ProcessNote
  public clear_notes()
  {
    this.editnote_value=null;
    this.processNotes.reset();
  }

//Error Handling
handleError(error)
{
  this.toastr.errorToastr(error, 'Error')
}

  //Send Claim Value to Followup-Template Component on Opening Template
  public send_calim_det(type)
  {
    if(this.main_tab==true)
    {
      if(type == 'followup')
      {
        this.follow.setvalue(this.claim_clicked['claim_no']);
      }
      else{
        this.notes_hadler.selected_tab(this.claim_clicked['claim_no']);
        this.notes_hadler.set_claim_details(this.claim_clicked);
      }
    }
    else
    {
      if(type == 'followup')
      {
        this.follow.setvalue(this.active_claim);
      }
      else{
        this.notes_hadler.selected_tab(this.active_claim);
        let claim_detials=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
        this.notes_hadler.set_claim_details(claim_detials);
      }
    }
  }



//Work Order table Formation
wo_page_number:number=1;
work_order_data;
public get_workorder(filter,from,to,type,page)
{
  if(filter == null && from == null && to == null)
  {
    this.tab_load=true;
    this.Jarwis.get_workorder(0,0,0,4,page,null,null,null,null,null,null,null).subscribe(
      data  => this.form_wo_table(data,page),
      error => this.handleError(error)
      );
  }
  else
  {
this.handleError("NYD");
  }

}

wo_total:Number;
public form_wo_table(data,page_no)
{
  this.tab_load=false;
  this.work_order_data=data.data;
  this.wo_total=data.count;
  this.wo_page_number=page_no;
}

wo_details:string[];
wo_name:string;
wo_created:string;

//Work Order details fetch
public get_wo_details(id,name,assigned)
{
  this.loading=true;
  this.wo_details=[]
  this.wo_name=name;
  this.wo_created=assigned;
  this.Jarwis.get_workorder_details(id).subscribe(
    data  => this.wo_details_table(data),
    error => this.handleError(error)
    );
}


public export_files(type)
{
  let filter='all claims';
  let s_code='adjustment';

  this.Jarwis.fetch_rcm_export_data(filter,s_code,this.setus.getId()).subscribe(
    data  => this.export_handler.sort_export_data(data,type,'claim'),
    error => this.error_handler(error)
    );
}


public export_wo_files(type)
{
  let filter='all claims';
  let s_code='adjustment';
  let wo_type=4;
  this.Jarwis.fetch_wo_export_data(filter,s_code,wo_type,this.setus.getId()).subscribe(
    data  => this.export_handler.ready_wo_export(data,type),
    error => this.error_handler(error)
    );

}

public wo_export_function(type)
{
  this.export_handler.sort_export_data(this.wo_details,type,'wo_detail');
}



public wo_details_table(data)
{
  this.loading=false;
this.wo_details=data.data;

}

line_data=[];

public get_line_items(claim)
{

let stat=0;

for(let i=0;i<this.line_item_data.length;i++)
{
  let array=this.line_item_data[i];
  let x =  array.find(x => x.claim_id == claim['claim_no']);
  if(x!=undefined)
  {
    this.line_data=array;
    stat=1;
  }

}

if(stat ==0)
{
  this.Jarwis.get_line_items(claim).subscribe(
    data  => this.assign_line_data(data) ,
    error => this.handleError(error)
  );
}

}

line_item_data=[];
assign_line_data(data)
{
  this.line_item_data.push(data.data);
  this.line_data=data.data;
}


error_handler(error)
{
  //console.log(error);
  if(error.error.exception == 'Illuminate\Database\QueryException')
  {
    this.toastr.warningToastr('File Cannot Be Deleted','Foreign Key Constraint');
  }
else{
  this.toastr.errorToastr(error.error.exception, 'Error!');
}


}
// get_touch_limit()
// {
//   this.Jarwis.get_practice_stats().subscribe(
//     data =>this.set_prac_settings(data)
//     );
// }

touch_count:number;
// set_prac_settings(data)
// {
//   let prac_data=data.data;
//   this.touch_count=prac_data.touch_limit;

//  //  console.log(this.touch_count);

// }


dataSource={
  "chart": {
      // "caption": "Sales of Liquor",
      // "subCaption": "Previous week vs current week",
      "xAxisName": "Day",
      // "yAxisName": "Sales (In USD)",
      "numberPrefix": "$",
      "plotFillAlpha": "60",
      "theme": "fusion"
  },
  "categories": [
      {
          "category": [
              {
                  "label": "Mon"
              },
              {
                  "label": "Tue"
              },
              {
                  "label": "Wed"
              },
              {
                  "label": "Thu"
              },
              {
                  "label": "Fri"
              },
              {
                  "label": "Sat"
              },
              {
                  "label": "Sun"
              }
          ]
      }
  ],
  "dataset": [
      {
          "seriesname": "Previous Week",
          "data": [
              {
                  "value": "13000"
              },
              {
                  "value": "14500"
              },
              {
                  "value": "13500"
              },
              {
                  "value": "15000"
              },
              {
                  "value": "15500"
              },
              {
                  "value": "17650"
              },
              {
                  "value": "19500"
              }
          ]
      },
      {
          "seriesname": "Current Week",
          "data": [
              {
                  "value": "8400"
              },
              {
                  "value": "9800"
              },
              {
                  "value": "11800"
              },
              {
                  "value": "14400"
              },
              {
                  "value": "18800"
              },
              {
                  "value": "24800"
              },
              {
                  "value": "30800"
              }
          ]
      }
  ]
}
dataSource2={
  "chart": {
      "caption": "Average Page Load Time (hsm.com)",
      "subCaption": "Last Week",
      "showBorder": "0",
      "xAxisName": "Day",
      "yAxisName": "Time (In Sec)",
      "numberSuffix": "s",
      "theme": "fusion"
  },
  "categories": [
      {
          "category": [
              {
                  "label": "Mon"
              },
              {
                  "label": "Tue"
              },
              {
                  "label": "Wed"
              },
              {
                  "label": "Thu"
              },
              {
                  "label": "Fri"
              },
              {
                  "label": "Sat"
              },
              {
                  "label": "Sun"
              }
          ]
      }
  ],
  "dataset": [
      {
          "seriesname": "Loading Time",
          "allowDrag": "0",
          "data": [
              {
                  "value": "6"
              },
              {
                  "value": "5.8"
              },
              {
                  "value": "5"
              },
              {
                  "value": "4.3"
              },
              {
                  "value": "4.1"
              },
              {
                  "value": "3.8"
              },
              {
                  "value": "3.2"
              }
          ]
      }
  ]
};

myOptions = {
  'placement': 'right',
  'hide-delay': 3000,
  'theme':'light'
}



public auto_assign_claims()
{
  //console.log(this.selected_claim_nos,this.users_details,this.selected_associates);
  let assignable_aud=[];
  if(this.selected_associates.length == 0){
    this.users_details.forEach(element => {
      assignable_aud.push(element.id);
  });
  }
  else{
    assignable_aud=this.selected_associates;
  }
//  console.log(assignable_aud);

  let selected_claims=this.selected_claim_nos;
  let init_value=0;
  let users = this.users_details;
  let assigned_details=[];


  let assign_value=0;
  assignable_aud.forEach(function (value) {
    let keys = value;
    let auditor_det = users.find(x => x['id'] == keys);
//Check Assignable Numbers
    let assign_limit= Number(auditor_det['assign_limit'])-Number(auditor_det['assigned_nos']);

    // Check assignable claims nos
    if((selected_claims.length - Number(assign_value))  < assign_limit)
    {
      assign_limit=selected_claims.length;
    }


    if(assign_limit >0 && (selected_claims.length - Number(assign_value)) !=0)
    {
      assign_value=Number(init_value)+Number(assign_limit);
      let claims_assigned=selected_claims.slice(init_value,Number(init_value)+Number(assign_limit));
      init_value=Number(init_value)+Number(assign_limit);
      assigned_details.push({assigned_to:auditor_det['id'],claim_nos:assign_limit,claims:claims_assigned});
    }
      });

      this.assigned_claim_details=assigned_details;
      //console.log("o/p",this.assigned_claim_details);
      this.assigned_claim_status=true;
    }



     //Red Alerrt Box
private _opened: boolean = false;
private isOpen: boolean = false;
private _positionNum: number = 0;
private _modeNum: number = 1;


private _MODES: Array<string> = ['push'];
private _POSITIONS: Array<string> = ['right'];

private redalert() {
 this._opened = !this._opened;
}



private mynotes(){
    this.isOpen=!this.isOpen;
}


private _togglePosition(): void {
 this._positionNum++;

 if (this._positionNum === this._POSITIONS.length) {
   this._positionNum = 0;
 }
}


private _toggleMode(): void {
 this._modeNum++;

 if (this._modeNum === this._MODES.length) {
   this._modeNum = 0;
 }
}

  ngOnInit() {
    this.getclaim_details(1,null,null,'null','null',null,null);
    // this.get_user_list();


    this.claimsFind = this.formBuilder.group({
      dos: [],
      claim_no: [],
      acc_no: [],
      patient_name: [],
      total_charge: [],
      total_ar: [],
      claim_note: [],
      insurance: [],
      prim_ins_name: [],
      prim_pol_id: [],
      sec_ins_name: [],
      sec_pol_id: [],
      ter_ins_name: [],
      ter_pol_id: [],
    });

    this.processNotes = new FormGroup({
      processnotes: new FormControl('', [
        Validators.required
      ])
    });
    this.claimNotes = new FormGroup({
      claim_notes: new FormControl('', [
        Validators.required
        ])
        });
    this.qcNotes = new FormGroup({
      qc_notes: new FormControl('', [
        Validators.required
        ])
        });

        this.workOrder = new FormGroup({
          workorder_name: new FormControl('', [
        Validators.required
        ]),
        due_date: new FormControl('', [
          Validators.required
        ]),
        priority: new FormControl('', [
          Validators.required
        ]),
        wo_notes: new FormControl('', [
          Validators.required
        ])
        });

        this.subscription=this.notify_service.fetch_touch_limit().subscribe(message => {
          this.touch_count = message });
        }

        ngAfterViewInit()
        {
          if(this.touch_count == undefined)
          {
            this.touch_count=this.notify_service.manual_touch_limit();
          }
        }

        ngOnDestroy(){
          // prevent memory leak when component destroyed
          this.subscription.unsubscribe();
        }

  public clear_fields()
  {
    this.assigned_claims_details=[];
    this.workOrder.reset();
  }

  public sort_details(type){
    console.log(this.table_datas)
    if(type=='id'){
      if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.table_datas.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
} else {
  this.sortByAsc = true;
  this.table_datas.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
}
}
if(type=='claims'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
}
}
if(type=='patient'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
}
}
if(type=='dos'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.dos.localeCompare(b.dos));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.dos.localeCompare(a.dos));
}
}
if(type=='insurance'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
}
}
if(type=='due'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
}
}
if(type=='status'){
  if(this.sortByAsc == true) {
this.sortByAsc = false;
this.table_datas.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
} else {
this.sortByAsc = true;
this.table_datas.sort((a,b) => b.claim_Status.localeCompare(a.claim_Status));
}
}
else if(type=='bill'){
  if(this.sortByAsc==true){
    this.sortByAsc=false;
    this.table_datas.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
  }
  else{
    this.sortByAsc=true;
    this.table_datas.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
  }
}
  }
  tooltipOptions= {
    'placement': 'right',
    'show-delay': '200',
    'tooltip-class': 'new-tooltip-class',
    'background-color': '#9ad9e4',
    
    };

public claim_number;

public tooltip(claim){
  this.claim_number = claim.claim_no;

  this.Jarwis.claims_tooltip(this.claim_number).subscribe(
    data  => this.handleClaimsTooltip(data),
    error => this.handleError(error)
  );
}

claim_data;
age;
showAge;
calculateAge;

public handleClaimsTooltip(data){
  this.claim_data = data.claim_data;
  this.age = data.claim_data.dob;

  const convertAge = new Date(this.age);
  const timeDiff = Math.abs(Date.now() - convertAge.getTime());
  this.showAge = Math.floor((timeDiff / (1000 * 3600 * 24))/365);
  this.calculateAge = this.showAge;
  console.log(this.calculateAge);
}

public export_excel_files(type, table_name)
{
   console.log(table_name);
   this.Jarwis.fetch_billing_claims_export_data(this.setus.getId(), table_name, this.search, this.claimsFind.value).subscribe(
      data  => this.export_handler.create_claim_export_excel(data),
      error => this.error_handler(error)
      );
}

public export_pdf_files(type, table_name)
  {
    let filter='all claims';
    let s_code='adjustment';

    this.Jarwis.fetch_billing_claims_export_data_pdf(this.setus.getId(), table_name).subscribe(
      data  => this.export_handler.sort_export_data(data,type,'claim'),
      error => this.error_handler(error)
    );
  }

}
