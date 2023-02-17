import { Component,ViewChildren,QueryList,ElementRef, OnInit,Input,ChangeDetectionStrategy,HostListener,ViewEncapsulation, OnDestroy } from '@angular/core';
import { SetUserService } from '../../Services/set-user.service';
import { JarwisService } from '../../Services/jarwis.service';
import { LoadingBarService } from '@ngx-loading-bar/core';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, Validators, FormBuilder } from "@angular/forms";
import { FollowupService } from '../../Services/followup.service';
import { NotesHandlerService } from '../../Services/notes-handler.service';
import { Subscription } from 'rxjs';
import { ExportFunctionsService } from '../../Services/export-functions.service';
import { ToastrManager } from 'ng6-toastr-notifications';
import { NotifyService } from '../../Services/notify.service';
import { debounceTime } from 'rxjs/operators';
import { pipe } from 'rxjs/util/pipe';
import * as moment from 'moment';

@Component({
  selector: 'app-followup',
  templateUrl: './followup.component.html',
  styleUrls: ['./followup.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class FollowupComponent implements OnInit, OnDestroy {

  assigned = "";
  reAssigned = "";
  closedWork = "";
  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;

  public status_codes_data:Array<any> =[];
  public sub_status_codes_data:string[];
  public status_options;
  public sub_options;
  decimal_pattern = "^\[0-9]+(\.[0-9][0-9])\-\[0-9]+(\.[0-9][0-9])?$";
  selecteds: any;
  selectedReAssigin: any;
  selectedClosed: any;
  alwaysShowCalendars: boolean;
  selectedAge = null;
  reassignedSelectedAge = null;
  closedSelectedAge = null;
  assigned_select_date: any;
  reassigned_select_date: any;
  closed_select_date: any;
  age_options:any = [{ "from_age": 0, "to_age": 30 },{ "from_age": 31, "to_age": 60 },{ "from_age": 61, "to_age": 90 },{ "from_age": 91, "to_age": 120 }];
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
  update_monitor: Subscription;
  subscription: Subscription;
  total: number;
  constructor(
    private formBuilder: FormBuilder,
    private Jarwis: JarwisService,
    private setus: SetUserService,
    private loadingBar: LoadingBarService,
    private modalService: NgbModal,
    private follow: FollowupService,
    private notes_hadler:NotesHandlerService,
    private export_handler:ExportFunctionsService,
    public toastr: ToastrManager,
    private notify_service:NotifyService,
  ) {
    this.response_data=this.notes_hadler.get_response_data('followup').subscribe(message => { this.collect_response(message) });
    this.update_monitor=this.notes_hadler.refresh_update().subscribe(message => {

      if(this.request_monitor <1)
      {
        this.getclaim_details(this.pages,'refresh','null','null','null','null',null,null,null,null);
        this.request_monitor++;
      }
    });
    this.alwaysShowCalendars = true;
  }
    request_monitor:number=0;
    public table_fields : string[];
    public workorder_table =[];
    // public total_assigned:number=0;
    public claim_notes_data_list =[];
    current_claim_type:string;
    closeResult : string;
    total_claims:number;
    pages:number;
    claim_notes_data :Array<any> =[];
    completed_claims=[];
    total_completed_claims:number;
    comp_pages:number;
    tab_load:boolean=false;
    claim_active;
    allocated_claims=[];
    reallocated_claims=[];
    total_allocated:number;
    total_reallocated:number;
    alloc_pages:number;
    realloc_pages:number;
    loading:boolean;
    sortByAsc: boolean = true;


    formdata = new FormData();
    processNotes: FormGroup;
    claimNotes: FormGroup;
    assignedClaimsFind: FormGroup;
    reassignedClaimsFind: FormGroup;
    closedClaimsFind: FormGroup;
    search_data: FormControl = new FormControl();
    wo_search_data: FormControl = new FormControl();
    filter_option: FormControl = new FormControl();



    //Red Alerrt Box
private _opened: boolean = false;
private isOpen: boolean = false;
private _positionNum: number = 0;
private _modeNum: number = 1;
table_datas : string[]=[];


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

current_total;
skip;
total_row;
skip_row;
current_row;
assigned_claims;
reassigned_claims_data;
completed_claims_data;
reallocate_total_row;

//Assign Work Order Table Data
    public form_table(data,type,form_type)
    {
      if(form_type==null)
      {
      if(type=="wo")
      {
        this.table_fields=data.data.fields;
        this.workorder_table=data.data.datas;
        this.total_claims=data.count;
        // this.total_assigned=6;
      }
      else if(type=='completed'){
        this.completed_claims=data.data.datas;
        // this.completed_claims_data = data.selected_claim_data;
        this.completed_claims_data = data.data.datas;
        this.total_completed_claims=data.count;

        this.total=data.total;
        this.current_total= data.current_total;
        this.skip = data.skip + 1;

        this.skip_row = this.skip;
        this.current_row = this.skip + this.current_total - 1;
        this.total_row = data.count;
      }
      else if(type=='allocated')
      {
        this.allocated_claims=data.data.datas;
        // this.assigned_claims = data.selected_claim_data;
        this.assigned_claims = data.data.datas;
        this.total_allocated=data.count;

        this.total=data.total;
        this.current_total= data.current_total;
        this.skip = data.skip + 1;

        this.skip_row = this.skip;
        this.current_row = this.skip + this.current_total - 1;
        this.total_row = data.count;

      }
	  else if(type=='reallocated')
      {
        this.reallocated_claims=data.data.datas;
        console.log(this.reallocated_claims);
        // this.reassigned_claims_data = data.selected_claim_data;
        this.reassigned_claims_data = data.data.datas;
        this.total_reallocated=data.count;

        this.total=data.total;
        this.current_total= data.current_total;
        this.skip = data.skip + 1;

        this.skip_row = this.skip;
        this.current_row = this.skip + this.current_total - 1;
        this.reallocate_total_row = data.current_total;

      }
      this.tab_load=false;
    }
    else if(form_type == 'refresh')
    {
      let new_claim;

      if(type=="wo")
      {
        this.table_fields=data.data.fields;
        this.workorder_table=data.data.datas;
        this.total_claims=data.count;
        //console.log("In WO",this.claim_active,this.workorder_table )
        if(this.claim_active != undefined)
        {
          new_claim=this.workorder_table.find(x => x.claim_no == this.claim_active['claim_no']);
        }

        if(new_claim == undefined)
        {
          new_claim=this.claim_active;
        }

      }
      else if(type=='allocated')
      {
    // console.log(data);
        this.allocated_claims=data.data.datas;
        // this.assigned_claims = data.selected_claim_data;
        this.assigned_claims = data.data.datas;
        console.log(this.assigned_claims);
        this.total_allocated=data.count;
        if(this.claim_active != undefined)
        {
        new_claim=this.allocated_claims.find(x => x.claim_no == this.claim_active['claim_no']);
        }
      }
	   else if(type=='reallocated')
      {
        // console.log(data);
        this.reallocated_claims=data.data.datas;
        // this.reassigned_claims_data = data.selected_claim_data;
        this.reassigned_claims_data = data.data.datas;
        this.total_reallocated=data.count;
        if(this.claim_active != undefined)
        {
        new_claim=this.reallocated_claims.find(x => x.claim_no == this.claim_active['claim_no']);
        }
      }
      else if(type=='completed'){
        this.completed_claims=data.data.datas;
        // this.completed_claims_data = data.selected_claim_data;
        this.completed_claims_data = data.data.datas;
        this.total_completed_claims=data.count;
        if(this.claim_active != undefined)
        {
        new_claim=this.completed_claims.find(x => x.claim_no == this.claim_active['claim_no']);
        }
      }
      if(this.claim_active != undefined)
      {


        //console.log("Here",this.main_tab);
      if(this.main_tab==true)
      {
        //console.log("Main",this.claim_active);
        this.getnotes(this.claim_active);
      //console.log("NewClaims",new_claim)
        this.claimslection(new_claim);
      }
      else{


        let claim_active=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
        // console.log('ref',claim_active);

        this.Jarwis.getnotes(claim_active).subscribe(
          data  =>{
            let prcs_data={data:data['data']['process']};
            let refer_data ={data:data['data']['claim']};
            let qc_data = {data:data['data']['qc']};
            this.update_refer_notes(prcs_data,'processnotes',claim_active.claim_no);
            this.update_refer_notes(refer_data,'claimnotes',claim_active.claim_no);
            this.update_refer_notes(qc_data,'qcnotes',claim_active.claim_no);
          } ,
          error =>console.log("THeis1",error)
        );

        // console.log("Goos sdhfb",this.refer_claim_notes,this.refer_process_notes,this.refer_qc_notes,this.refer_client_notes);

        this.referclaim(claim_active);
    }
    }
    this.tab_load=false;
  }

  // console.log('Deploy Trie');
    }

sorting_name;


order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search)
{
  this.sorting_name = sort_type;

  if(this.sortByAsc == true) {
      this.sortByAsc = false;
      this.getclaim_details(this.alloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  } else {
      this.sortByAsc = true;
      this.getclaim_details(this.alloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  }

}

closed_sorting_name;

completed_order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search)
{
  this.closed_sorting_name = sort_type;

  if(this.sortByAsc == true) {
      this.sortByAsc = false;
      this.getclaim_details(this.comp_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  } else {
      this.sortByAsc = true;
      this.getclaim_details(this.comp_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  }

}

reassigned_sorting_name;

reassigned_order_list(type, sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search)
{
  this.reassigned_sorting_name = sort_type;

  if(this.sortByAsc == true) {
      this.sortByAsc = false;
      this.getclaim_details(this.realloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  } else {
      this.sortByAsc = true;
      this.getclaim_details(this.realloc_pages,type,this.sortByAsc,sort_type,sorting_name,sorting_method,null,null,null,search);
  }

}

search;
public assigned_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search){
  console.log('dadsas' + search);
  this.search = search;
  this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search);
}

public reassigned_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search){
  this.search = search;
  this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search);
}

public closed_claims_filter(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search){
  console.log('dadsas' + search);
  this.search = search;
  console.log(this.search);
  this.getclaim_details(page,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search);
}

claim_status_codes=[];
claim_sub_status_codes=[];
//Get Work Order Table Data
type;
types;
  public getclaim_details(page:number,type,sort_data,sort_type,sorting_name,sorting_method,assign_claim_searh,reassign_claim_searh,closed_claim_searh,search)
{

  this.type = type;
  let page_count=15;
  console.log("ip",type);
  let form_type=null;
  let searchs = this.search;
  console.log(searchs);

  if(type=='wo')
  {
    this.types='wo';
    this.pages=page;
    this.current_claim_type='wo';
    this.Jarwis.get_table_page(this.setus.getId(),page,page_count,type,sort_data,sort_type,null,null).subscribe(
      data  => this.assign_page_data(data),
      error => this.handleError(error)
      );
  }
  else if(type=='completed'){

    this.types='completed';

    if(sorting_name == 'null' && searchs != 'search'){
      console.log('com_1');
      this.comp_pages=page;
      this.current_claim_type='completed';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else if(searchs == 'search'){
      console.log('com_2');
      this.comp_pages=page;
      this.current_claim_type='reallocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,this.closedClaimsFind.value,this.search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else{
    console.log('com_3');
      this.comp_pages=page;
      this.current_claim_type='completed';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,null,this.search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }
  }
  else if(type=='allocated')
  {
    this.types='allocated';
    if(sorting_name == 'null' && searchs != 'search'){
      this.alloc_pages=page;
      this.current_claim_type='allocated';
      page_count = 100;
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else if(searchs == 'search'){
       this.alloc_pages=page;
      this.current_claim_type='allocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.sorting_name,this.sortByAsc,this.assignedClaimsFind.value,null,null,this.search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else{
      this.alloc_pages=page;
      this.current_claim_type='allocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.sorting_name,this.sortByAsc,null,null,null,this.search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }
  }
  else if(type=='reallocated')
  {
    this.types='reallocated';

    if(sorting_name == 'null' && searchs != 'search'){

      console.log(searchs);

      this.realloc_pages=page;
      this.current_claim_type='reallocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else if(searchs == 'search'){
      console.log('++++++++++++++++');
        this.realloc_pages=page;
        this.current_claim_type='reallocated';
        this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.reassigned_sorting_name,this.sortByAsc,null,this.reassignedClaimsFind.value,null,this.search).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
    }else{
      this.realloc_pages=page;
      this.current_claim_type='reallocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.reassigned_sorting_name,this.sortByAsc,null,null,null,this.search).subscribe(
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
      // console.log("Get",this.current_claim_type);
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
      }
	  else if(type=='reallocated')
      {
        page=this.realloc_pages;
      }

    }
  }
  console.log(type);

this.tab_load=true;

/* if(type=='allocated' ){
  if(sorting_name == 'null' && searchs != 'search'){
    console.log('middle');
    this.alloc_pages=page;
      this.current_claim_type='allocated';
    this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }else if(searchs == 'search'){
      console.log('middle');
       this.alloc_pages=page;
      this.current_claim_type='allocated';
      this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.sorting_name,this.sortByAsc,this.assignedClaimsFind.value,null,null,this.search).subscribe(
        data  => this.form_table(data,type,form_type),
        error => this.handleError(error)
      );
    }else if(sorting_name != 'null'){
      console.log('last');
     this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.sorting_name,this.sortByAsc,null,null,null,search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }
}else if(type=='reallocated'){
  console.log('com'+type);
  if(sorting_name == 'null' && searchs != 'search'){
    console.log('first');
    this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }else if(searchs == 'search'){
    console.log('-----------');
       this.alloc_pages=page;
    this.current_claim_type='reallocated';
    this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.reassigned_sorting_name,this.sortByAsc,null,this.reassignedClaimsFind.value,null,this.search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }else if(sorting_name != 'null'){
    console.log('second');
     this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.reassigned_sorting_name,this.sortByAsc,null,null,null,this.search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }
}else if(type=='completed'){
  console.log('com'+type);
  if(sorting_name == 'null'  && searchs != 'search'){
    this.comp_pages=page;
    this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,sorting_name,sorting_method,null,null,null,search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }else if(searchs == 'search'){
       this.comp_pages=page;
    this.current_claim_type='reallocated';
    this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,this.closedClaimsFind.value,this.search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }else if(sorting_name != 'null'){
    this.comp_pages=page;
     this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,sort_data,sort_type,this.closed_sorting_name,this.sortByAsc,null,null,null,this.search).subscribe(
      data  => this.form_table(data,type,form_type),
      error => this.handleError(error)
    );
  }
} */


}

selected_status_code=[];
selected_sub_status_code=[];
//Assign Status codes
public assign_status_codes(data)
{
this.claim_status_codes=data.status;
this.claim_sub_status_codes=data.sub_status;
}

//Change values of substatus
public change_sub_status_code($event)
{
  this.selected_status_code=$event.target.value;
   this.selected_sub_status_code=this.claim_sub_status_codes[$event.target.value];
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
this.getclaim_details(1,'all',null,null,'null','null',null,null,null,null);
}

//Assign Table data and `total values
public assign_page_data(data)
{
  this.workorder_table=data.data;
//  console.log(this.workorder_table);
  this.total=data.total;
}



searchData:string;
//Search filter function
public sort_data(data)
{
  this.getclaim_details(1,'wo',data,'searchFilter','null','null',null,null,null,null);
  this.searchData=data;
  // To reset the checklist
  this.check_all[1]=false;
  this.selected_claim_nos=[];

  //console.log(this.searchData);
}
public sort_table(data)
{
this.getclaim_details(1,'wo',data,'filters','null','null',null,null,null,null);
}

public export_files(type)
{
  let filter='all claims';
  let s_code='adjustment';

  this.Jarwis.fetch_followup_export_data(filter,s_code,this.setus.getId()).subscribe(
    data  => this.export_handler.sort_export_data(data,type,'claim'),
    error => this.error_handler(error)
    );
}

public handleError(error)
{
  console.log(error);
}

//Open and Close Modal
open(content) {
  this.modalService.open(content, { centered: true ,windowClass:'custom-class'}).result.then((result) => {
    this.closeResult = `${result}`;
  }, (reason) => {
    this.closeResult = `${this.getDismissReason()}`;
  });
}

private getDismissReason() {
  this.close_clear_data();
  }

//Managing Values displayed in Modal
claim_clicked : string[];
claim_related : string[];
process_notes:string[];
claim_notes:string[];
qc_notes:string[];
client_notes:string[];
line_data=[];
toal:number;
claim_note;
assigned_to;
created_at;
public claim_no;
public claimslection(claim)
{
  console.log(claim);
  this.claim_no = claim.claim_no;
  this.claim_note = claim.claim_note;
  console.log(this.claim_note);
  this.assigned_to = claim.assigned_to;
  this.created_at = claim.created_at;
  this.loading=true;
  this.get_line_items(claim);
  this.check_reassign_alloc(claim);
  this.clear_refer();
  this.claim_clicked=claim;
  let length=this.workorder_table.length;
  this.claim_related=[];
  this.get_related(claim);
  // for(let i=0;i<this.workorder_table.length;i++)
  // {
  //   let related_length=this.claim_related.length;
  //   length= length-1;
  //   if(related_length<3)
  //   {
  //     if(this.workorder_table[length]['acct_no'] == claim.acct_no && this.workorder_table[length]['claim_no'] != claim.claim_no )
  //     {
  //      this.claim_related.push(this.workorder_table[length]);
  //     }
  //   }
  // }

  // console.log("Related",this.claim_related,this.workorder_table)
  this.send_calim_det('footer_data');
  this.getnotes(this.claim_clicked);
  this.check_reassign_alloc(this.claim_clicked);
  //this.processNotesDelete(this.claim_no);
}

processNotesDelete(data){
  this.Jarwis.followup_process_notes_delete(data, this.setus.getId()).subscribe(
    data  => this.handleResponseProcess(data),
    error => this.handleError(error)
  );
}

handleResponseProcess(data){
  this.getnotes(this.claim_clicked);
}


get_related(claim)
{
  this.Jarwis.get_related_calims(claim,'followup',this.setus.getId()).subscribe(
    data  => this.list_related(data),
    error => console.log(error)
    );this.Jarwis
}

list_related(claims)
{
    this.claim_related = claims.data;
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

claim_type;
public claim_tab_name(claim_type){
  this.claim_type = claim_type;
  // alert('claim_no1 ' + this.claim_type);
}

public referclaim(claim)
{


  claim = claim.claim;

  this.claim_nos = claim.claim_no;

  this.claim_status = claim.claim_Status;
  this.Jarwis.get_claimno(this.claim_nos, this.setus.getId(), this.claim_status, this.type).subscribe(
    data  => this.handleClaimNo(data),
    error => this.handleError(error)
  );


  if(this.refer_claim_no.indexOf(claim['claim_no']) < 0 )
  {
    this.refer_claim_det.push(claim);
    this.refer_claim_no.push(claim['claim_no']);

    // console.log("Into ref",claim)
    this.Jarwis.getnotes(claim).subscribe(
      data  => this.refer_notes(data,claim.claim_no),
      error => console.log("THeis2",error)
    );
  }
  else
  {
  this.selected_tab(claim['claim_no']);
  }
   this.send_calim_det('footer_data');
}

  assigned_data;

public handleClaimNo(data){
  this.assigned_data = data.claim_count;
  this.refer_claim(this.assigned_data);
}

refer_claim(assigned_data){

  //alert('claim_no');

    if(assigned_data == true ){
        this.refer_claim_editable = 'true';
     console.log(this.refer_claim_editable)
    // alert('claim_no1');
    }else if(assigned_data == false ){
      this.refer_claim_editable = 'false';
      console.log(this.refer_claim_editable);
     // alert('claim_no2');
    }
}

//Display Reference Notes
public refer_notes(data,claimno)
{
  // this.get_line_items(this.claim_clicked);

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

public update_refer_notes(data,type,claimno)
{
  // this.get_line_items(claimno);
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

    this.refer_claim_notes[claimno]=data.data;

    console.log(this.refer_claim_notes[claimno]);
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
this.send_calim_det('followup');
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
  this.send_calim_det('followup');
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

//Get Notes
public getnotes(claim)
{
  this.process_notes=[];
  this.claim_notes=[];
  this.qc_notes=[];
  this.client_notes=[];
  let type='All';

  // console.log("Getnot",claim)
  this.Jarwis.getnotes(claim).subscribe(
    data  => this.display_notes(data,type),
    error => this.handleError(error)
  );
}

//Update Process Notes
//Update Displayed Notes
  import_claim_note;
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
          console.log(this.claim_notes);
        }
    }
    this.loading=false;
    this.processNotes.reset();
    this.claimNotes.reset();
  }
}


//Save Notes

note_refresh(){
  this.process_notes_data_list =[];
  this.claim_notes_data_list =[];
}


public process_notes_data_list =[];
public process_notes_data =[];

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
    // this.Jarwis.claim_note(this.setus.getId(),this.claimNotes.value['claim_notes'],claim_id,'claim_create').subscribe(
    //   data  => this.display_notes(data,type),
    //   error => this.handleError(error)
    //   );

    if(this.editnote_value!=null || this.editnote_value!=undefined){
      this.claimNotes.value['claim_notes'] = this.editnote_value;
    }
    this.request_monitor=0;
    this.claim_notes_data.push({notes:this.claimNotes.value['claim_notes'],id:claim_id['claim_no']});
    this.claim_notes_data_list.push(claim_id['claim_no']);

    //console.log("Dta List",this.claim_notes_data_list);
    this.notes_hadler.set_notes(this.setus.getId(),this.claimNotes.value['claim_notes'],claim_id,'claim_create');
    this.send_calim_det('footer_data');
  }
}



//Edit Notes
edit_noteid:number;
editnote_value: string[];
initial_edit:boolean=false;
proess_initial_edit;
public editnotes(type,value,id)
{
  //For initial Edit of Claim notes
  if(type=='claim_notes_init')
  {
    let claim_data=this.claim_notes_data.find(x => x.id == id['claim_no']);
    this.editnote_value=claim_data.notes;
    this.edit_noteid=id;
    this.initial_edit=true;
  }else if(type=='process_notes_init')
  { let process_data=this.process_notes_data.find(x => x.id == id['claim_no']);
    this.editnote_value=process_data.notes;
    this.edit_noteid=id;
    this.proess_initial_edit=true;
  }
  else{
    console.log(type);
    console.log(value);
    console.log(id);
    this.editnote_value=value;
    this.edit_noteid=id;

    if(type=='claimnotes'){
      this.claimNotes.patchValue({
        claim_notes: this.editnote_value,
      });
    } 
    this.initial_edit=false;
  }


}

public get_insurance(){
  this.Jarwis.get_insurance(this.setus.getId()).subscribe(
      data  => this.handleInsurance(data),
      error => this.handleError(error)
    );
}

option;
handleInsurance(data){
  this.option = data.claim_data;
}

//Update Notes
public updatenotes(type){
  if(this.initial_edit==true)
  {
    this.notes_hadler.set_notes(this.setus.getId(),this.claimNotes.value['claim_notes'],this.edit_noteid,'claim_create');
    this.claim_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.claimNotes.value['claim_notes'];
    // this.claim_notes_data[this.edit_noteid['claim_no']]=this.claimNotes.value['claim_notes'];
    this.initial_edit=false;
    this.send_calim_det('footer_data');
  }/*else if(this.proess_initial_edit==true){
    this.notes_hadler.set_notesest(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'claim_create');
    this.process_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes=this.processNotes.value['processnotes'];
    this.initial_edit=false;
    this.send_calim_det('footer_data');
  } */
  else{
  if(type=='processnotes')
  {
    this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'processupdate', 'audit-closed').subscribe(
      data  => this.display_notes(data,type),
      error => this.handleError(error)
    );

    // let claim_active;
    // this.Jarwis.check_edit_val(claim_active,'followup').subscribe(
    //   data  => {
    //   this.set_note_edit_validity(data);
    //     // console.log("Note _edit",this.note_edit_val);
    //     if(this.note_edit_val != undefined)
    //     {
    //       // console.log("Inside",this.processNotes.value,this.edit_noteid);
    //      this.Jarwis.process_note(this.setus.getId(),this.processNotes.value['processnotes'],this.edit_noteid,'processupdate', 'followup').subscribe(
    //       data  => this.display_notes(data,type),
    //       error => this.handleError(error)
    //      );
    //     }
    //     else
    //     {
    //       this.toastr.errorToastr('Notes cannot be Updated.', 'Claim Processed.');
    //     }
    // },
    //   error => this.handleError(error)
    // );
  }
  else if(type == 'claimnotes')
  {
    let claim_active;
    let claim_id = [];

    // if(this.main_tab == true)
    // {
    //   claim_active=this.claim_clicked;
    // }
    // else{
    //   claim_active=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
    // }
    // console.log("cc",claim_active ,);
    // this.check_note_edit_validity(this.claim_clicked);

    if(this.main_tab == true)
        {
          claim_active=this.claim_clicked;
          claim_id = this.claim_clicked;
          console.log(claim_active);
        }
        else{
          console.log(this.refer_claim_det);
          claim_active=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
          console.log(claim_active);
          claim_id = this.claim_clicked; 
        }

    this.Jarwis.check_edit_val(claim_active,'followup').subscribe(
      data  => {
        // console.log("ched",data);
      this.set_note_edit_validity(data);
        console.log("Note _edit",this.note_edit_val);
        if(this.note_edit_val != undefined)
        {
          if(this.editnote_value !=null || this.editnote_value!=undefined){
            this.claimNotes.value['claim_notes'] = this.editnote_value;
          }
          // console.log("Inside",this.claimNotes.value,this.edit_noteid);
          this.Jarwis.claim_note(this.setus.getId(),this.claimNotes.value['claim_notes'],claim_id,'claimupdate').subscribe(
            data  => this.display_notes(data,type),
            error => this.handleError(error)
          );
          this.notes_hadler.set_notes(this.setus.getId(),this.claimNotes.value['claim_notes'],claim_id,'claimupdate');
    this.send_calim_det('footer_data');
        }
        else
        {
          this.toastr.warningToastr('Claim notes cannot be Updated.');
        }
    },

      error => this.handleError(error)
    );


  }

  }
  this.editnote_value=null;

  }


  public close_clear_data()
  {
    this.editnote_value=null;
  }


//Clear ProcessNote
public clear_notes()
{
  this.editnote_value=null;
  this.processNotes.reset();
}

//Send Claim Value to Followup-Template Component on Opening Template
// active_sent_claim:string[];
public send_calim_det(type)
{
  console.log(type);
  
  if(this.main_tab==true)
  {
    console.log(this.main_tab);
    if(type == 'followup')
    {
       console.log(this.claim_clicked['claim_no']);
      this.follow.setvalue(this.claim_clicked['claim_no']);
    }
    else{
      this.notes_hadler.selected_tab(this.claim_clicked['claim_no']);
      this.notes_hadler.set_claim_details(this.claim_clicked);
      this.claim_active=this.claim_clicked;
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
      console.log(claim_detials);
      this.notes_hadler.set_claim_details(claim_detials);
      this.claim_active=this.active_claim;
    }

  }

 }

 claimid;
 active_data;
 followup_data;
 followup_question_data;

 public get_followup_details()
  {
    let claim=this.follow.getvalue();
    if(this.claimid.includes(claim) )
    {
      let id=this.claimid.indexOf(claim);
      this.active_claim=this.followup_data[id];
      this.active_data=this.followup_question_data[id];
      console.log('1'+ this.active_data);
      }
      else{
        this.Jarwis.get_followup(claim).subscribe(
          data  => this.assign_data(data,claim),
          error => this.handleError(error)
          );
        }
      }

  public assign_data(data,claim)
  {
    console.log(data.data.data);
    this.claimid.push(claim);
    this.followup_data.push( data.data.data);
    this.followup_question_data.push(data.data.content);
    this.active_claim=data.data.data;
    this.active_data=data.data.content;
    console.log('2' +this.active_data);
  }

 public collect_response(data)
 {
 console.log("collect",data);

   if(this.main_tab == true)
   {
    this.check_note_edit_validity(this.claim_clicked);
   }
   else{

    let claim_detials=this.refer_claim_det.find(x => x.claim_no == this.active_claim);
    this.check_note_edit_validity(claim_detials);
   }




   this.display_notes(data,'claimnotes');
   this.getclaim_details(1,'refresh','null','null','null','null',null,null,null,null);

  //  console.log("Dta List Brf",this.claim_notes_data_list);

   let index =  this.claim_notes_data_list.indexOf(this.active_claim);
   this.claim_notes_data_list.splice(index, 1);

   let index1 =  this.process_notes_data_list.indexOf(this.active_claim);
   this.process_notes_data_list.splice(index1, 1);
   //console.log("Dta List AFTT",this.claim_notes_data_list);
  }

public get_line_items(claim)
{
  // console.log("Get line",claim);
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
//error_handler
error_handler(error)
{
  //console.log(error)
  if(error.error.exception == "Illuminate\Database\QueryException"){
    this.toastr.warningToastr("File can not be Deleted",'Foreign key Constraint');
  }
  else{
    this.toastr.errorToastr(error.error.exception, "Error!");
  }
}

line_item_data=[];
assign_line_data(data)
{
  this.line_item_data.push(data.data);
  this.line_data=data.data;
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
  let mod_type='followup';

  this.Jarwis.reassign_calim(this.reassign_claim,this.setus.getId(),mod_type).subscribe(
    data  => this.after_reassign(data,this.reassign_claim['claim_no']) ,
    error => this.handleError(error)
  );

}
}

reassign_allocation:boolean=true;
after_reassign(data,claim)
{
  // console.log(data,claim);
  this.curr_reassigned_claims.push(claim);
  // this.getclaim_details(this.alloc_pages,'allocated');
  this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null);
  this.reassign_allocation=false;
}

check_reassign_alloc(claim)
{

  console.log("Here REassign",claim);
  if(this.setus.get_role_id() == '1' && claim['followup_work_order'] != null)
  {
    let already_re=this.curr_reassigned_claims.indexOf(claim.claim_no);
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
  console.log("Check",claim);
  this.Jarwis.check_edit_val(claim,'followup').subscribe(
    data  => this.set_note_edit_validity(data),
    error => this.handleError(error)
  );

}

note_edit_val:number;
set_note_edit_validity(data)
{
  console.log("Validity",data);
  if(data.edit_val == true)
  {
    // console.log(data.note_id['id']);
    this.note_edit_val = data.note_id['id'];
    console.log(this.note_edit_val);
  }
  else
  {
    this.note_edit_val=undefined;
  }
  console.log(this.note_edit_val);
}

reload_data()
{
  this.loading=true;
  if(this.modalService.hasOpenModals() == false)
  {
    this.getclaim_details(this.pages,'allocated',null,null,'null','null',null,null,null,null);

    for(let i=0;i<this.assigned_claims.length;i++)
    {
      let claim=this.assigned_claims[i]['claim_no'];
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind,1);
      this.selected_claim_nos.splice(ind,1);

    }

    let page_count=15;

    this.pages=1;
    this.Jarwis.get_table_page(null,this.pages,page_count,null,'null','null','null','null').subscribe(
      data  => this.assign_page_data(data),
      error => this.handleError(error)
    );

    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });

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

//   console.log(this.touch_count);

// }

claim_check(count)
{
  if(Number(count)>this.touch_count)
  {
    this.toastr.errorToastr('Claim Exceeds '+ this.touch_count+ ' Touches', 'Exceeds!!');

  }else if(Number(count) == (this.touch_count -1))
  {
    this.toastr.warningToastr('Claim Nearing '+ this.touch_count+ ' Touches.', 'Warning!')
  }
  else if(Number(count) == this.touch_count)
  {
    this.toastr.errorToastr('Claim Reaches '+ this.touch_count+ ' Touches', 'Count Limit!!');
  }
}



chart_val={
  "chart": {
      // "caption": "Split of Top Products Sold",
      // "subCaption": "Last Quarter",
      "basefontsize": "10",
      "pieFillAlpha": "70",
      "pieBorderThickness": "2",
      "hoverFillColor": "#cccccc",
      "pieBorderColor": "#ffffff",
      "showPercentInTooltip": "0",
      "numberPrefix": "$",
      "plotTooltext": "$label, $$valueK, $percentValue",
      "theme": "fusion"
  },
  "category": [
      {
          "label": "My Report",
          "color": "#ffffff",
          "value": "150",
          "category": [
              {
                  "label": "0-30",
                  "color": "#f8bd19",
                  "category": [
                      {
                          "label": "Breads",
                          "color": "#f8bd19",
                          "value": "11.1"
                      },
                      {
                          "label": "Juice",
                          "color": "#f8bd19",
                          "value": "27.75"
                      },
                      {
                          "label": "Noodles",
                          "color": "#f8bd19",
                          "value": "9.99"
                      },
                      {
                          "label": "Seafood",
                          "color": "#f8bd19",
                          "value": "6.66"
                      }
                  ]
              },
              {
                  "label": "31-60",
                  "color": "#e44a00",
                  "category": [
                      {
                          "label": "Sun Glasses",
                          "color": "#e44a00",
                          "value": "10.08"
                      },
                      {
                          "label": "Clothing",
                          "color": "#e44a00",
                          "value": "18.9"
                      },
                      {
                          "label": "Handbags",
                          "color": "#e44a00",
                          "value": "6.3"
                      },
                      {
                          "label": "Shoes",
                          "color": "#e44a00",
                          "value": "6.72"
                      }
                  ]
              },
              {
                  "label": "61-90",
                  "color": "#008ee4",
                  "category": [
                      {
                          "label": "Bath &{br}Grooming",
                          "color": "#008ee4",
                          "value": "9.45"
                      },
                      {
                          "label": "Feeding",
                          "color": "#008ee4",
                          "value": "6.3"
                      },
                      {
                          "label": "Diapers",
                          "color": "#008ee4",
                          "value": "6.75"
                      }
                  ]
              },
              {
                  "label": "120+",
                  "color": "#33bdda",
                  "category": [
                      {
                          "label": "Laptops",
                          "color": "#33bdda",
                          "value": "8.1"
                      },
                      {
                          "label": "Televisions",
                          "color": "#33bdda",
                          "value": "10.5"
                      },
                      {
                          "label": "SmartPhones",
                          "color": "#33bdda",
                          "value": "11.4"
                      }
                  ]
              }
          ]
      }
  ]
}

user_role:Number=0;
class_change=[];
class_change_tab=[];
user_role_maintainer()
{
let role_id=Number(this.setus.get_role_id());

//console.log("User Role",role_id);

  if(role_id == 5 || role_id == 3 || role_id == 2)
  {
    this.user_role=2;
    this.class_change['tab1']='active';
    this.class_change['tab2']='';

    this.class_change_tab['tab1']='tab-pane active';
    this.class_change_tab['tab2']='tab-pane'

  }
  else if(role_id == 1)
  {
    this.user_role=1;

    this.class_change['tab1']='active';
    this.class_change['tab2']='';

    this.class_change_tab['tab1']='tab-pane active';
    this.class_change_tab['tab2']='tab-pane'

    this.get_month_details();
  }



}

weeks=[];
days=[];

get_month_details()
{
  this.Jarwis.get_month_details().subscribe(
    data  => this.set_month_det(data),
    error => this.handleError(error)
  );
}
col_span=[];
set_month_det(data)
{
  // console.log(data.working,"WEE",data.weeks);
this.weeks=data.weeks;
this.days=data.working;

//For SATURDAY
let week_length=[];
data.weeks.forEach(element => {

  if(element.length == undefined)
  {
    week_length.push(1);
  }
  else{
    week_length.push(element.length);
  }

});
this.col_span=week_length;
// console.log("len",this.col_span)
this.get_prod_qual();
}



get_prod_qual()
{
  this.Jarwis.get_prod_qual(this.setus.getId(),this.days).subscribe(
    data  => this.assign_prod_qual(data),
    error => this.handleError(error)
  );
}

assigned_target=[];
achieved_target=[];
achi_targ_per=[];
assign_prod_qual(data)
{
  //console.log('o/p',data);
this.assigned_target=data.assigned;
this.achieved_target = data.worked;
this.achi_targ_per = data.work_per;

}

public check_all: Array<any> =[];
public selected_claims=[];
public selected_claim_nos=[];

public check_all_assign(page,event)
{
if( event.target.checked == true)
{
  this.check_all[page]==true;
}
else{
  this.check_all[page]==false;
}
}
//Selected Claim Sorting
public selected(event,claim,index)
{

  if(claim == 'all' && event.target.checked == true )
  {
    let assigned_claims = this.assigned_claims;

    let claim_nos=this.selected_claim_nos;
    let claim_data= this.selected_claims;

    assigned_claims.forEach(function (value) {
      let keys = value;
      if(!claim_nos.includes(keys['claim_no']))
      {
      claim_nos.push(keys['claim_no']);
      claim_data.push(keys);
      }
      });
      this.selected_claim_nos=claim_nos;
      this.selected_claims=claim_data;
      console.log(this.selected_claims);
  }
  else if(claim == 'all' && event.target.checked == false)
  {

    for(let i=0;i<this.assigned_claims.length;i++)
    {
      let claim=this.assigned_claims[i]['claim_no'];
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind,1);
      this.selected_claim_nos.splice(ind,1);

    }

    // this.selected_claims=[];
    // this.selected_claim_nos=[];
  }
else if(event.target.checked == true)
{
this.selected_claims.push(this.table_datas[index]);
this.selected_claim_nos.push(claim);
}
else if(event.target.checked == false)
{
  let ind = this.selected_claim_nos.indexOf(claim);
  this.selected_claims.splice(ind,1);
  this.selected_claim_nos.splice(ind,1);

}
}


public reassigned_selected(event,claim,index)
{

  if(claim == 'all' && event.target.checked == true )
  {
    let reassigned_claims_data = this.reassigned_claims_data;

    let claim_nos=this.selected_claim_nos;
    let claim_data= this.selected_claims;

    reassigned_claims_data.forEach(function (value) {
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

    for(let i=0;i<this.reassigned_claims_data.length;i++)
    {
      let claim=this.reassigned_claims_data[i]['claim_no'];
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind,1);
      this.selected_claim_nos.splice(ind,1);

    }

    // this.selected_claims=[];
    // this.selected_claim_nos=[];
  }
else if(event.target.checked == true)
{
this.selected_claims.push(this.reassigned_claims_data[index]);
this.selected_claim_nos.push(claim);
}
else if(event.target.checked == false)
{
  let ind = this.selected_claim_nos.indexOf(claim);
  this.selected_claims.splice(ind,1);
  this.selected_claim_nos.splice(ind,1);

}
}


public completed_selected(event,claim,index)
{

  if(claim == 'all' && event.target.checked == true )
  {
    let completed_claims_data = this.completed_claims_data;

    let claim_nos=this.selected_claim_nos;
    let claim_data= this.selected_claims;

    completed_claims_data.forEach(function (value) {
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

    for(let i=0;i<this.completed_claims_data.length;i++)
    {
      let claim=this.completed_claims_data[i]['claim_no'];
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind,1);
      this.selected_claim_nos.splice(ind,1);

    }

    // this.selected_claims=[];
    // this.selected_claim_nos=[];
  }
else if(event.target.checked == true)
{
this.selected_claims.push(this.table_datas[index]);
this.selected_claim_nos.push(claim);
}
else if(event.target.checked == false)
{
  let ind = this.selected_claim_nos.indexOf(claim);
  this.selected_claims.splice(ind,1);
  this.selected_claim_nos.splice(ind,1);

}
}

summary_total_assigned:Number=0;
setSummaryInfo(data){
	this.summary_total_assigned = data.summary.total_assigned;

}

getSummary(){

	 // this.Jarwis.getSummaryDetails(this.setus.getId()).subscribe(
  //     data  => this.setSummaryInfo(data),
  //     error => this.handleError(error)
  //   );
}

  public get_statuscodes()
  {
    this.Jarwis.get_status_codes(this.setus.getId(),'all').subscribe(
      data  => this.process_codes(data)
    );
  }
  public process_codes(data:any)
  {
    console.log(data);
    let status_option=[];
    this.status_codes_data=data.status;
    this.sub_status_codes_data=data.sub_status;
    for(let i=0;i<this.status_codes_data.length;i++)
    {
      if(this.status_codes_data[i]['status']==1)
      {
        // alert(this.status_codes_data[i]['status_code']);
        status_option.push({id: this.status_codes_data[i]['id'], description: this.status_codes_data[i]['status_code'] +'-'+ this.status_codes_data[i]['description'] } );
      }
    }
    this.status_options=status_option;
  }
  public assigned_status_code_changed(event:any)
  {
    if(event.value!=undefined)
    {
      let sub_status=this.sub_status_codes_data[event.value.id];
      let sub_status_option=[];
      console.log('sub_status_option');
      if(sub_status == undefined || sub_status =='' )
      {
        this.sub_options=[];
        this.assignedClaimsFind.patchValue({
          sub_status_code: ''
        });
      }
      else {
        for(let i=0;i<sub_status.length;i++)
        {
          if(sub_status[i]['status']==1)
          {
            sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });
          }
          this.sub_options=sub_status_option;
          if(this.sub_options.length !=0)
          {
            this.assignedClaimsFind.patchValue({
              sub_status_code: {id:this.sub_options[0]['id'],description:this.sub_options[0]['description']}
            });
          }
          else{
            this.assignedClaimsFind.patchValue({
              sub_status_code: ""
            });
          }
        }
      }
      // this.modified_stats.push(event);
    }
  }
  public reassigned_status_code_changed(event:any)
  {
    if(event.value!=undefined)
    {
      let sub_status=this.sub_status_codes_data[event.value.id];
      let sub_status_option=[];
      console.log('sub_status_option');
      if(sub_status == undefined || sub_status =='' )
      {
        this.sub_options=[];
        this.reassignedClaimsFind.patchValue({
          sub_status_code: ''
        });
      }
      else {
        for(let i=0;i<sub_status.length;i++)
        {
          if(sub_status[i]['status']==1)
          {
            sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });
          }
          this.sub_options=sub_status_option;
          if(this.sub_options.length !=0)
          {
            this.reassignedClaimsFind.patchValue({
              sub_status_code: {id:this.sub_options[0]['id'],description:this.sub_options[0]['description']}
            });
          }
          else{
            this.reassignedClaimsFind.patchValue({
              sub_status_code: ""
            });
          }
        }
      }
      // this.modified_stats.push(event);
    }
  }
  public closed_status_code_changed(event:any)
  {
    if(event.value!=undefined)
    {
      let sub_status=this.sub_status_codes_data[event.value.id];
      let sub_status_option=[];
      console.log('sub_status_option');
      if(sub_status == undefined || sub_status =='' )
      {
        this.sub_options=[];
        this.closedClaimsFind.patchValue({
          sub_status_code: ''
        });
      }
      else {
        for(let i=0;i<sub_status.length;i++)
        {
          if(sub_status[i]['status']==1)
          {
            sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });
          }
          this.sub_options=sub_status_option;
          if(this.sub_options.length !=0)
          {
            this.closedClaimsFind.patchValue({
              sub_status_code: {id:this.sub_options[0]['id'],description:this.sub_options[0]['description']}
            });
          }
          else{
            this.closedClaimsFind.patchValue({
              sub_status_code: ""
            });
          }
        }
      }
      // this.modified_stats.push(event);
    }
  }

  //Configuration of Dropdown Search
 config = {
  displayKey:"description",
  search:true,
  limitTo: 1000,
  result:'single'
 }

  ngOnInit() {
    //this.get_insurance();
    this.user_role_maintainer();
    this.getSummary();
    this.getclaim_details(1,'wo','null','null','null','null',null,null,null,null);
    this.get_statuscodes();

    this.assignedClaimsFind = this.formBuilder.group({
      dos: [],
      age_filter: [],
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

    this.reassignedClaimsFind = this.formBuilder.group({
      dos: [],
      age_filter: [],
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
      age_filter: [],
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

        this.subscription=this.notify_service.fetch_touch_limit().subscribe(message => {
          this.touch_count = message });

          const debouncetime = pipe(debounceTime(700));
    this.search_data.valueChanges.pipe(debouncetime)
    .subscribe( result => this.sort_data(result)
    );
    this.filter_option.valueChanges
    .subscribe( result => this.sort_table(result)
    );
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
         //this.subscription.unsubscribe();
          this.response_data.unsubscribe();
          this.update_monitor.unsubscribe();
          this.subscription.unsubscribe();
        }
		//Create Work Order




// public reassign(){
//      this.Jarwis.getdata(this.selected_claim_nos,this.setus.getId()).subscribe(
//     data  => this.reassigned_claims(data),
//     error => this.handleError(error),
//    )}
//     reassigned_claims(data){
//       if(data.status =='success'){
//         console.log(data.status);
//        this.toastr.successToastr('Assigned Successfully.','Successfully');
//       }
//       else{
//        this.toastr.errorToastr( 'Some thing went wrong.');
//       }
//     }
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

 confirm_box(confirmation)
{
    this.Jarwis.getdata(this.selected_claim_nos,this.setus.getId(),confirmation).subscribe(
      data  => this.reassigned_claims(data),
      error => this.handleError(error)

    );
}
reassigned_claims(data){
  if(this.selected_claim_nos.length==0){
      this.toastr.errorToastr('please select Claims');
    }
    for(let i=0;i<this.selected_claim_nos.length;i++)
    {
      var assigned_to=this.selected_claim_nos[i]['assigned_to'];
      var assigned_by=this.selected_claim_nos[i]['assigned_by'];
    }
      if(data.assigned_to == data.assigned_by)
      {
        this.toastr.errorToastr('Unable to Reassign');
        this.selected_claim_nos=[];

      }
      else{
        let page_count=15;
        // console.log("ip",type);
        let form_type=null;
        let type='allocated';
        let page = this.alloc_pages;
              this.tab_load=true;
        this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,null,null,null,null,null,null,null,null).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
        this.toastr.successToastr( 'Reassigned Successfully');
      }
    }


confirm_boxes(reassign)
{
    this.Jarwis.getdata(this.selected_claim_nos,this.setus.getId(),reassign).subscribe(
      data  => this.reassigned_claims_datas(data),
      error => this.handleError(error)

    );
}
reassigned_claims_datas(data){
  if(this.selected_claim_nos.length==0){
      this.toastr.errorToastr('please select Claims');
    }
    for(let i=0;i<this.selected_claim_nos.length;i++)
    {
      var assigned_to=this.selected_claim_nos[i]['assigned_to'];
      var assigned_by=this.selected_claim_nos[i]['assigned_by'];
    }
      if(data.assigned_to == data.assigned_by)
      {
        this.toastr.errorToastr('Unable to Reassign');
        this.selected_claim_nos=[];

      }
      else{
        let page_count=15;
        // console.log("ip",type);
        let form_type=null;
        let type='reallocated';
        let page = this.realloc_pages;
              this.tab_load=true;
        // this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,null,null,'null','null',null,null,null,null).subscribe(
        //   data  => this.form_table(data,type,form_type),
        //   error => this.handleError(error)
        // );

        this.Jarwis.getclaim_details(this.setus.getId(),page,page_count,type,null,null,null,null,null,null,null,null).subscribe(
          data  => this.form_table(data,type,form_type),
          error => this.handleError(error)
        );
        this.toastr.successToastr( 'Reassigned Successfully');
      }
    }


  cancel_claims(){
    this.selected_claim_nos=[];
  }

  public sort_details(type) {
    if(type=='id'){
            if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.allocated_claims.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
        this.completed_claims.sort((a,b) => a.acct_no.localeCompare(b.acct_no));
        this.reallocated_claims.sort((a,b) =>a.acct_no.localeCompare(b.acct_no));
      } else {
        this.sortByAsc = true;
        this.allocated_claims.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
        this.completed_claims.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
        this.reallocated_claims.sort((a,b) => b.acct_no.localeCompare(a.acct_no));
     }
    }
    else if(type=='claims'){
      if(this.sortByAsc == true) {
        this.sortByAsc = false;
        this.allocated_claims.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
        this.completed_claims.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
        this.reallocated_claims.sort((a,b) => a.claim_no.localeCompare(b.claim_no));
      } else {
        this.sortByAsc = true;
        this.allocated_claims.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
        this.completed_claims.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
        this.reallocated_claims.sort((a,b) => b.claim_no.localeCompare(a.claim_no));
     }
    }
    else if(type=='patient'){
      if(this.sortByAsc == true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
        this.completed_claims.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
        this.reallocated_claims.sort((a,b) => a.patient_name.localeCompare(b.patient_name));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
        this.completed_claims.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
        this.reallocated_claims.sort((a,b) => b.patient_name.localeCompare(a.patient_name));
      }
    }
    else if(type=='insurance'){
      if(this.sortByAsc == true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
        this.completed_claims.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
        this.reallocated_claims.sort((a,b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
        this.completed_claims.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
        this.reallocated_claims.sort((a,b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
      }
    }
    else if(type=='bill'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
        this.completed_claims.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
        this.reallocated_claims.sort((a,b) => a.total_charges.localeCompare(b.total_charges));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
        this.completed_claims.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
        this.reallocated_claims.sort((a,b) => b.total_charges.localeCompare(a.total_charges));
      }
    }
    else if(type=='due'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
        this.completed_claims.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
        this.reallocated_claims.sort((a,b) => a.total_ar.localeCompare(b.total_ar));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
        this.completed_claims.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
        this.reallocated_claims.sort((a,b) => b.total_ar.localeCompare(a.total_ar));
      }
    }
    else if(type=='status'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
        this.completed_claims.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
        this.reallocated_claims.sort((a,b) => a.claim_Status.localeCompare(b.claim_Status));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.claim_Status.localeCompare(a.claim_Status));
        this.completed_claims.sort((a,b) => b.claim_Status.localeCompare(a.claim_Status));
        this.reallocated_claims.sort((a,b) => b.claim_Status.localeCompare(a.claim_Status));
      }
    }
    else if(type=='dos'){
      if(this.sortByAsc==true){
        this.sortByAsc=false;
        this.allocated_claims.sort((a,b) => a.dos.localeCompare(b.dos));
        this.completed_claims.sort((a,b) => a.dos.localeCompare(b.dos));
        this.reallocated_claims.sort((a,b) => a.dos.localeCompare(b.dos));
      }
      else{
        this.sortByAsc=true;
        this.allocated_claims.sort((a,b) => b.dos.localeCompare(a.dos));
        this.completed_claims.sort((a,b) => b.dos.localeCompare(a.dos));
        this.reallocated_claims.sort((a,b) => b.dos.localeCompare(a.dos));
      }
    }
    }


    // order_list(page:number,type,sort_data,sort_type) {
    //   let page_count=15;
    //   // console.log("ip",type);
    //   let form_type=null;
    //   this.alloc_pages=page;
    //   this.current_claim_type='allocated';

    //   if(this.sortByAsc==true){
    //     this.sortByAsc=false;
    //     this.Jarwis.getclaim_details_sort(this.setus.getId(),page,page_count,type,this.sortByAsc,sort_type).subscribe(
    //       data  => this.form_table(data,type,form_type),
    //       error => this.handleError(error)
    //     );
    //   }
    //   else{
    //     this.sortByAsc=true;
    //     this.Jarwis.getclaim_details_sort(this.setus.getId(),page,page_count,type,this.sortByAsc,sort_type).subscribe(
    //       data  => this.form_table(data,type,form_type),
    //       error => this.handleError(error)
    //     );
    //   }


    // }

    handleOrderList(data){

    }
    tooltipOptions= {
        'placement': 'right',
        'show-delay': '200',
        'tooltip-class': 'new-tooltip-class',
        'background-color': '#9ad9e4',
        'margin-top': '20px'
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

public searchClaims;

public export_excel_files(type, table_name)
{
  console.log(table_name);
 if(table_name == 'Assigned_claims'){
   this.searchClaims = this.assignedClaimsFind.value;
   console.log(this.searchClaims);
 }else if(table_name == 'Reassigned_claims'){
   this.searchClaims = this.reassignedClaimsFind.value;
 }else if(table_name == 'Closed_claims'){
   this.searchClaims = this.closedClaimsFind.value;
 }

 this.Jarwis.fetch_followup_claims_export_data(this.setus.getId(), table_name, this.search, this.searchClaims).subscribe(
    data  => this.export_handler.create_claim_export_excel(data),
    error => this.error_handler(error)
    );
}

public export_pdf_files(type, table_name)
{
  let filter='all claims';
  let s_code='adjustment';

  this.Jarwis.fetch_followup_claims_export_data_pdf(this.setus.getId(), table_name).subscribe(
    data  => this.export_handler.sort_export_data(data,type,'claim'),
    error => this.error_handler(error)
  );
}

export_Excel_handler(){

}


}
