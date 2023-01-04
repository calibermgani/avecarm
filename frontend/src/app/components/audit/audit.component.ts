import { Component,ViewChildren,ElementRef,QueryList, OnInit,ChangeDetectionStrategy,ViewEncapsulation } from '@angular/core';
import { SetUserService } from '../../Services/set-user.service';
import { JarwisService } from '../../Services/jarwis.service';
import { LoadingBarService } from '@ngx-loading-bar/core';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, Validators, FormBuilder } from "@angular/forms";
import { FollowupService } from '../../Services/followup.service';
import { NotesHandlerService } from '../../Services/notes-handler.service';
import { Subscription } from 'rxjs';
import { ToastrManager } from 'ng6-toastr-notifications';
import { ExportFunctionsService } from '../../Services/export-functions.service';
import { NotifyService } from '../../Services/notify.service';
import { debounceTime } from 'rxjs/operators';
import { pipe } from 'rxjs/util/pipe';
import * as moment from 'moment';

@Component({
  selector: 'app-audit',
  templateUrl: './audit.component.html',
  styleUrls: ['./audit.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class AuditComponent implements OnInit {

  createWork = "";
  assigned = "";
  closedWork = "";
  associateCount : any ='';

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  selecteds: any;
  selectedReAssigin: any;
  selectedClosed: any;
  selectedDueDate: any;
  selectedCreatedAt: any;
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


  response_data: Subscription;
  observalble: Subscription;
  update_monitor: Subscription;
  isopend=true;
  subscription : Subscription;
  constructor(private Jarwis: JarwisService,
    private formBuilder: FormBuilder,
    private setus: SetUserService,
    private loadingBar: LoadingBarService,
    private modalService: NgbModal,
    private follow: FollowupService,
    private notes_hadler:NotesHandlerService,
    public toastr: ToastrManager,
    private export_handler:ExportFunctionsService,
    private notify_service:NotifyService,
    ) {
      this.observalble=this.setus.update_edit_perm().subscribe(message => {this.check_edit_permission(message)} );
      this.response_data=this.notes_hadler.get_response_data('audit').subscribe(message => { this.collect_response(message) });
      this.update_monitor=this.notes_hadler.refresh_update().subscribe(message => {
        this.getclaim_details(this.pages,'refresh','null','null','null','null',null,null,null,null,null);
        console.log(this.update_monitor);

    });
    this.alwaysShowCalendars = true;
    }

    public editnote_value = null;
    formdata = new FormData();
    processNotes: FormGroup;
    search_data: FormControl = new FormControl();
    wo_search_data: FormControl = new FormControl();
    filter_option: FormControl = new FormControl();

    claimNotes: FormGroup;
    qcNotes: FormGroup;
    workOrder: FormGroup;
    auditClaimsFind: FormGroup;
    assignedClaimsFind: FormGroup;
    closedClaimsFind: FormGroup;
    workOrderFind: FormGroup;

    qc_notes_data :Array<any> =[];
    qc_notes_data_list=[];
    tab_load:boolean=false;
    sortByAsc: boolean = true;
    claim_active;
//Error Handling
    handleError(error)
    {
      console.log(error);
    }

  //Work Order Tab Functions*****
  table_fields : string[];
  table_datas=[] ;
  claim_clicked : string[];
  claim_related : string[];
  process_notes : string[];
  claim_notes : string[];
  qc_notes : string[];
  client_notes:string[];
  closeResult : string;
  total_claims:number;
  pages:number;
  loading:boolean;

  completed_claims=[];
  total_completed_claims:number;
  comp_pages:number;


  allocated_claims=[];
  total_allocated:number;
  alloc_pages:number;
  current_claim_type:string;
  sorting_name;


  order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search)
  {
    console.log(sorting_name);
    this.sorting_name = sort_type;

    if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.getclaim_details(this.pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    } else {
        this.sortByAsc = true;
        this.getclaim_details(this.pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    }

  }

  assigned_sorting_name;
  assigned_order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search)
  {
    this.assigned_sorting_name = sort_type;

    if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.getclaim_details(this.alloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    } else {
        this.sortByAsc = true;
        this.getclaim_details(this.alloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    }

  }

  closed_sorting_name;
  closed_order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search){

    this.closed_sorting_name = sort_type;
    if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.getclaim_details(this.comp_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    } else {
        this.sortByAsc = true;
        this.getclaim_details(this.comp_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,null,search);
    }
  }

  public audit_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search){
    this.search = search;
    console.log(this.search);
    this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search);
  }

  public assigned_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search){
    this.search = search;
    console.log(this.search);
    this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search);
  }

  public closed_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search){
    this.search = search;
    console.log(type);
    this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search);
  }


  //Get Claim Details to Display
  type;
  search;
  public getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,audit_claim_search,search)
  {
    console.log(assign_claim_searh);
    let page_count=15;
    let form_type=null;
    let searchs = this.search;

    console.log(this.type);

    this.type = type;

    if(type == 'wo')
    {
      console.log(searchs);
      this.pages=page;
      this.current_claim_type='wo';
      if(sorting_name == 'null' && searchs != 'search'){
        this.Jarwis.get_audit_table_page(sort_data,page,page_count,sort_type,sorting_name,sorting_method,null,null,null,null,search).subscribe(
          data  => this.assign_page_data(data),
          error => this.handleError(error)
        );
      }else if(searchs == 'search'){
        this.Jarwis.get_audit_table_page(sort_data,page,page_count,sort_type,this.sorting_name,this.sortByAsc,null,null,null,this.auditClaimsFind.value,this.search).subscribe(
          data  => this.assign_page_data(data),
          error => this.handleError(error)
        );
      }else{
        this.Jarwis.get_audit_table_page(sort_data,page,page_count,sort_type,this.sorting_name,this.sortByAsc,null,null,null,null,this.search).subscribe(
          data  => this.assign_page_data(data),
          error => this.handleError(error)
        );
        }
    }
    else if(type=='completed'){
      this.comp_pages=page;
      this.current_claim_type='completed';
      if(sorting_name == 'null' && searchs != 'search'){
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,null,search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }else if(searchs == 'search'){
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,this.closedClaimsFind.value,null,this.search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }else{
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,null,null,this.search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }
    }
    else if(type=='allocated')
    {
      this.alloc_pages=page;
      this.current_claim_type='allocated';

      if(sorting_name == 'null' && searchs != 'search'){
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,null,search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }else if(searchs == 'search'){
        console.log('target');
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.assigned_sorting_name,this.sortByAsc,this.assignedClaimsFind.value,null,null,null,this.search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }else{
        this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.assigned_sorting_name,this.sortByAsc,null,null,null,null,this.search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
      }
    }
    else if(type == 'refresh')
    {

      if(type == 'refresh')
      {
        type=this.current_claim_type;
        form_type='refresh';

        if(type == 'wo')
        {
          page=this.pages;

        }
        else if(type=='completed'){
          page=this.comp_pages;
        }
        else if(type=='allocated')
        {
          page=this.alloc_pages;

          console.log(page);
        }

      }
    }

    this.tab_load=true;
    // this.Jarwis.get_audit_claim_details(this.setus.getId(),page,page_count,'allocated').subscribe(
    //   data  => this.form_table(data,type,form_type),
    //   error => this.handleError(error)
    // );
  }

  selected_filter_type=[];
  //set filter type
  public claim_filter_type($event)
  {
  this.selected_filter_type=$event.target.value;

  this.claim_sort_filter();
  }


  //sort with filter
  public claim_sort_filter()
  {
  this.getclaim_details(1,'all',null,null,null,null,null,null,null,null,null)
  }

current_total;
skip;
total_row;
skip_row;
current_row;
total;

total_claims_closed;
current_totals;
skips;
total_rows;
skip_rows;
current_rows;
totals;
audit_claim_data;
//Assign Table data and `total values
public assign_page_data(data)
{
  this.table_datas=data.data;
  this.audit_claim_data = data.audit_claim_data;
  this.total=data.total;

  this.totals=data.total;
  this.current_totals= data.current_total;
  this.skips = data.skip + 1;

  this.skip_rows = this.skips;
  this.current_rows = this.skips + this.current_totals - 1;
  this.total_rows = this.total;

}
searchData:string;
//Search filter function
public sort_data(data)
{
  this.getclaim_details(1,'wo',data,'searchFilter','null','null',null,null,null,null,null);
  this.searchData=data;
  //To reset the checklist
  this.check_all[1]=false;
  this.selected_claim_nos=[];

  //console.log(this.searchData);
}

public sort_wo_data(data)
{
  // console.log(data);
  if(data == '')
  {
    this.get_workorder(null,null,null,2,1,null,null,null,null,null,null,null);
  }
  else{
    this.get_workorder('search',data,0,2,1,null,null,null,null,null,null,null);
  }

}
public sort_table(data)
{
this.getclaim_details(1,'wo',data,'filters','null','null',null,null,null,null,null);
}
//Work Order table Formation

  wo_page_number:number=1;
work_order_data;
wo_sorting_name;

work_order_list(sort_type,sorting_name,sorting_method,search){

  this.wo_sorting_name = sort_type;

  if(this.sortByAsc == true) {
    this.sortByAsc = false;
    this.get_workorder(null,null,null,1,this.w_pages,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,search);
  } else {
    this.sortByAsc = true;
    this.get_workorder(null,null,null,1,this.w_pages,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,search);
  }
}

workorder_search(filter, from, to, type, page,sort_type, sort_data,sorting_name,sorting_method,closedsearch,workordersearch,search){
  this.search = search;
  console.log(this.search);
  this.get_workorder(filter,from,to,type,page,sort_type, this.sortByAsc,sorting_name,sorting_method,null,this.workOrderFind.value,search);
}

w_pages;

public get_workorder(filter,from,to,type,page,sort_data,sort_type,sorting_name,sorting_method,closedsearch,workordersearch,search)
{
  let page_count=15;
  this.tab_load=true;


  if(filter == null && from == null && to == null)
  {

    let searchs = this.search;

    this.w_pages=page;

    if(sorting_name == 'null' && searchs != 'search'){
      this.Jarwis.get_workorder(0,0,0,2,page,sort_type,sort_data,sorting_name,sorting_method,closedsearch,workordersearch,search).subscribe(
        data  => this.form_wo_table(data,page),
        error => this.handleError(error)
      );
    }else if(searchs == 'search'){
      this.Jarwis.get_workorder(0,0,0,2,page,sort_type,sort_data,this.wo_sorting_name,this.sortByAsc,null,this.workOrderFind.value,this.search).subscribe(
        data  => this.form_wo_table(data,page),
        error => this.error_handler(error)
      );
    }else{
      this.Jarwis.get_workorder(0,0,0,2,page,sort_type,sort_data,this.wo_sorting_name,this.sortByAsc,null,workordersearch,this.search).subscribe(
        data  => this.form_wo_table(data,page),
        error => this.handleError(error)
      );
    }

  }
  else if(filter=='search')
  {
    this.Jarwis.get_workorder(filter,from,0,2,page,sort_data,sort_type,sorting_name,sorting_method,null,null,null).subscribe(
      data  => this.form_wo_table(data,page),
      error => this.handleError(error)
    )
  }

}
assigned_claim_data;
closed_claim_data;
  //Form Claim Table
  public form_table(data,type,form_type)
  {
    //console.log("fom_datav ",data,type,form_type);
    if(form_type==null)
    {
      if(type=="wo")
    {
      this.table_fields=data.data.fields;
      this.table_datas=data.data.datas;
      this.total_claims=data.count;

      // this.total=data.total;
      // this.current_total= data.current_total - 1;
      // this.skip = data.skip;

      // this.skip_row = this.skip;
      // this.current_row = this.skip + this.current_total;
      // this.total_row = data.count;
    }
    else if(type=='allocated')
    {
      // console.log(data);
      this.allocated_claims=data.data.datas;
      this.assigned_claim_data = data.selected_claim_data;
      this.total_allocated=data.count;

      this.current_total= data.current_total;
      this.skip = data.skip + 1;

      this.skip_row = this.skip;
      this.current_row = this.skip + this.current_total - 1;
      this.total_row = data.count;

    }
    else if(type=='completed'){
      this.completed_claims=data.data.datas;
      this.closed_claim_data = data.selected_claim_data;
      this.total_completed_claims=data.count;

      //this.total=data.total;
      this.current_total= data.current_total;
      this.skip = data.skip + 1;

      this.skip_row = this.skip;
      this.current_row = this.skip + this.current_total - 1;
      this.total_row = data.count;
    }

    }
    else if(form_type == 'refresh')
    {
      let new_claim;

      if(type=="wo")
      {
        this.table_fields=data.data.fields;
        this.table_datas=data.data.datas;
        this.total_claims=data.count;
        if(this.claim_active != undefined)
        {
          new_claim=this.table_datas.find(x => x.claim_no == this.claim_active['claim_no']);
        }

      }
      else if(type=='allocated')
      {
        // console.log(data);
        this.allocated_claims=data.data.datas;
        this.total_allocated=data.count;
        if(this.claim_active != undefined)
        {
        new_claim=this.allocated_claims.find(x => x.claim_no == this.claim_active['claim_no']);
        }
      }
      else if(type=='completed'){
        this.completed_claims=data.data.datas;
        this.total_completed_claims=data.count;
        if(this.claim_active != undefined)
        {
        new_claim=this.completed_claims.find(x => x.claim_no == this.claim_active['claim_no']);
        }
      }
      if(this.claim_active != undefined)
      {
      if(this.main_tab==true)
      {
        this.getnotes(this.claim_active);

        this.claimslection(new_claim);
      }
      else{
        this.Jarwis.getnotes(this.claim_active).subscribe(
          data  =>{
            let prcs_data={data:data['data']['process']};
            let refer_data ={data:data['data']['claim']};
            let qc_data = {data:data['data']['qc']};
            this.update_refer_notes(prcs_data,'processnotes',this.claim_active.claim_no);
            this.update_refer_notes(refer_data,'claimnotes',this.claim_active.claim_no);
            this.update_refer_notes(qc_data,'qcnotes',this.claim_active.claim_no);
          } ,
          error => this.handleError(error)
        );

        // console.log("Goos sdhfb",this.refer_claim_notes,this.refer_process_notes,this.refer_qc_notes,this.refer_client_notes);

        this.referclaim(this.claim_active);
    }
    }
    this.tab_load=false;
  }

  }

  //Managing Values displayed in Modal
  public claim_no;

  tooltipOptions= {
        'placement': 'right',
        'show-delay': '200',
        'tooltip-class': 'new-tooltip-class',
        'background-color': '#9ad9e4',
        'margin-top': '20px'
      };

  public tooltip(claim){
    this.claim_no = claim.claim_no;

    this.Jarwis.claims_tooltip(this.claim_no).subscribe(
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

  public claimslection(claim)
  {
    // console.log("Here",claim);
    this.claim_no = claim.claim_no;
    this.get_line_items(claim);
    this.check_reassign_alloc(claim);
    //Clear Previous Claims
    this.clear_refer();
    this.claim_clicked=claim;
    console.log(this.claim_clicked);
    let length=this.table_datas.length;
    this.claim_related=[];
    this.get_related(claim);
    // for(let i=0;i<this.table_datas.length;i++)
    // {
    //   let related_length=this.claim_related.length;
    //   length= length-1;
    //   if(related_length<3)
    //   {
    //     if(this.table_datas[length]['acct_no'] == claim.acct_no && this.table_datas[length]['claim_no'] != claim.claim_no )
    //     {
    //     this.claim_related.push(this.table_datas[length]);
    //     }
    //   }
    // }
    this.send_calim_det('footer_data');
    this.loading=true;
    this.getnotes(this.claim_clicked);
    // this.process_notes_delete(this.claim_no);
  }


  get_related(claim)
{
  this.Jarwis.get_related_calims(claim,'followup',this.setus.getId()).subscribe(
    data  => this.list_related(data),
    error => this.handleError(error)
    );
}

list_related(claims)
{
  this.claim_related = claims.data;
  //console.log(this.claim_related);
}

  //Refer Claim Clicked Action
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
  refer_claim_notes_nos=[];
  refer_process_notes_nos=[];
  refer_qc_notes_nos=[];
  refer_client_notes_nos=[];
  refer_client_notes=[];
  active_refer_client=[];
  refer_claim_editable='false';
  claim_status;
  claim_nos;

  //Refer Claim
  public referclaim(claim)
  {

   // (claim.editable == false) ? (this.refer_claim_editable = false) : (this.refer_claim_editable = true);
  claim=claim.claim;

  this.claim_nos = claim.claim_no;

  console.log(this.type);

  this.claim_status = claim.claim_Status;
  this.Jarwis.get_audit_claimno(this.claim_nos, this.setus.getId(), this.claim_status, this.type).subscribe(
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
    this.check_reassign_alloc(this.claim_clicked);
  }

  //Clear Tabs Details
  public clear_refer()
  {
    this.main_tab=true;
    this.active_claim=[];
    this.refer_claim_det=[];
    this.refer_claim_no=[];
  }
  submitted=false;
  get f() { return this.processNotes.controls; }
  get v() { return this.qcNotes.controls; }
  get c() { return this.claimNotes.controls; }


  

  //Update Displayed Notes
  public display_notes(data,type)
  {
    console.log(data);
    console.log(type);
  if(this.active_claim != undefined)
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

                }


public process_display_notes(data,type)
  {
    console.log(data);
    console.log(type);
  if(this.active_claim != undefined)
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

     }


  //Get Notes
  public getnotes(claim)
  {
    // console.log("get_notes",claim)
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
  initial_edit:boolean=false;
  proess_initial_edit;
  public editnotes(type,value,id)
  {
    if(type=='qc_notes_init')
    {
      let qc_data=this.qc_notes_data.find(x => x.id == id['claim_no']);
      this.editnote_value=qc_data.notes;
      this.edit_noteid=id;
      this.initial_edit=true;
    }else if(type=='process_notes_init')
    { let process_data=this.process_notes_data.find(x => x.id == id['claim_no']);
      this.editnote_value=process_data.notes;
      this.edit_noteid=id;
      this.proess_initial_edit=true;
    }
    else{
      this.editnote_value=value.content;
      this.edit_noteid=id;

      if(type=='qcnote')
      {
        let root_cause= value.root_cause;
        let error_type= JSON.parse(value.error_type);

        // console.log(this.audit_codes_list);
        let root_det=this.audit_codes_list.root.find(x => x.id = root_cause );

        let error_det = this.audit_codes_list.error;

        let selecetd_err=[];
// console.log("ERR_tyoe",error_type);
        error_type.forEach(function (value) {
          let keys = value;
          let error=error_det.find(x => x.id == keys );
          selecetd_err.push({id:keys,description:error['name']});
          });
        this.qcNotes.patchValue({
          root_cause: {id:root_cause,description:root_det['name']},
          error_type: selecetd_err
          });
      }




      this.initial_edit=false;
    }

  }

  rc_et_data:any;

  //Handle Rootcause and Error Type
  public handle_notes_opt()
  {
    // console.log("QC",this.qcNotes.value);

    let error_type=this.qcNotes.value['error_type'];
    let root_cause=this.qcNotes.value['root_cause'];
    let error_parameter = this.qcNotes.value['parameter'];
    let error_sub_parameter = this.qcNotes.value['sub_parameter'];
    let fyi_parameter = this.qcNotes.value['fyi_parameter'];
    let fyi_sub_parameter = this.qcNotes.value['fyi_sub_parameter'];
    if (error_type !="1" && error_type =="2"){
      if (this.qcNotes.value['error_parameter'] !=null && this.qcNotes.value['error_sub_parameter'] !=null){
        error_parameter = this.qcNotes.value['error_parameter'];
        error_sub_parameter = this.qcNotes.value['error_sub_parameter'];
      }
      else{
        error_parameter = null;
        error_sub_parameter = null;
      }
    }
    else if(error_type !="1" && error_type =="3"){
      if (this.qcNotes.value['fyi_parameter'] !=null && this.qcNotes.value['fyi_sub_parameter'] !=null){
        fyi_parameter = this.qcNotes.value['fyi_parameter'];
        fyi_sub_parameter = this.qcNotes.value['fyi_sub_parameter'];
      }
      else{
        fyi_parameter = null;
        fyi_sub_parameter = null;
      }
    }
    else{
      error_parameter = null;
      error_sub_parameter = null;
      fyi_parameter = null;
      fyi_sub_parameter = null;
    }    
    
    let error_types_ids=[];
if (error_type.length >1){
  error_type.forEach(function (value) {
    let keys = value;
    error_types_ids.push(keys['id']);
    });
}
else{
  let keys = error_type;
  console.log(keys);
  error_types_ids.push(keys);
}

      
     

this.rc_et_data={root_cause:null,error_types:error_types_ids,error_parameter:error_parameter,error_sub_parameter:error_sub_parameter,fyi_parameter:fyi_parameter,fyi_sub_parameter:fyi_sub_parameter}

  }


  //Save Notes
  public process_notes_data_list =[];
  public process_notes_data =[];
  request_monitor:number=0;

  note_refresh(){
    this.process_notes_data_list =[];
    this.qc_notes_data_list =[];
  }

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
        console.log(this.claim_clicked);
        }
        if(type=='processnotes')
        {
          this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],claim_id,'processcreate', 'followup').subscribe(
            data  => this.display_notes(data,type),
            error => this.handleError(error)
          );
          // this.request_monitor=0;
          // this.process_notes_data.push({notes:this.processNotes.value['processnotes'],id:claim_id['claim_no']});
          // this.process_notes_data_list.push(claim_id['claim_no']);
          // this.notes_hadler.set_notesest(this.setus.getId(),this.processNotes.value['processnotes'],claim_id,'process_create');
          // this.send_calim_det('footer_data');
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
          //console.log(this.qcNotes.value['qc_notes']);
          console.log('claaim id  :' + claim_id);

          this.submitted=true;
          
          this.handle_notes_opt();
          // console.log("QC",this.rc_et_data);
          this.qc_notes_data.push({notes:this.qcNotes.value['qc_notes'],id:claim_id['claim_no'],notes_opt:this.rc_et_data});
          this.qc_notes_data_list.push(claim_id['claim_no']);
          let notes_det={notes:this.qcNotes.value['qc_notes'],notes_opt:this.rc_et_data};

          // this.Jarwis.qc_note(this.setus.getId(),notes_det,claim_id,'create_qcnotes').subscribe(
          //   data  => this.display_notes(data,type),
          //   error => this.handleError(error)
          //   );

          this.notes_hadler.set_notes(this.setus.getId(),notes_det,claim_id,'create_qcnotes');
          this.send_calim_det('footer_data');
            }
            }



  //Update Notes
  public updatenotes(type)
  {
    if(this.initial_edit==true)
    {
      this.handle_notes_opt();
    // console.log("QC",this.rc_et_data);
    let notes_det={notes:this.qcNotes.value['qc_notes'],notes_opt:this.rc_et_data};
      this.notes_hadler.set_notes(this.setus.getId(),notes_det,this.edit_noteid,'create_qcnotes');

      // this.qc_notes_data[this.edit_noteid['claim_no']]=this.qcNotes.value['qc_notes'];

      this.qc_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.qcNotes.value['qc_notes'];


      this.initial_edit=false;
      this.send_calim_det('footer_data');
    }
    // else if(this.proess_initial_edit==true){
    //   this.notes_hadler.set_notesest(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'claim_create');
    //   this.process_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.processNotes.value['processnotes'];
    //   this.initial_edit=false;
    //   this.send_calim_det('footer_data');
    // }
    else{

      if(type=='processnotes')
      {
      this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'processupdate', 'audit-closed').subscribe(
        data  => this.display_notes(data,type),
        error => this.handleError(error)
      );
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


        let claim_active;
        console.log(this.edit_noteid);

    if(this.main_tab == true)
    {
      claim_active=this.claim_clicked;
      console.log(claim_active);
    }
    else{
      claim_active=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
    }



        this.Jarwis.check_edit_val(claim_active,'audit').subscribe(
          data  => {this.set_note_edit_validity(data);
            if(this.note_edit_val != undefined)
            {
              this.handle_notes_opt();
              let notes_det={notes:this.qcNotes.value['qc_notes'],notes_opt:this.rc_et_data};

            this.Jarwis.qc_note(this.setus.getId(),notes_det,this.edit_noteid,'qcupdate').subscribe(
              data  => this.display_notes(data,type),
              error => this.handleError(error)
            );
          }
          else{
            this.toastr.errorToastr('Notes cannot be Updated.', 'Claim Processed.');
          }
          },
          error => this.handleError(error)
        );


      }

    }
    this.editnote_value=null;
  }


  public closedupdatenotes(type)
  {
    if(this.initial_edit==true)
    {
      this.handle_notes_opt();
    // console.log("QC",this.rc_et_data);
    let notes_det={notes:this.qcNotes.value['qc_notes'],notes_opt:this.rc_et_data};
      this.notes_hadler.set_notes(this.setus.getId(),notes_det,this.edit_noteid,'create_qcnotes');

      // this.qc_notes_data[this.edit_noteid['claim_no']]=this.qcNotes.value['qc_notes'];

      this.qc_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.qcNotes.value['qc_notes'];


      this.initial_edit=false;
      this.send_calim_det('footer_data');
    }
    else{

      if(type=='processnotes')
      {
      this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'processupdate', 'audit-closed').subscribe(
        data  => this.display_notes(data,type),
        error => this.handleError(error)
      );
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


        let claim_active;

    if(this.main_tab == true)
    {
      claim_active=this.claim_clicked;
    }
    else{
      claim_active=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
    }



        this.Jarwis.check_edit_val(claim_active,'audit').subscribe(
          data  => {this.set_note_edit_validity(data);
            if(this.note_edit_val != undefined)
            {
              this.handle_notes_opt();
              let notes_det={notes:this.qcNotes.value['qc_notes'],notes_opt:this.rc_et_data};

            this.Jarwis.qc_note(this.setus.getId(),notes_det,this.edit_noteid,'qcupdate').subscribe(
              data  => this.display_notes(data,type),
              error => this.handleError(error)
            );
          }
          else{
            this.toastr.errorToastr('Notes cannot be Updated.', 'Claim Processed.');
          }
          },
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

  //Open Pop-up
  open(content) {
      this.modalService.open(content, { centered: true,windowClass: 'dark-modal' }).result.then((result) => {
        this.closeResult = `${result}`;
      }, (reason) => {
        this.closeResult = `${this.getDismissReason()}`;
      });
  }

  //Modal Dismiss on Clicking Outside the Modal
  private getDismissReason() {
  this.clear_notes();
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
        this.claim_active = this.claim_clicked;
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
        this.claim_active= claim_detials;
      }
    }
  }

  //Collect Response Forom Footer Component after Claim processing
  public collect_response(data)
  {
    console.log(data);

   if(this.main_tab == true)
   {
    this.check_note_edit_validity(this.claim_clicked);
   }
   else{

    let claim_detials=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
    this.check_note_edit_validity(claim_detials);
   }



    // this.check_note_edit_validity(this.active_claim)
    this.display_notes(data,'qcnotes');
    this.getclaim_details(1,'allocated','null','null','null','null',null,null,null,null,null);
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);
    this.getclaim_details(1,'completed','null','null','null','null',null,null,null,null,null);
    let index =  this.qc_notes_data_list.indexOf(this.active_claim);
    this.qc_notes_data_list.splice(index, 1);
    // console.log(this.qc_notes_data_list);
   let index1 =  this.process_notes_data_list.indexOf(this.active_claim);
   this.process_notes_data_list.splice(index1, 1);
  }

  //Get Auditor details
  auditors_detail :Array<any> = [];
  public get_auditors()
  {
    this.Jarwis.get_auditors(this.setus.getId()).subscribe(
      data  => this.assign_auditors(data),
      error => this.handleError(error)
      );
  }

  //Assign and List auditor details
  public assign_auditors(data)
  {
    //console.log(data);
    this.auditors_detail=data.data;
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
      let audit_claim_data = this.audit_claim_data;
      let claim_nos=this.selected_claim_nos;
      let claim_data= this.selected_claims;
      audit_claim_data.forEach(function (value) {
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
      for(let i=0;i<this.audit_claim_data.length;i++)
      {
        let claim=this.audit_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }
    else if(event.target.checked == true)
    {
      this.selected_claims.push(this.audit_claim_data[index]);
      this.selected_claim_nos.push(claim);
      console.log(this.selected_claim_nos);
      }
      else if(event.target.checked == false)
      {
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }

  public assigned_selected(event,claim,index)
  {
    if(claim == 'all' && event.target.checked == true )
    {
      let assigned_claim_data = this.assigned_claim_data;
      let claim_nos=this.selected_claim_nos;
      let claim_data= this.selected_claims;
      assigned_claim_data.forEach(function (value) {
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
      for(let i=0;i<this.assigned_claim_data.length;i++)
      {
        let claim=this.assigned_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }
    else if(event.target.checked == true)
    {
      this.selected_claims.push(this.assigned_claim_data[index]);
      this.selected_claim_nos.push(claim);
      }
      else if(event.target.checked == false)
      {
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }


    public closed_selected(event,claim,index)
  {
    if(claim == 'all' && event.target.checked == true )
    {
      let assigned_claim_data = this.closed_claim_data;
      let claim_nos=this.selected_claim_nos;
      let claim_data= this.selected_claims;
      assigned_claim_data.forEach(function (value) {
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
      for(let i=0;i<this.closed_claim_data.length;i++)
      {
        let claim=this.closed_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
    }
    else if(event.target.checked == true)
    {
      this.selected_claims.push(this.closed_claim_data[index]);
      this.selected_claim_nos.push(claim);
      }
      else if(event.target.checked == false)
      {
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }
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
    if(this.selected_associates.length==0){
      this.toastr.errorToastr("Please select Associate")
    }
    else{
      this.claim_assign_type=type;
    }
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
          // this.check_limit();
          this.proceed_stats();
          }
          assigned_claim_status:boolean=false;
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

  claim_proceed:boolean=true;
  proceed_stats()
  {
    // selected_claim_nos.length==0 || selected_claim_nos.length < assigned_claim_nos
    // console.log(this.selected_claim_nos.length ,',', this.assigned_claim_nos, this.selected_claim_nos.length,this.limit_exceeds  )
    // console.log(this.selected_associates.length, this.selected_claim_nos.length , this.assigned_claim_nos , this.claim_assign_type , this.assigned_claims_details.length )


    if(this.selected_associates.length == 0 || this.selected_claim_nos.length < this.assigned_claim_nos )
    {
      // console.log("P_Stats  -> True")
      this.claim_proceed=true;
    }
    else{
      // console.log("P_Stats  -> False")
      this.claim_proceed=false;
    }
    // console.log(this.claim_proceed)

  }

  limit_clearance:boolean=false;
  limit_exceeds=[];
  //Monitor Limit of Associates
  check_limit()
  {
  // console.log("Here",this.assigned_claims_details)

  for(let i=0;i < this.assigned_claims_details.length;i++)
  {
  let associate=this.auditors_detail.find(x => x['id'] == this.assigned_claims_details[i]['id']);

  let total_assigned=Number(this.assigned_claims_details[i]['value']) + Number(associate['assigned_claims']);
  // console.log("Ta",total_assigned,associate['assign_limit'])
  if(associate['assign_limit'] < total_assigned)
  {
    //Filter duplicate
    if(this.limit_exceeds.indexOf(associate['id']) < 0)
    {
      this.limit_exceeds.push(associate['id']);
    }
    // console.log("Limit _exccede",this.limit_exceeds)
    this.limit_clearance=false;
  }
  else
  {
    // console.log("Entered")
    if(this.limit_exceeds.length == 0)
    {
      this.limit_clearance=true;
    }
    else{
      //Splice code
      let index=this.limit_exceeds.indexOf(associate['id']);
      this.limit_exceeds.splice(index, 1);

      if(this.limit_exceeds.length == 0)
      {
        this.limit_clearance=true;
      }
    }
  }
  // console.log("Associate",associate);
  }

  }




  public assigned_claim_details:Array<any> =[];
  //Assign Claims to Create Work Order
  public assign_claims()
  {
    let selected_claims=this.selected_claim_nos;
    console.log(selected_claims);
    let assigned_details=[];
    let init_value=0;

    this.assigned_claims_details.forEach(function (value) {
      let keys = value;
      let id = keys['id'];
      let value_data = keys['value'];

      let claims_assigned=selected_claims.slice(init_value,Number(init_value)+Number(value_data));
      console.log(claims_assigned);
      init_value=value_data;
      assigned_details.push({assigned_to:id,claim_nos:value_data,claims:claims_assigned});
      });

      this.assigned_claim_details=assigned_details;
      console.log("O*/p",this.assigned_claim_details);
      this.assigned_claim_status=true;
  }


  public auto_assign_claims()
  {
    
    //alert("Auto");

    //console.log("Auto",this.selected_claim_nos,this.auditors_detail,this.selected_associates);

    let assignable_aud=[];
    console.log(this.selected_associates.length);
    if(this.selected_associates.length == 0){
      this.auditors_detail.forEach(element => {
          assignable_aud.push(element.id);
      });
      console.log(this.auditors_detail);
    }
    else{
      assignable_aud=this.selected_associates;
      console.log(assignable_aud);
      this.modalService.dismissAll()
    }

    let selected_claims=this.selected_claim_nos;
    console.log(selected_claims);
    let init_value=0;
    let auditors = this.auditors_detail;
    console.log(auditors);
    let assigned_details=[];


    let assign_value=0;
    console.log(assignable_aud);
    assignable_aud.forEach(function (value) {
          let keys = value;
          let auditor_det = auditors.find(x => x['id'] == keys);
          console.log(auditor_det);
          //Check Assignable Numbers
          console.log(auditor_det['assign_limit']);
          console.log(auditor_det['assigned_nos']);
          let assign_limit = Number(auditor_det['assigned_nos'])-Number(auditor_det['assign_limit']);
          console.log(assign_limit);
          // Check assignable claims nos
          if((selected_claims.length - Number(assign_value))  < assign_limit)
          {
            assign_limit = selected_claims.length;
            console.log(assign_limit);
          }

          console.log('this' +selected_claims.length,assign_value);
          console.log(assign_limit);
          if(/*assign_limit >0 && */(selected_claims.length - Number(assign_value)) !=0)
          {
            assign_value=Number(init_value)+Number(assign_limit);
            console.log(init_value);
            console.log(assign_value);
            let claims_assigned=selected_claims.slice(init_value,Number(init_value)+Number(assign_limit));
            init_value=Number(init_value)+Number(assign_limit);
            assigned_details.push({assigned_to:auditor_det['id'],claim_nos:assign_limit,claims:selected_claims});
            console.log(assigned_details);        
          }
    });

        this.assigned_claim_details=assigned_details;
        console.log("o/p",this.assigned_claim_details);
        this.assigned_claim_status=true;

        this.create_workorder();
      }



  public create_workorder()
  {
    this.Jarwis.create_workorder(this.setus.getId(),this.workOrder.value,this.assigned_claim_details,'audit').subscribe(
      data  => this.handle_workorder_creation(data),
      error => this.handleError(error)
      );
  }

  //Aftermath Work Order creation Handling
  public handle_workorder_creation(data)
  {
    this.toastr.successToastr('Work Order Created')
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);
    this.claim_assign_type=null;
    this.workOrder.reset();
    this.selected_claim_nos=[];
    this.selected_claims=[];
    this.check_all=[];
    this.assigned_claim_details=[];
    this.assigned_data=[];
    this.selected_claim_nos=[]; 

  }

wo_total:Number;
w_total;
w_current_total;
w_skip;
w_skip_rows;
w_current_row;
w_total_row;
public form_wo_table(data,page_no)
{
//  console.log(data);
  this.work_order_data=data.data;
  this.wo_total=data.count;
  this.wo_page_number=page_no;
  this.tab_load=false;

  this.w_total = data.count;
  this.w_current_total= data.current_total;
  this.w_skip = data.skip + 1;

  this.w_skip_rows = this.w_skip;
  this.w_current_row= this.w_skip + this.w_current_total - 1;
  this.w_total_row = this.w_total;
}

wo_details=[];
wo_name:string;
wo_created:string;


public export_files(type)
{
  let filter='all claims';
  let s_code='adjustment';

  this.Jarwis.fetch_audit_export_data(filter,s_code,this.setus.getId()).subscribe(
    data  => this.export_handler.sort_export_data(data,type,'claim'),
    error => this.handleError(error)
    );
}


public export_wo_files(type)
{
  let filter='all claims';
  let s_code='adjustment';
  let wo_type=2;
  this.Jarwis.fetch_wo_export_data(filter,s_code,wo_type,this.setus.getId()).subscribe(
    data  => this.export_handler.ready_wo_export(data,type),
    error => this.handleError(error)
    );

}

public wo_export_function(type)
{
  this.export_handler.sort_export_data(this.wo_details,type,'wo_detail');
}

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


public wo_details_table(data)
{
  this.loading=false;
this.wo_details=data.data;

}



line_data=[];

public get_line_items(claim)
{
  this.check_note_edit_validity(claim);
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


edit_permission:boolean=false;
check_edit_permission(data)
{
if(data.includes('audit'))
{
  console.log(data);
  this.edit_permission=true;
}
else{
  this.edit_permission=false;
}
//console.log(this.edit_permission);
}


confirmation_type:string;
reassign_claim:string;
curr_reassigned_claims=[];

confirm_reassign(claim:any)
{
  this.confirmation_type='Reassign';
this.reassign_claim=claim;
}

confirm_action(type)
{
if(type == 'Reassign')
{
  let mod_type='audit';
  this.Jarwis.reassign_calim(this.reassign_claim,this.setus.getId(),mod_type).subscribe(
    data  => this.after_reassign(data,this.reassign_claim['claim_no']) ,
    error => this.handleError(error)
  );

}
}

reassign_allocation:boolean=true;
after_reassign(data,claim)
{
  this.curr_reassigned_claims.push(claim);
  // this.getclaim_details(this.alloc_pages,'allocated');
  this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);
  this.reassign_allocation=false;
}

check_reassign_alloc(claim)
{
  console.log("ROle",this.setus.get_role(),claim['audit_work_order']);
  //console.log(this.setus.get_role_id());

  if(this.setus.get_role_id() == '4' && this.setus.get_role_id() == '3' && claim['audit_work_order'] != null)
  {
    let already_re=this.curr_reassigned_claims.indexOf(claim.claim_no);
    // console.log("Here REassign",claim,already_re);
    if(already_re<0)
    {
      this.reassign_allocation=true;
    }
    else
    {
      this.reassign_allocation=false;
    }

  }
  else{
    this.reassign_allocation=false;
  }

}

check_note_edit_validity(claim)
{
  this.Jarwis.check_edit_val(claim,'audit').subscribe(
    data  => this.set_note_edit_validity(data),
    error => this.handleError(error)
  );

}

note_edit_val:number;
set_note_edit_validity(data)
{
  if(data.edit_val == true)
  {
    this.note_edit_val = data.note_id['id'];
  }
  else
  {
    this.note_edit_val=undefined;
  }
}


reload_data(page)
{
  if(this.modalService.hasOpenModals() == false)
  {
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);

    for(let i=0;i<this.audit_claim_data.length;i++)
      {
        let claim=this.audit_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }

    let page_count=15;

    this.pages=page;
    this.Jarwis.get_audit_table_page('null',this.pages,page_count,'null','null','null',null,null,null,null,null).subscribe(
      data  => this.assign_page_data(data),
      error => this.handleError(error)
    );

    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
  }
}


reload_datas(page)
{
  if(this.modalService.hasOpenModals() == false)
  {
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);

    for(let i=0;i<this.audit_claim_data.length;i++)
      {
        let claim=this.audit_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind,1);
        this.selected_claim_nos.splice(ind,1);
      }

    let page_count=15;

    this.pages=page;
    this.Jarwis.get_audit_table_page('null',this.pages,page_count,'null','null','null',null,null,null,null,null).subscribe(
      data  => this.assign_page_data(data),
      error => this.handleError(error)
    );

    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
  }
}

root_cause_list=[];
err_type_list=[];
audit_codes_list:any;

get_audit_codes()
{
  if(!this.audit_codes_list )
  {
    this.Jarwis.get_audit_codes(this.setus.getId()).subscribe(
      data  => this.assign_audit_codes(data),
      error => this.handleError(error)
    );
  }
  //console.log(this.audit_codes_list);

}



assign_audit_codes(data)
{
  console.log(data);
  let root_stats=data.root_states;
  console.log(root_stats);
  let err_stats =data.err_types;

  this.audit_codes_list={root:root_stats,error:err_stats};

  let root_states=[];
  console.log(root_states);
for(let i=0; i<root_stats.length ;i++)
{
  if(root_stats[i].status=='1'){
  root_states.push({id: root_stats[i]['id'], description: root_stats[i]['name']});
}
}
this.root_cause_list=root_states;
let error_states=[];
for(let j=0;j< err_stats.length;j++)
{
  if(err_stats[j].status=='1'){
  error_states.push({id: err_stats[j]['id'], description: err_stats[j]['name'] });
  }
}
this.err_type_list=error_states;
// console.log("err",this.err_type_list,this.root_cause_list);
  // sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });

}

 //Configuration of Dropdown Search
 config = {
  displayKey:"description",
  search:true,
  result:'single'
 }

 //isChecked;
 auto_select_claims(event)
 {

  //console.log("checked",this.selected_claim_nos,event.target.checked);
  // if(!this.isChecked)
  // {
    this.Jarwis.auto_assign_claims(this.setus.getId()).subscribe(
      data  => this.assign_auto_select(data),
      error => this.handleError(error)
    );

  // }

 }

  assigntype_reset;

  removeTextbox() {
     //this.assign_type(this.type).reset(this.type);
     this.assigntype_reset = this.assign_type(this.type);
     this.assigntype_reset='';
     this.associateCount='';
   }

 isCheck = true;
 checkedEvnt(val) {
     for(let i =0;i < this.auditors_detail.length;i++){
       this.auditors_detail[i].isCheck = val;
     }
     this.associateCount='';
   }

 assign_auto_select(data)
 {

  this.selected_claim_nos=[];
   let work_claims=data.data;
   let assignable=[];

   for(let i=0;i<work_claims.length;i++)
   {
    assignable=work_claims[i]['work_claims'];

    for(let j=0;j<assignable.length;j++)
    {
      this.selected_claim_nos.push(assignable[j]);
    }


   }

console.log("AA_Op",this.selected_claim_nos);

 }


//  get_touch_limit()
//  {
//    this.Jarwis.get_practice_stats().subscribe(
//      data =>this.set_prac_settings(data)
//      );
//  }

 touch_count:number;
//  set_prac_settings(data)
//  {
//    let prac_data=data.data;
//    this.touch_count=prac_data.touch_limit;

//   //  console.log(this.touch_count);

//  }

dataSource={
  "chart": {
      "caption": "Sales of Liquor",
      "subCaption": "Previous week vs current week",
      "xAxisName": "Day",
      "yAxisName": "Sales (In USD)",
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
}

myOptions = {
  'placement': 'right',
  'hide-delay': 3000,
  'theme':'light'
}

user_role:Number=0;
class_change=[];
class_change_tab=[];
user_role_maintainer()
{
let role_id=Number(this.setus.get_role_id());

  if(role_id == 5 || role_id == 3 || role_id == 2)
  {
    this.user_role=2;
    this.class_change['tab1']='';
    this.class_change['tab2']='active';

    this.class_change_tab['tab1']='tab-pane';
    this.class_change_tab['tab2']='tab-pane active'




  }
  else if(role_id == 4)
  {
    this.user_role=1;

    this.class_change['tab1']='active';
    this.class_change['tab2']='';

    this.class_change_tab['tab1']='tab-pane active';
    this.class_change_tab['tab2']='tab-pane'


  }



}

graphStatus()
{
  let role_id=Number(this.setus.get_role_id());
  var exclusion = [2,4,5];

  if(!exclusion.includes(role_id) )
  {
    this.Jarwis.get_audit_graph(this.setus.getId()).subscribe(
      data  => {console.log(data)},
      error => this.handleError(error)
    );
  }

}

  // order_list(type){
  //   if(this.sortByAsc == true) {
  //     this.sortByAsc = false;
  //     this.getclaim_details(1,'wo',this.sortByAsc,type);
  //   } else {
  //     this.sortByAsc = true;
  //     this.getclaim_details(1,'wo',this.sortByAsc,type);
  //   }
  // }



  ngOnInit() {
    this.user_role_maintainer();
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);
    // this.get_auditors();

    this.auditClaimsFind = this.formBuilder.group({
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

    this.assignedClaimsFind = this.formBuilder.group({
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


    this.closedClaimsFind = this.formBuilder.group({
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

    this.workOrderFind = this.formBuilder.group({
      created_at: [],
      due_date: [],
      work_order_name: [],
      priority: [],
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
        ]),
        root_cause: new FormControl(null),
        error_type: new FormControl('', [
            Validators.required
            ]),
            error_parameter:new FormControl(''),
            error_sub_parameter:new FormControl(''),
              fyi_parameter:new FormControl(''),
              fyi_sub_parameter:new FormControl('')
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

const debouncetime = pipe(debounceTime(700));
this.search_data.valueChanges.pipe(debouncetime)
.subscribe( result => this.sort_data(result)
);
this.wo_search_data.valueChanges.pipe(debouncetime)
.subscribe( result => this.sort_wo_data(result)
);
this.filter_option.valueChanges
.subscribe( result => this.sort_table(result)
);
this.subscription=this.notify_service.fetch_touch_limit().subscribe(message => {
  this.touch_count = message });

  this.graphStatus();
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

public reassign(content){
  if(this.selected_claim_nos.length==0){
    this.toastr.errorToastr('Please select Claims');
  }
  else{
    this.modalService.open(content, { centered: true ,windowClass:'custom-class'}).result.then((result) => {
      this.closeResult = `${result}`;
    }, (reason) => {
      this.closeResult = `${this.getDismissReason()}`;
    });
  }
}

confirm_box()
{
    this.Jarwis.get_closed_claims(this.selected_claim_nos,this.setus.getId()).subscribe(
      data  => this.reassigned_claims(data),
      error => this.handleError(error)
    );
}
reassigned_claims(data){
  console.log(data);
  if(this.selected_claim_nos.length==0){
    this.toastr.errorToastr('please select Claims');
  }

  if(data.status == 'success'){

    let type = 'allocated';
    this.getclaim_details(1,'allocated','null','null','null','null',null,null,null,null,null);

    this.toastr.successToastr( 'Claim move to closed.');
  }
}
  cancel_claims(){
    this.selected_claim_nos=[];
  }

  public select_claims(content){
    if(this.selected_claim_nos.length==0)
    {
      this.toastr.errorToastr( 'Please Select Claims.');
    }
    else{
      this.modalService.open(content, { centered: true,windowClass: 'dark-modal' }).result.then((result) => {
        this.closeResult = `${result}`;
      }, (reason) => {
        this.closeResult = `${this.getDismissReason()}`;
      });
    }
  }
  public clear_fields()
  {
    this.assigned_claims_details=[];
    this.workOrder.reset();
  }
  public sort_details(type) {
    if(type=='id'){
            if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.table_datas.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
        this.work_order_data.sort((a,b) => a.created.localeCompare(b.created));
        this.allocated_claims.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
      } else {
        this.sortByAsc = true;
        this.table_datas.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
        this.work_order_data.sort((a,b) => b.created.localeCompare(a.created));
        this.allocated_claims.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
     }
    }
    else if(type=='claims'){
      if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.table_datas.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
        this.allocated_claims.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
      } else {
        this.sortByAsc = true;
        this.table_datas.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
        this.allocated_claims.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
     }
    }
    else if(type=='patient'){
      if(this.sortByAsc == true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
        this.table_datas.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
        this.work_order_data.sort((a,b) => a.work_order_name.localeCompare(b.work_order_name));

      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
        this.table_datas.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
        this.work_order_data.sort((a,b) => b.work_order_name.localeCompare(a.work_order_name));

      }
    }
    else if(type=='insurance'){
      if(this.sortByAsc == true){
        this.sortByAsc=false;
        this.table_datas.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
        this.allocated_claims.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
      }
      else{
        this.sortByAsc=true;
        this.table_datas.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
        this.allocated_claims.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
      }
    }
    else if(type=='bill'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.table_datas.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
        this.allocated_claims.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
      }
      else{
        this.sortByAsc=true;
        this.table_datas.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
        this.allocated_claims.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
      }
    }
    else if(type=='due'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.table_datas.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
        this.work_order_data.sort((a,b) => a.due_date.localeCompare(b.due_date));
        this.allocated_claims.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
      }
      else{
        this.sortByAsc=true;
        this.table_datas.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
        this.work_order_data.sort((a,b) => b.due_date.localeCompare(a.due_date));
        this.allocated_claims.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
      }
    }
    else if(type=='status'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.table_datas.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
       this.work_order_data.sort((a,b) => a.status.localeCompare(b.status));
       this.allocated_claims.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
      }
      else{
        this.sortByAsc=true;
        this.table_datas.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
        this.work_order_data.sort((a,b) => b.status.localeCompare(a.status));
        this.allocated_claims.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
      }
    }
    else if(type=='dos'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.table_datas.sort((a,b) => a.dos.localeCompare(b.dos));
        this.allocated_claims.sort((a,b) => a.dos.localeCompare(b.dos));
      }
      else{
        this.sortByAsc=true;
        this.table_datas.sort((a,b) => b.dos.localeCompare(a.dos));
        this.allocated_claims.sort((a,b) => b.dos.localeCompare(a.dos));
      }
    }
    }
    public sort_claims(type){
      if(type=='acct_no'){
        if(this.sortByAsc == true) {
    this.sortByAsc = false;
    this.wo_details.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
  } else {
    this.sortByAsc = true;
    this.wo_details.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
  }
  }
  if(type=='claim_no'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
  }
  }
  if(type=='patient_name'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
  }
  }
  if(type=='dos_date'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.dos.localeCompare(b.dos));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.dos.localeCompare(a.dos));
  }
  }
  if(type=='prim_ins_name'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
  }
  }
  if(type=='total_charges'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
  }
  }
  if(type=='total_ar'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
  }
  }
  if(type=='claim_Status'){
    if(this.sortByAsc == true) {
  this.sortByAsc = false;
  this.wo_details.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
  } else {
  this.sortByAsc = true;
  this.wo_details.sort((a,b) => b.claim_Status.localeCompare(a.claim_Status));
  }
  }
  }
  public togglecollapse(){
    // alert("hi")
    this.isopend=!this.isopend;

  }

  public searchClaims;

  public export_excel_files(type, table_name)
  {
      console.log(table_name);

       if(table_name == 'Audit_que_claims'){
         this.searchClaims = this.auditClaimsFind.value;
       }else if(table_name == 'Assigned_claims'){
         this.searchClaims = this.assignedClaimsFind.value;
       }else if(table_name == 'Closed_claims'){
         this.searchClaims = this.closedClaimsFind.value;
       }

   this.Jarwis.fetch_audit_claims_export_data(this.setus.getId(), table_name, this.search, this.searchClaims).subscribe(
      data  => this.export_handler.create_claim_export_excel(data),
      error => this.error_handler(error)
      );
  }

  public export_pdf_files(type, table_name)
  {
    let filter='all claims';
    let s_code='adjustment';

    this.Jarwis.fetch_audit_claims_export_data_pdf(this.setus.getId(), table_name).subscribe(
      data  => this.export_handler.sort_export_data(data,type,'claim'),
      error => this.error_handler(error)
    );
  }


  public auto_assigned(){
     
      console.log(this.selected_claim_nos);
      //this.setus.getId(),this.workOrder.value,this.assigned_claim_details,'audit'
      this.Jarwis.auto_assigned(this.setus.getId(), this.selected_claim_nos,this.workOrder.value,this.assigned_claim_details,'audit').subscribe(
        data  => this.auto_assigned_data(data),
        error => this.error_handler(error)
      );
  }

  public auto_assigned_data(data){
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null,null);
    this.modalService.dismissAll();
    this.clear_notes();
    this.workOrder.reset();
    this.selected_claim_nos = [];

    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });

    
  }

  public export_excel_wo_files(type, table_name)
  {
    this.Jarwis.fetch_work_order_export_data(this.setus.getId(), table_name, this.search, this.workOrderFind.value).subscribe(
      data  => this.export_handler.create_wo_export_excel(data),
      error => this.error_handler(error)
    );
  }

  error_handler(error){

  }

  get fe() { return this.workOrder.controls; }

    claimValidators() {
      this.workOrder = this.formBuilder.group({
        workorder_name: ['', Validators.required],
        due_date: ['', Validators.required],
        priority: ['', Validators.required],
        wo_notes: ['', Validators.required]
      });
    }
  // tooltipOptions= {
  //     'placement': 'right',
  //     'show-delay': '200',
  //     'tooltip-class': 'new-tooltip-class',
  //     'background-color': '#9ad9e4'
  //   };

    // public clear_auditor()
    // {
      // this.assigned_claims_details=[];
      // this.assign_claims.reset();
    //}
   }

