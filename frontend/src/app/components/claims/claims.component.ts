import { Component, ViewChildren, ElementRef, QueryList, OnInit, ChangeDetectionStrategy, Input, EventEmitter, Output, OnChanges, ViewEncapsulation, AfterViewInit, OnDestroy } from '@angular/core';
import * as XLSX from 'xlsx';
import { SetUserService } from '../../Services/set-user.service';
import { JarwisService } from '../../Services/jarwis.service';
import { LoadingBarService } from '@ngx-loading-bar/core';
import * as FileSaver from 'file-saver';
import { NgbModal, ModalDismissReasons, NgbModalConfig } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, Validators, FormBuilder } from "@angular/forms";
import { FollowupService } from '../../Services/followup.service';
import { debounceTime } from 'rxjs/operators';
import { pipe } from 'rxjs/util/pipe';
import { ToastrManager } from 'ng6-toastr-notifications';
import { ExcelService } from '../../excel.service';
import { ExportFunctionsService } from '../../Services/export-functions.service';
import { NotifyService } from '../../Services/notify.service';
import { Subscription } from 'rxjs';
import { DatePipe } from '@angular/common';
import { WorkOrderAssign } from '../../models/work-order-assign.bar';
import { NgbDatepickerConfig, NgbCalendar, NgbDate, NgbDateStruct, NgbDateParserFormatter } from '@ng-bootstrap/ng-bootstrap';
import { forEach } from '@angular/router/src/utils/collection';
// import { NgbDateCustomParserFormatter} from '../../date_file';
import { NotesHandlerService } from '../../Services/notes-handler.service';
import * as moment from 'moment';
import { HttpClient, HttpHeaders, HttpResponse } from '@angular/common/http';

//import * as localization from 'moment/locale/fr';
//moment.locale('fr', localization);

@Component({
  selector: 'app-claims',
  templateUrl: './claims.component.html',
  styleUrls: ['./claims.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None
})



export class ClaimsComponent implements OnInit,OnDestroy,AfterViewInit {

  page = "";
  createWork = "";
  isDesc: boolean = false;
  column: string = "dos";
  associateCount: any = '';
  filter = '';
  assigned = "";
  reAssigned = "";
  status_list:any;
  select_date:any;
  all_select_date: any;
  resaaigned_select_date:any;
  closed_select_date: any;
  selectedAge = null;
  age_options:any = [{ "from_age": 0, "to_age": 30 },{ "from_age": 31, "to_age": 60 },{ "from_age": 61, "to_age": 90 },{ "from_age": 91, "to_age": 120 },{ "from_age": 121, "to_age": 180 },{ "from_age": 181, "to_age": 365 }];
  claim_statuses :any = ['Closed', 'Assigned', 'Auditing'];
  decimal_pattern = "^\[0-9]+(\.[0-9][0-9])\-\[0-9]+(\.[0-9][0-9])?$";
  isSelectedAll = false;
  public status_codes_data:Array<any> =[];
  public sub_status_codes_data:string[];
  public status_options;
  public sub_options;

  isValueSelected:boolean = false;
  results: any[] = [];
  searchResults: any[] = [];
  selected_val:any;

  @ViewChildren('pageRow') private pageRows: QueryList<ElementRef<HTMLTableRowElement>>;

  @ViewChildren("checkboxes") checkboxes: QueryList<ElementRef>;


  //selected: any;

  configure = {
    displayKey:"description",
    search:true,
    result:'single'
   }



  public filecount: any;
  public file_name = [];
  public data = null;
  public error = null;
  public fileupload = null;
  public newclaim = null;
  public duplicate = null;
  public mismatch = null;
  public claimno = null;
  public filenote = null;
  public claims_processed;
  new_claims: string[];
  duplicate_claims: string[];
  mismatch_claims: string[];
  mismatch_claim_nos: number;
  mismatch_claim_data: string[];
  mismatch_claim_numbers: string[];
  mismatch_claim_data_value: string[];
  mismatch_claim_data_mismatch: string[];
  mismatch_claim_number_sort: string[];
  new_claim_data: string[];
  file_upload: string[];
  input_data: string[];
  closeResult: string;
  old_value: string[];
  new_value: string[];
  fieldselect: string[];
  roles: string[] = [];
  importProcessed:any;
  datas: string[];
  mismatch_field_list: string[];
  mismatch_selected: string;
  @Input('data') table_datas_list: string[] = [];
  loading: boolean;
  field_name = [];
  sortByAsc: boolean = true;

  public editnote_value = null;

  formdata = new FormData();
  formGroup: FormGroup;
  search_data: FormControl = new FormControl();
  wo_search_data: FormControl = new FormControl();
  filter_option: FormControl = new FormControl();

  processNotes: FormGroup;
  claimNotes: FormGroup;
  workOrder: FormGroup;
  closedClaimsFind: FormGroup;
  createClaimsFind: FormGroup;
  allClaimsFind: FormGroup;
  workOrderFind: FormGroup;
  autoclose_claim: FormGroup;
  reassignedClaimsFind:FormGroup;
  // formGroup: FormGroup;
  submitted = false;
  modalform: FormGroup;
  get v() { return this.qcNotes.controls; }
  public tabdat = ['date', 'file_name', 'claims', 'newclaims', 'uploaded'
  ];

  myDate = new Date();

  subscription: Subscription;
  observalble: Subscription;
  response_data;

  name = 'Angular';

  selecteds: any;
  selectedClosed:any;
  selectedAll:any;
  selectedReasssign:any;
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

  isInvalidDate = (m: moment.Moment) => {
    return this.invalidDates.some(d => d.isSame(m, 'day'))
  }


  constructor(private formBuilder: FormBuilder,
    private Jarwis: JarwisService,
    private setus: SetUserService,
    private loadingBar: LoadingBarService,
    private modalService: NgbModal,
    private follow: FollowupService,
    public toastr: ToastrManager,
    private excelService: ExcelService,
    private export_handler: ExportFunctionsService,
    private config: NgbModalConfig,
    private notify_service: NotifyService,
    private datepipe: DatePipe,
    private date_config: NgbDatepickerConfig,
    private calendar: NgbCalendar,
    private notes_hadler: NotesHandlerService,
    public formatter: NgbDateParserFormatter,
  ) {
    //this.alwaysShowCalendars = true;
    // this.fromDate = calendar.getToday();
    // this.toDate = calendar.getNext(calendar.getToday(), 'd', 10);
    this.config.backdrop = 'static';
    this.observalble = this.setus.update_edit_perm().subscribe(message => { this.check_edit_permission(message) });
    this.response_data = this.notes_hadler.get_response_data('audit').subscribe(message => { this.collect_response(message) });
    this.alwaysShowCalendars = true;
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

  private mynotes() {
    this.isOpen = !this.isOpen;
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



  onFileChange(evt: any) {
    /* wire up file reader */
    const target: DataTransfer = <DataTransfer>(evt.target);
    console.log(target.files.length);

    if (target.files.length !== 1) throw new Error('Cannot use multiple files');
    const reader: FileReader = new FileReader();

    reader.onload = (e: any) => {
      /* read workbook */
      const bstr: string = e.target.result;

      const wb: XLSX.WorkBook = XLSX.read(bstr, { type: 'binary' });
      /* grab first sheet */
      const wsname: string = wb.SheetNames[0];
      const ws: XLSX.WorkSheet = wb.Sheets[wsname];

      /* save data */
      this.data = (XLSX.utils.sheet_to_json(ws, { header: 2 }));
      console.log(target.files[0]['name'].length);

      if (this.data.length != 0 && target.files[0].type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" && target.files[0]['name'].length <= 200) {
        this.formdata.append('file_name', target.files[0]);

        this.formdata.append('user_id', this.setus.getId());
        console.log(this.formdata);

        this.filenote = "";
      }
      else if (target.files[0]['name'].length > 200) {
        this.formGroup.controls.file.reset();
        this.toastr.errorToastr('Upload another file.', 'File Name too long!');
      }
      else {
        // console.log("Name",target.files[0]['name'].length);
        this.formGroup.controls.file.reset();
        // this.filenote="Invalid File";

        this.toastr.errorToastr('Invalid File.', 'Oops!');


        // setTimeout(()=>{
        //   this.filenote = "";
        //   }, 1000);
      }
    };
    reader.readAsBinaryString(target.files[0]);
  }

  onAutocc_FileChange(evt: any) {
    /* wire up file reader */
    const target: DataTransfer = <DataTransfer>(evt.target);
    console.log(target.files.length);

    if (target.files.length !== 1) throw new Error('Cannot use multiple files');
    const reader: FileReader = new FileReader();

    reader.onload = (e: any) => {
      /* read workbook */
      const bstr: string = e.target.result;

      const wb: XLSX.WorkBook = XLSX.read(bstr, { type: 'binary' });
      /* grab first sheet */
      const wsname: string = wb.SheetNames[0];
      const ws: XLSX.WorkSheet = wb.Sheets[wsname];

      /* save data */
      this.data = (XLSX.utils.sheet_to_json(ws, { header: 2 }));
      console.log(target.files[0]['name'].length);

      if (this.data.length != 0 && target.files[0].type == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" && target.files[0]['name'].length <= 200) {
        this.formdata.append('file_name', target.files[0]);

        this.formdata.append('user_id', this.setus.getId());
        console.log(this.formdata);

        this.filenote = "";
      }
      else if (target.files[0]['name'].length > 200) {
        this.autoclose_claim.controls.file.reset();
        this.toastr.errorToastr('Upload another file.', 'File Name too long!');
      }
      else {
        // console.log("Name",target.files[0]['name'].length);
        this.autoclose_claim.controls.file.reset();
        // this.filenote="Invalid File";

        this.toastr.errorToastr('Invalid File.', 'Oops!');


        // setTimeout(()=>{
        //   this.filenote = "";
        //   }, 1000);
      }
    };
    reader.readAsBinaryString(target.files[0]);
  }

  file_upload_id;

  public ignore_function() {
    this.Jarwis.updateingnore(this.file_upload_id).subscribe(
      message => this.updateignore(message),
      error => this.notify(error)
    );
  }

  public ignore() {
    this.duplicate = 0;
    this.toastr.successToastr('Successfully');
  }

  public updateignore(message) {
    console.log();
    // this.process_uld_file(message.upload_id);
  }

  public handlesuccess(res) {
    this.filecount = res.file_datas;
    console.log(this.filecount);
  }
  duplicates;

  public handlemessage(message) {
    // console.log("Handle",message);
    //assigning backend values to frontend

    this.newclaim = message.message.new_filter.length;
    this.duplicate = message.message.duplicate_filter.length;
    this.duplicates = message.message.filedata.total_claims;
    this.mismatch = message.message.mismatch_nos;
    console.log('mismatch' + this.mismatch);
    this.claimno = message.message.mismatch_nos;
    this.new_claims = message.message.new_filter_data;
    this.duplicate_claims = message.message.duplicate_filter;
    this.mismatch_claims = message.message.mismatch_data;
    this.mismatch_claim_nos = message.message.mismatch_nos;
    this.new_claim_data = message.message.new_datas;
    this.file_upload = message.message.filedata;
    this.file_upload_id = message.message.filedata.id;
    console.log(message.message.filedata.id);
    this.field_name = message.message.field_name;
    this.claims_processed = message.message.filedata.claims_processed;

    // console.log("Field",this.field_name)
    //Mismatch data Keys
    this.mismatch_claim_numbers = Object.keys(message.message.mismatch_data);
    this.mismatch_claim_number_sort = this.mismatch_claim_numbers;
    let z = [];
    let x = [];
    let y = [];
    let field_list = [];
    this.mismatch_claim_numbers.forEach(function (value) {
      let keys = value;
      let data = message.message.mismatch_data[keys]['midb'];
      let data2 = message.message.mismatch_data[keys]['mupd'];
      x[keys] = Object.values(data);
      y[keys] = Object.values(data2);
      z[keys] = Object.keys(data);
      let fields = Object.keys(data);
      for (let i = 0; i < fields.length; i++) {
        let x = field_list.find(x => x == fields[i]);
        if (x == undefined) {
          field_list.push(fields[i]);
        }
      }
    });
    //For Mismatch View
    this.mismatch_field_list = field_list;
    this.old_value = [];
    this.new_value = [];
    this.fieldselect = [];
    this.mismatch_claim_data = z;
    this.mismatch_claim_data_value = x;
    this.mismatch_claim_data_mismatch = y;
    this.loadingBar.stop();
    // this.getclaims();
    this.pageChange(1, 'all', null, null, 'null', 'null', 'null', 'null');
    this.fileupload = "";


    // this.error = message.error;
    // setTimeout(()=>{
    //   this.error = "";
    //   }, 1500);
  }

  notify(error) {
    //console.log(error);
    this.toastr.errorToastr('Error in Uploading File.');
  }

  upload_total: number;
  latest_id;
  importedfile;
  skip_row_import;
  current_row_import;
  total_row_import
  handleResponse(data) {
    console.log(data);
    this.roles = data.message;
    console.log(this.roles);
    this.latest_id = data.latest_id;
    this.importedfile = (data.message.filter(x => x.id == this.latest_id));
    console.log(this.importedfile);
    this.importedfile.forEach(element => {
      this.importProcessed = element.processed;
     });
    console.log(this.importProcessed);
    this.datas = this.tabdat;
    this.upload_total = data.count;
    this.total = data.count;
    this.current_total = data.current_total;
    this.skip = data.skip + 1;
    this.skip_row_import = this.skip;
    this.current_row_import = this.skip + this.current_total - 1;
    this.total_row_import = data.count;
  }

  handleError(error) {
    console.log(error);
  }



  // private getclaims()
  // {
  //   this.Jarwis.getclaims(this.setus.getId()).subscribe(
  //     data  => this.handleResponse(data),
  //     error => this.handleError(error)
  //   );
  // }

  filedown(data, name) {

    //console.log("Template",data);
    console.log(data);
    if (data.size == 47) {
      console.log('dfasdas');
      // this.error = "No Preferred Fields";
      this.toastr.errorToastr('No Preferred Fields.');
      // setTimeout(()=>{
      //   this.error = "";
      //   }, 1500);
    }
    else {
      // FileSaver.saveAs(data, name);
      console.log('dfasdas');
      // this.excelService.exportAsExcelFile(data, 'template');
      this.export_handler.create_template(data);
      this.toastr.successToastr('Download Complete');
    }
  }

  getfile(event, name) {
    this.Jarwis.getfile(event).subscribe(
      data => { FileSaver.saveAs(data, name); this.toastr.successToastr('Download Complete'); },
      error => { this.toastr.errorToastr("File Not Found") }

    );
  }

  public template() {
    this.Jarwis.template().subscribe(
      data => this.filedown(data, 'template')

    );
  }

  open(content) {
    this.modalService.open(content, { centered: true, windowClass: 'custom-class' }).result.then((result) => {
      this.closeResult = `${result}`;
    }, (reason) => {
      this.closeResult = `${this.getDismissReason()}`;
    });
  }

  //Modal Dismiss on Clicking Outside the Modal
  private getDismissReason() {
    this.clear();
    this.clear_notes();
  }

  public processdata() {
    console.log(this.formGroup.value)
    // this.loadingBar.start();

    let report_date = this.formGroup.value.report_date;
    this.formdata.append('report_date', report_date.day + '-' + report_date.month + '-' + report_date.year);
    this.formdata.append('notes', this.formGroup.value.notes);
    this.formdata.append('practice_dbid', localStorage.getItem('practice_id'));
    console.log(this.formdata);
    this.Jarwis.upload(this.formdata).subscribe(
      message => { this.handlemessage(message); this.toastr.successToastr('Uploaded'); },
      error => this.notify(error)
    );
  }
  notifysuccess(message) {
    console.log(message)
    this.toastr.successToastr(message);
  }


  public auto_close_claim() {
    console.log(this.autoclose_claim.value['file']);
    let file_name = this.autoclose_claim.value['file'];
    this.formdata.append('file_name', file_name);
    this.formdata.append('user_id', this.setus.getId());
    this.formdata.append('practice_dbid', localStorage.getItem('practice_id'));
    console.log(this.formdata);
    this.Jarwis.uploadcloseclaim(this.formdata).subscribe(
      message => {
        let data = message['message'];
        console.log(data); this.notifysuccess(data)
      },
      error => this.notify(error)
    );
  }



  public clear() {
    this.formGroup.controls.file.reset();
    this.formGroup.controls.notes.reset();
    this.formGroup.controls.report_date.reset();
  }

  public saveclaims() {
    this.Jarwis.createnewclaims(this.new_claim_data, this.file_upload, this.setus.getId()).subscribe(
      data => this.updatelog(data),
      error => this.handleError(error)
    );
  }

  public updatelog(data) {
    if (data.error == 'Created') {
      this.toastr.successToastr('New Claims Created.');
      console.log(this.claims_processed);
      this.new_claims = [];
      this.newclaim = 0;
    }


    // let compare = data.message.filter(item => this.new_claims.indexOf(item) < 0);
    // this.new_claims = compare;
    // this.newclaim = this.new_claims.length;

  }

  // public mismatch_action(data)
  // {
  //   if(data == 'Replace')
  //   {
  //     let mismatch_keys = this.mismatch_claim_data;
  //     let mismatch_values = this.mismatch_claim_data_mismatch;
  //     let mismatchkey = {};
  //     let mismatchvalue = {};
  //     this.mismatch_claim_numbers.forEach(function (value) {
  //       mismatchkey[value] = mismatch_keys[value];
  //       mismatchvalue[value] = mismatch_values[value];
  //       });
  //     let inputdata = [];
  //     inputdata.push(mismatchkey);
  //     inputdata.push(mismatchvalue);
  //     this.Jarwis.mismatch(inputdata).subscribe(
  //       message=> this.updatemismatch(message),
  //       error => this.notify(error)
  //       );
  //       }
  //       }

  public updatemismatch(data) {
    this.mismatch = data.message.length;
    console.log(this.mismatch);
    this.mismatch_claim_nos = data.message.length;
    this.mismatch_claim_numbers = Object.keys(data.message);
  }

  public displayvalues(claim, field) {
    this.old_value[claim] = this.mismatch_claims[claim]['midb'][field];
    this.new_value[claim] = this.mismatch_claims[claim]['mupd'][field];
    this.fieldselect[claim] = field;
  }

  public action(claim, data, type) {
    let field = data[claim];
    if (field == 0 || field == 'Select' || field == undefined && type != 'Ignore_all_fields') {
      this.toastr.errorToastr("Please Select a Value.")
      // console.log("Please Select a Value.");
    }
    else {
      let value = this.mismatch_claims[claim]['mupd'][field];
      let inputdata = [];
      inputdata.push(claim);
      inputdata.push(field);
      inputdata.push(value);

      if (type == 'Overwrite') {
        this.Jarwis.overwrite(inputdata, this.setus.getId()).subscribe(
          message => this.update_action(message, field, claim),
          error => this.notify(error)
        );
        this.toastr.successToastr("Successfull ")
      }
      else if (type == 'Ignore') {
        this.update_action('ignore', field, claim);
        this.toastr.successToastr("Successfull ")
      }
      else if (type == 'Ignore_all_fields') {
        this.mismatch_claim_data.splice(claim, 0);
        let indexsort = this.mismatch_claim_number_sort.indexOf(claim);
        this.mismatch_claim_number_sort.splice(indexsort, 1);
        this.mismatch_claim_nos = this.mismatch_claim_nos - 1;
        this.mismatch = this.mismatch_claim_nos;
        this.toastr.successToastr("Successfull ")
      }
    }
  }

  public ignore_all(data) {
    if (this.mismatch_selected == undefined || this.mismatch_selected == "All") {
      this.toastr.errorToastr("Please select Anything.");
      // console.log("Please select Anything.");
    }
    else {
      let mismatch_field_list_key: number = this.mismatch_field_list.indexOf(this.mismatch_selected);
      this.mismatch_field_list.splice(mismatch_field_list_key, 1);
      //Display Claims Removal
      this.mismatch_claim_number_sort = [];
      //Delete field data from Claims
      for (let i = 0; i < data.length; i++) {
        let claim_id = data[i];
        let array = this.mismatch_claim_data[claim_id];
        let index = <any>[];
        for (let j = 0; j < array.length; j++) {
          if (array[j] != this.mismatch_selected && array[j] != undefined) {
            index.push(array[j]);
          }
        }
        //Insert if Not Null
        if (index.length == 0) {
          //Main Sorted Variable
          let mismatchsort = this.mismatch_claim_numbers.indexOf(claim_id);
          this.mismatch_claim_numbers.splice(mismatchsort, 1);
          //Decrease Mismatch Claims Number
          this.mismatch_claim_nos = this.mismatch_claim_nos - 1;
          this.mismatch = this.mismatch_claim_nos;
        }
        else {
          this.mismatch_claim_data[claim_id] = index;
        }
        //Assigning Claims to current Instance Variable
        this.mismatch_claim_number_sort = this.mismatch_claim_numbers;
        this.old_value = [];
        this.new_value = [];
        //Clear Array
        index = [];
      }
    }
  }

  public overwrite_all(data) {
    if (this.mismatch_selected == undefined || this.mismatch_selected == "All") {
      this.toastr.errorToastr("Please select Anything.");
      // console.log("Please select Anything.");
    }
    else {
      let field = this.mismatch_selected;
      let value = [];
      for (let i = 0; i < data.length; i++) {
        value[data[i]] = this.mismatch_claims[data[i]]['mupd'][field];
      }
      let inputdata = [];
      inputdata.push(field);
      inputdata.push(data);
      inputdata.push(value);
      this.Jarwis.overwrite_all(inputdata, this.setus.getId()).subscribe(
        message => this.update_action_overwrite(message, field, data),
        error => this.notify(error)
      );
    }
  }

  public update_action_overwrite(data, field, claim) {
    //Collective Dropdown Ops
    let mismatch_field_list_key: number = this.mismatch_field_list.indexOf(field);
    this.mismatch_field_list.splice(mismatch_field_list_key, 1);
    //Display Claims Removal
    this.mismatch_claim_number_sort = [];
    //Delete field data from Claims
    let index = <any>[];
    for (let i = 0; i < claim.length; i++) {
      let claim_id = claim[i];
      let array = this.mismatch_claim_data[claim_id];

      for (let j = 0; j < array.length; j++) {

        if (array[j] != field && array[j] != undefined) {
          index.push(array[j]);
        }

      }
      //Insert if Not Null
      if (index.length == 0) {
        //Main Sorted Variable
        let mismatchsort = this.mismatch_claim_numbers.indexOf(claim_id);
        this.mismatch_claim_numbers.splice(mismatchsort, 1);

        //Decrease Mismatch Claims Number
        this.mismatch_claim_nos = this.mismatch_claim_nos - 1;
        this.mismatch = this.mismatch_claim_nos;
      }
      else {
        this.mismatch_claim_data[claim_id] = index;
      }
      //Assigning Claims to current Instance Variable
      this.mismatch_claim_number_sort = this.mismatch_claim_numbers;
      this.old_value = [];
      this.new_value = [];
      //Clear Array
      index = [];
    }
  }

  public update_action(data, field, claim) {
    let array = this.mismatch_claim_data[claim];
    this.mismatch_claim_data.splice(claim, 0);
    const index = <any>[];
    for (var i = 0; i < array.length; i++) {
      if (array[i] != field) {
        index.push(array[i]);
      }
    }
    this.mismatch_claim_data[claim] = index;
    if (this.mismatch_selected != undefined || this.mismatch_selected == "All") {
      let indexsort = this.mismatch_claim_number_sort.indexOf(claim);
      this.mismatch_claim_number_sort.splice(indexsort, 1);
    }

    //To remove Claims
    if (index.length == 0) {
      let ind = this.mismatch_claim_numbers.indexOf(claim);
      this.mismatch_claim_numbers.splice(ind, 1);
      this.mismatch_claim_nos = this.mismatch_claim_nos - 1;
      this.mismatch = this.mismatch_claim_nos;
    }
  }

  public display_selected(data) {
    this.mismatch_selected = data;
    this.mismatch_claim_number_sort = [];
    if (data == "All") {
      this.mismatch_claim_number_sort = this.mismatch_claim_numbers;
      this.old_value = [];
      this.new_value = [];
    }
    else {
      for (let i = 0; i < this.mismatch_claim_numbers.length; i++) {
        let claim = this.mismatch_claim_numbers[i];
        let claim_data = this.mismatch_claim_data[claim];
        let find = claim_data.find(x => x == data);
        if (find != undefined) {
          this.mismatch_claim_number_sort.push(claim);
          this.old_value[claim] = this.mismatch_claims[claim]['midb'][data];
          this.new_value[claim] = this.mismatch_claims[claim]['mupd'][data];
          console.log(this.mismatch_claims[claim]['mupd'][data]);
          this.fieldselect[claim] = data;
        }
      }
    }
  }

  //Create Work Order Tab Functions*****
  table_fields: string[];
  table_datas = [];
  claim_clicked: string[];
  claim_related: string[];
  process_notes: string[];
  claim_notes: string[];
  line_data = [];

  //Get Claim Details to Display
  // private getclaim_details()
  // {
  //   this.Jarwis.getclaim_details(this.setus.getId()).subscribe(
  //     data  => this.form_table(data),
  //     error => this.handleError(error)
  //   );
  // }

  // public form_table(data)
  // {
  //   this.table_fields=data.data.fields;
  //   // this.table_datas=data.data.datas;
  // }

  //Managing Values displayed in Modal
  public claim_no;

  tooltipOptions = {
    'placement': 'right',
    'show-delay': '200',
    'tooltip-class': 'new-tooltip-class',
    'background-color': '#9ad9e4',
    'margin-top': '20px'
  };

  public tooltip(claim) {
    this.claim_no = claim.claim_no;

    this.Jarwis.claims_tooltip(this.claim_no).subscribe(
      data => this.handleClaimsTooltip(data),
      error => this.handleError(error)
    );
  }

  claim_data;
  age;
  showAge;
  calculateAge;

  public handleClaimsTooltip(data) {
    this.claim_data = data.claim_data;
    this.age = data.claim_data.dob;

    const convertAge = new Date(this.age);
    const timeDiff = Math.abs(Date.now() - convertAge.getTime());
    this.showAge = Math.floor((timeDiff / (1000 * 3600 * 24)) / 365);
    this.calculateAge = this.showAge;
    console.log(this.calculateAge);
  }

  public claimslection(claim) {
    this.claim_no = claim.claim_no;
    this.get_line_items(claim);
    this.check_reassign_alloc(claim);
    //Clear Previous Claims
    this.clear_refer();
    this.claim_clicked = claim;
    // let length=this.table_datas?.length;
    this.claim_related = [];
    // this.get_related(claim);
    // console.log("Selected",this.claim_clicked);
    //Related Claims
    this.loading = true;

    this.Jarwis.get_selected_claim_details_fork(claim).subscribe(
      data => {
        this.claim_related = data[0]['data'],
        this.line_data = data[1]['data'],
        this.line_item_data.push(data[1]['data'])
      },
      error => this.handleError(error)
    );

    this.claim_related = [];


    //Check in DB for matching account_no


    // let length=this.table_datas.length;
    //   for(let i=0;i<this.table_datas.length;i++)
    //   {
    //     let related_length=this.claim_related.length;
    //     length= length-1;
    //     if(related_length<3)
    //     {
    //       if(this.table_datas[length]['acct_no'] == claim.acct_no && this.table_datas[length]['claim_no'] != claim.claim_no )
    //       {
    //        this.claim_related.push(this.table_datas[length]);
    //       }
    //     }
    //   }
    this.send_calim_det('footer_data');
    this.send_calim_det('followup');

    this.getnotes(this.claim_clicked);
    this.processNotesDelete(this.claim_no);
  }
  confirmation_type: string;
  reassign_claim: string;
  curr_reassigned_claims = [];
  reassign_allocation: boolean = true;
  check_reassign_alloc(claim) {
    //console.log("ROle",this.setus.get_role(),claim['audit_work_order']);
    //console.log(this.setus.get_role_id());
    if ((this.setus.get_role_id() == '4' || this.setus.get_role_id() == '3') && claim['audit_work_order'] != null) {
      let already_re = this.curr_reassigned_claims.indexOf(claim.claim_no);
      //console.log("Here REassign",claim,already_re);
      if (already_re < 0) {
        this.reassign_allocation = true;
        //console.log(this.reassign_allocation);
      }
      else {
        this.reassign_allocation = false;
      }

    }
    else {
      this.reassign_allocation = false;
    }

  }

  /*get_related(claim)
 {
   this.Jarwis.get_related_calims(claim,'followup',this.setus.getId()).subscribe(
     data  => this.list_related(data),
     error => this.handleError(error)
     );
 }

 list_related(claims)
 {
   this.claim_related = claims.data;
 }*/
  edit_permission: boolean = false;
  check_edit_permission(data) {
    if (data.includes('claims')) {
      this.edit_permission = true;
      //console.log(data);
    }
    else {
      this.edit_permission = false;
    }
    //console.log(this.edit_permission);
  }


  claim_active;
  public send_calim_det(type) {
    console.log(type);
    console.log(this.main_tab);
    if (this.main_tab == true) {
      if (type == 'claims') {
        console.log(this.claim_clicked['claim_no']);
        this.follow.setvalue(this.claim_clicked['claim_no']);
      }
      else {
        this.notes_hadler.selected_tab(this.claim_clicked['claim_no']);
        this.notes_hadler.set_claim_details(this.claim_clicked);
        this.claim_active = this.claim_clicked;
      }
    }
    else {
      if (type == 'claims') {
        this.follow.setvalue(this.active_claim);
      }
      else {

        this.notes_hadler.selected_tab(this.active_claim);
        let claim_detials = this.refer_claim_det.find(x => x.claim_no == this.active_claim);
        this.notes_hadler.set_claim_details(claim_detials);
        this.claim_active = this.active_claim;
      }

    }

  }





  processNotesDelete(data) {
    // this.Jarwis.process_notes_delete(data, this.setus.getId()).subscribe(
    //   data  => this.handleResponseProcess(data),
    //   error => this.handleError(error)
    // );
  }

  handleResponseProcess(data) {
    this.getnotes(this.claim_clicked);
  }



  public closeclaimslection(claim) {
    this.claim_no = claim.claim_no;
    //console.log(this.claim_no);
    this.get_line_items(claim);
    this.check_reassign_alloc(claim);
    //Clear Previous Claims
    this.clear_refer();
    this.claim_clicked = claim;
    let length = this.table_datas.length;
    this.claim_related = [];
    // this.get_related(claim);
    // console.log("Selected",this.claim_clicked);
    //Related Claims
    this.loading = true;

    this.Jarwis.get_selected_claim_details_fork(claim).subscribe(
      data => {
        this.claim_related = data[0]['data'],
        this.line_data = data[1]['data'],
        this.line_item_data.push(data[1]['data'])
      },
      error => this.handleError(error)
    );
    //console.log(this.claim_related);
    this.claim_related = [];


    //Check in DB for matching account_no


    // let length=this.table_datas.length;
    //   for(let i=0;i<this.table_datas.length;i++)
    //   {
    //     let related_length=this.claim_related.length;
    //     length= length-1;
    //     if(related_length<3)
    //     {
    //       if(this.table_datas[length]['acct_no'] == claim.acct_no && this.table_datas[length]['claim_no'] != claim.claim_no )
    //       {
    //        this.claim_related.push(this.table_datas[length]);
    //       }
    //     }
    //   }
    //this.send_calim_det('footer_data');
    this.send_calim_det('Audit');
    this.getnotes(this.claim_clicked);
    //this.processNotesDelete(this.claim_no);
  }

  //Refer Claim Clicked Action
  refer_claim_det = [];
  refer_claim_no = [];
  refer_claim_notes_nos = [];
  refer_process_notes_nos = [];
  refer_qc_notes_nos = [];
  refer_client_notes_nos = [];
  refer_claim_notes = [];
  refer_process_notes = [];
  refer_qc_notes = [];
  refer_client_notes = [];
  main_tab: boolean = true;
  active_tab = [];
  active_refer_claim = [];
  active_refer_process = [];
  active_refer_qc = [];
  active_refer_client = [];
  active_claim: string[];
  refer_claim_editable = 'false';
  assigned_datas;
  claim_status;
  claim_nos;

  public referclaim(claim) {
    console.log(claim.claim_no);

    claim = claim;

    console.log(claim);

    this.claim_nos = claim.claim_no;


    console.log(this.type);

    this.claim_status = claim.claim_Status;
    this.Jarwis.get_claimno(this.claim_nos, this.setus.getId(), this.claim_status, this.type).subscribe(
      data => this.handleClaimNo(data),
      error => this.handleError(error)
    );


    this.get_line_items(claim);


    if (this.refer_claim_no.indexOf(claim['claim_no']) < 0) {
      this.refer_claim_det.push(claim);
      this.refer_claim_no.push(claim['claim_no']);

      this.Jarwis.getnotes(claim).subscribe(
        data => this.refer_notes(data, claim.claim_no),
        error => this.handleError(error)
      );

    }
    else {
      this.selected_tab(claim['claim_no']);
    }

    //this.get_line_items(claim);
    // this.check_reassign_alloc(claim);
    //Clear Previous Claims
    //this.clear_refer();

    this.send_calim_det('footer_data');
    this.send_calim_det('claims');

    this.getnotes(this.claim_clicked);
    this.processNotesDelete(this.claim_no);
  }

  public handleClaimNo(data) {
    this.assigned_datas = data.claim_count;
    console.log(this.assigned_datas);
    this.refer_claim(this.assigned_datas);
  }

  refer_claim(assigned_datas) {
    if (assigned_datas == true) {
      this.refer_claim_editable = 'true';
    } else if (assigned_datas == false) {
      this.refer_claim_editable = 'false';
    }
  }

  //Display Reference Notes
  public refer_notes(data, claimno) {
    this.refer_claim_notes_nos.push(claimno);
    this.refer_claim_notes.push(data.data.claim);

    this.refer_process_notes_nos.push(claimno);
    this.refer_process_notes.push(data.data.process);

    this.refer_qc_notes_nos.push(claimno);
    this.refer_qc_notes.push(data.data.qc);

    this.refer_client_notes_nos.push(claimno);
    this.refer_client_notes.push(data.data.client);


    let index_claim = this.refer_claim_notes_nos.indexOf(claimno);
    let index_process = this.refer_process_notes_nos.indexOf(claimno);
    let index_qc = this.refer_qc_notes_nos.indexOf(claimno);
    let index_client = this.refer_client_notes_nos.indexOf(claimno);

    this.active_refer_claim = this.refer_claim_notes[index_claim];
    this.active_refer_process = this.refer_process_notes[index_process];
    this.active_refer_qc = this.refer_qc_notes[index_qc];
    this.active_refer_client = this.refer_client_notes[index_client];

    this.main_tab = false;
    this.active_claim = claimno;


  }

  public update_refer_notes(data, type, claimno) {
    console.log(type);
    let index_up_qc = this.refer_qc_notes_nos.indexOf(claimno);
    let index_up_process = this.refer_process_notes_nos.indexOf(claimno);
    console.log(index_up_process);
    let index_up_claim = this.refer_claim_notes_nos.indexOf(claimno);
    if (type == 'processnotes') {
      if (index_up_process == undefined) {
        this.refer_process_notes_nos.push(claimno);
        this.refer_process_notes.push(data.data);
        index_up_process = this.refer_process_notes_nos.indexOf(claimno);
      }
      else {
        this.refer_process_notes[index_up_process] = data.data;
        console.log(this.refer_process_notes[index_up_process]);
      }
      // this.refer_process_notes[claimno]=data.data;
    }
    else if (type == 'claimnotes') {
      if (index_up_claim == undefined) {
        this.refer_claim_notes_nos.push(claimno);
        this.refer_claim_notes.push(data.data);
        index_up_claim = this.refer_claim_notes_nos.indexOf(claimno);
      }
      else {
        this.refer_claim_notes[index_up_claim] = data.data;
      }
      // this.refer_claim_notes[claimno]=data.data;
    }
    else if (type == 'qcnotes') {
      if (index_up_qc == undefined) {
        this.refer_qc_notes_nos.push(claimno);
        this.refer_qc_notes.push(data.data);
        index_up_qc = this.refer_qc_notes_nos.indexOf(claimno);
      }
      else {
        this.refer_qc_notes[index_up_qc] = data.data;
      }

      // this.refer_qc_notes[claimno]=data.data;
    }
    this.active_refer_claim = this.refer_claim_notes[index_up_claim];
    this.active_refer_process = this.refer_process_notes[index_up_process];
    this.active_refer_qc = this.refer_qc_notes[index_up_qc];
  }

  //Focus on Selected Tab
  public selected_tab(claimno) {
    console.log(claimno);
    if (claimno == 'maintab') {
      this.main_tab = true;
      this.active_claim = [];
    }
    else {

      let index_qc = this.refer_qc_notes_nos.indexOf(claimno);
      let index_process = this.refer_process_notes_nos.indexOf(claimno);
      let index_claim = this.refer_claim_notes_nos.indexOf(claimno);
      let index_client = this.refer_claim_notes_nos.indexOf(claimno);

      this.active_refer_claim = this.refer_claim_notes[index_claim];
      this.active_refer_process = this.refer_process_notes[index_process];
      this.active_refer_qc = this.refer_qc_notes[index_qc];
      this.active_refer_client = this.refer_client_notes[index_client];
      this.main_tab = false;
      console.log(this.main_tab, '--->>>>');
      this.active_claim = claimno;
    }
    this.send_calim_det('footer_data');
    this.send_calim_det('followup');
  }

  //Close Refer Tab
  public close_tab(claim_no) {
    let index = this.refer_claim_det.indexOf(claim_no);
    let list_index = this.refer_claim_no.indexOf(claim_no.claim_no);
    this.refer_claim_det.splice(index, 1);
    this.refer_claim_no.splice(list_index, 1);
    this.main_tab = true;
    this.active_claim = [];
    this.send_calim_det('footer_data');
    this.send_calim_det('followup');
    this.get_line_items(this.claim_clicked);
  }

  //Clear Tabs Details
  public clear_refer() {
    this.main_tab = true;
    this.active_claim = [];
    this.refer_claim_det = [];
    this.refer_claim_no = [];
  }

  qc_notes;
  client_notes;
  //Update Displayed Notes
  public display_notes(data, type) {
    console.log('data' + data);
    if (this.active_claim != undefined) {
      console.log(type);
      console.log(this.active_claim);
      if (this.active_claim.length != 0) {
        this.update_refer_notes(data, type, this.active_claim)
      }
      else {
        if (type == 'processnotes') {
          console.log(type);
          console.log(data);
          this.process_notes = data.data;
          console.log(this.process_notes);
        }
        else if (type == 'claimnotes') {
          this.claim_notes = data.data;
          console.log(this.claim_notes);
        }
        else if (type == 'qcnotes') {
          this.qc_notes = data.data;
          console.log(this.qc_notes);
        }
        else if (type == 'All') {
          this.process_notes = data.data.process;
          this.claim_notes = data.data.claim;
          this.qc_notes = data.data.qc;
          this.client_notes = data.data.client;
          console.log("All details");
          console.log(this.claim_notes);
          console.log(this.qc_notes);
        }
      }
      this.loading = false;
      this.processNotes.reset();
      //this.claimNotes.reset();
      //this.qcNotes.reset();
    }

  }

  //Get Notes
  public getnotes(claim) {
    // console.log("get_notes",claim)
    this.process_notes = [];
    this.claim_notes = [];
    this.qc_notes = [];
    this.client_notes = [];
    let type = 'All';
    this.Jarwis.getnotes(claim).subscribe(
      data => this.display_notes(data, type),
      error => this.handleError(error)
    );
  }

  get_audit_codes() {
    if (!this.audit_codes_list) {
      this.Jarwis.get_audit_codes(this.setus.getId()).subscribe(
        data => this.assign_audit_codes(data),
        error => this.handleError(error)
      );
    }

  }

  root_cause_list;
  err_type_list;

  assign_audit_codes(data) {
    // console.log(data);
    let root_stats = data.root_states;
    let err_stats = data.err_types;

    this.audit_codes_list = { root: root_stats, error: err_stats };

    let root_states = [];
    for (let i = 0; i < root_stats.length; i++) {
      if (root_stats[i].status == '1') {
        root_states.push({ id: root_stats[i]['id'], description: root_stats[i]['name'] });
      }
    }
    this.root_cause_list = root_states;
    let error_states = [];
    for (let j = 0; j < err_stats.length; j++) {
      if (err_stats[j].status == '1') {
        error_states.push({ id: err_stats[j]['id'], description: err_stats[j]['name'] });
      }
    }
    this.err_type_list = error_states;
    // console.log("err",this.err_type_list,this.root_cause_list);
    // sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });

  }


  //Edit Notes
  edit_noteid: number;
  initial_edit: boolean = false;
  audit_codes_list;
  public editnotes(type, value, id) {
    if (type == 'qc_notes_init') {
      let qc_data = this.qc_notes_data.find(x => x.id == id['claim_no']);
      this.editnote_value = qc_data.notes;
      this.edit_noteid = id;
      this.initial_edit = true;
    } else if (type == 'processnote') {
      this.editnote_value = value;
      this.edit_noteid = id;
      this.initial_edit = false;
    }
    else {
      this.editnote_value = value.content;
      this.edit_noteid = id;

      if (type == 'qcnote') {
        let root_cause = value.root_cause;
        let error_type = JSON.parse(value.error_type);

        // console.log(this.audit_codes_list);
        let root_det = this.audit_codes_list.root.find(x => x.id = root_cause);

        let error_det = this.audit_codes_list.error;

        let selecetd_err = [];
        // console.log("ERR_tyoe",error_type);
        error_type.forEach(function (value) {
          let keys = value;
          let error = error_det.find(x => x.id == keys);
          selecetd_err.push({ id: keys, description: error['name'] });
        });
        this.qcNotes.patchValue({
          root_cause: { id: root_cause, description: root_det['name'] },
          error_type: selecetd_err
        });
      }




      this.initial_edit = false;
    }

  }

  rc_et_data: any;

  //Handle Rootcause and Error Type
  public handle_notes_opt() {
    // console.log("QC",this.qcNotes.value);

    let error_type = this.qcNotes.value['error_type'];
    let root_cause = this.qcNotes.value['root_cause'];
    let error_types_ids = [];

    error_type.forEach(function (value) {
      let keys = value;
      error_types_ids.push(keys['id']);
    });

    this.rc_et_data = { root_cause: root_cause['id'], error_types: error_types_ids }

  }




  //Update Notes
  public updatenotes(type) {
    if (this.initial_edit == true) {
      this.handle_notes_opt();
      // console.log("QC",this.rc_et_data);
      let notes_det = { notes: this.qcNotes.value['qc_notes'], notes_opt: this.rc_et_data };
      this.notes_hadler.set_notes(this.setus.getId(), notes_det, this.edit_noteid, 'create_qcnotes');

      // this.qc_notes_data[this.edit_noteid['claim_no']]=this.qcNotes.value['qc_notes'];

      this.qc_notes_data.find(x => x.id == this.edit_noteid['claim_no']).notes = this.qcNotes.value['qc_notes'];


      this.initial_edit = false;
      this.send_calim_det('followup');
      this.send_calim_det('footer_data');
    }
    else {

      if (type == 'processnotes') {
        this.Jarwis.process_note(this.setus.getId(), this.processNotes.value['processnotes'], this.edit_noteid, 'processupdate', 'audit-closed').subscribe(
          data => this.display_notes(data, type),
          error => this.handleError(error)
        );
      }
      else if (type == 'claimnotes') {
        this.Jarwis.claim_note(this.setus.getId(), this.claimNotes.value['claim_notes'], this.edit_noteid, 'claimupdate').subscribe(
          data => this.display_notes(data, type),
          error => this.handleError(error)
        );
      }
      else if (type == 'qcnotes') {


        let claim_active;

        if (this.main_tab == true) {
          claim_active = this.claim_clicked;
        }
        else {
          claim_active = this.refer_claim_det.find(x => x.claim_no == this.active_claim);
        }



        this.Jarwis.check_edit_val(claim_active, 'audit').subscribe(
          data => {
            this.set_note_edit_validity(data);
            if (this.note_edit_val != undefined) {
              this.handle_notes_opt();
              let notes_det = { notes: this.qcNotes.value['qc_notes'], notes_opt: this.rc_et_data };

              this.Jarwis.qc_note(this.setus.getId(), notes_det, this.edit_noteid, 'qcupdate').subscribe(
                data => this.display_notes(data, type),
                error => this.handleError(error)
              );
            }
            else {
              this.toastr.errorToastr('Notes cannot be Updated.', 'Claim Processed.');
            }
          },
          error => this.handleError(error)
        );


      }

    }
    this.editnote_value = null;
  }




  //Save Notes
  qc_notes_data: Array<any> = [];
  qc_notes_data_list = [];
  qcNotes: FormGroup;
  public savenotes(type) {
    console.log(type);
    let claim_id = [];
    if (this.active_claim.length != 0) {
      let index = this.refer_claim_no.indexOf(this.active_claim);
      claim_id = this.refer_claim_det[index];
    }
    else {
      claim_id = this.claim_clicked;
    }
    if (type == 'processnotes') {
      this.Jarwis.process_note(this.setus.getId(), this.processNotes.value['processnotes'], claim_id, 'processcreate', 'create_claims').subscribe(
        data => this.display_notes(data, type),
        error => this.handleError(error)
      );
    }
    else if (type == 'claimnotes') {
      this.Jarwis.claim_note(this.setus.getId(), this.claimNotes.value['claim_notes'], claim_id, 'claim_create').subscribe(
        data => this.display_notes(data, type),
        error => this.handleError(error)
      );
    }
    else if (type == 'qcnotes') {
      console.log(this.qcNotes.value);
      this.submitted = true;
      this.Jarwis.qc_note(this.setus.getId(), this.qcNotes.value['qc_notes'], claim_id, 'create_qcnotes').subscribe(
        data => this.display_notes(data, type),
        error => this.handleError(error)
      );
      this.handle_notes_opt();
      // console.log("QC",this.rc_et_data);
      this.qc_notes_data.push({ notes: this.qcNotes.value['qc_notes'], id: claim_id['claim_no'], notes_opt: this.rc_et_data });
      this.qc_notes_data_list.push(claim_id['claim_no']);

      let notes_det = { notes: this.qcNotes.value['qc_notes'], notes_opt: this.rc_et_data };
      this.notes_hadler.set_notes(this.setus.getId(), notes_det, claim_id, 'create_qcnotes');
      this.send_calim_det('footer_data');
      this.send_calim_det('followup');
    }
  }

  public collect_response(data) {

    if (this.main_tab == true) {
      this.check_note_edit_validity(this.claim_clicked);
    }
    else {

      let claim_detials = this.refer_claim_det.find(x => x.claim_no == this.active_claim);
      this.check_note_edit_validity(claim_detials);
    }

    this.display_notes(data, 'qcnotes');
    this.get_workorder('closedClaims', 0, 0, 1, 1, null, null, null, null, null, null, null);
    let index = this.qc_notes_data_list.indexOf(this.active_claim);
    this.qc_notes_data_list.splice(index, 1);
  }

  check_note_edit_validity(claim) {
    this.Jarwis.check_edit_val(claim, 'audit').subscribe(
      data => this.set_note_edit_validity(data),
      error => this.handleError(error)
    );

  }


  note_edit_val: number;
  set_note_edit_validity(data) {
    if (data.edit_val == true) {
      this.note_edit_val = data.note_id['id'];
    }
    else {
      this.note_edit_val = undefined;
    }
  }



  public clear_notes() {
    this.editnote_value = null;
    this.processNotes.reset();
  }

  //Send Claim Value to Followup-Template Component on Opening Template
  // public send_calim_det()
  // {
  //   if(this.main_tab==true)
  //   {
  //     this.follow.setvalue(this.claim_clicked['claim_no']);
  //   }
  //   else
  //   {
  //     this.follow.setvalue(this.active_claim)
  //   }
  //  }



  //Create Work Order
  public check_all: Array<any> = [];
  public selected_claims = [];
  public selected_claim_nos = [];

  public check_all_assign(page, event) {
    if (event.target.checked == true) {
      this.check_all[page] = true;
      console.log(this.check_all[page]);
    }
    else {
      this.check_all[page] = false;
    }

  }
  //Select all Check

  selectAll(isChecked: boolean) {
    this.isSelectedAll = !this.isSelectedAll;
    const indices = (this.pageRows.toArray().map(vcr => +vcr.nativeElement.dataset.index));
    this.table_datas.filter(i => indices.indexOf(i.index) > -1)
    .forEach(i => i.checked = this.isSelectedAll);
  }

  // public select_all(event)
  // {
  // if(event.target.checked == true)
  // {
  // this.check_all='all';
  // }
  // else if(event.target.checked == false)
  // {
  //   this.check_all='none';
  // }

  // }
  public select_claim(content) {
    if (this.selected_claim_nos.length == 0) {
      this.toastr.errorToastr('Please Select Claim');
    }
    else {
      this.modalService.open(content, { centered: true, windowClass: 'custom-class' }).result.then((result) => {
        this.closeResult = `${result}`;
      }, (reason) => {
        this.closeResult = `${this.getDismissReason()}`;
      });
    }
  }

  //Selected Claim Sorting
  public selected(event, claim, index) {
    console.log(this.selected_claim_nos);

    if (claim == 'all' && event.target.checked == true) {
      // for(let i=0;i<index;i++){
      //   var selected_claim_datas;
      //   selected_claim_datas.push(this.selected_claim_data[i]);
      // }
      let selected_claim_data = this.selected_claim_data;
      let claim_nos = this.selected_claim_nos;
      let claim_data = this.selected_claims;
      selected_claim_data.forEach(function (value) {
        let keys = value;
        if (!claim_nos.includes(keys['claim_no'])) {
          claim_nos.push(keys['claim_no']);
          claim_data.push(keys);
        }
      });
      this.selected_claim_nos = claim_nos;
      this.selected_claims = claim_data;
      console.log(this.selected_claim_nos);
    }
    else if (claim == 'all' && event.target.checked == false) {

      for (let i = 0; i < this.selected_claim_data.length; i++) {
        let claim = this.selected_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind, 1);
        this.selected_claim_nos.splice(ind, 1);
      }

      // this.selected_claims=[];
      // this.selected_claim_nos=[];
    }
    else if (event.target.checked == true) {
      this.selected_claims.push(this.selected_claim_data[index]);
      this.selected_claim_nos.push(claim);
    }
    else if (event.target.checked == false) {
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind, 1);
      this.selected_claim_nos.splice(ind, 1);

    }

  }



  public closed_selected(event, claim, index) {
    console.log(event.target.checked);
    console.log(claim);
    console.log(index);

    if (claim == 'all' && event.target.checked == true) {
      let closed_claim_data = this.closed_claim_data;
      let claim_nos = this.selected_claim_nos;
      let claim_data = this.selected_claims;

      closed_claim_data.forEach(function (value) {
        let keys = value;
        if (!claim_nos.includes(keys['claim_no'])) {
          claim_nos.push(keys['claim_no']);
          claim_data.push(keys);
        }
      });
      this.selected_claim_nos = claim_nos;
      console.log(this.selected_claim_nos);
      this.selected_claims = claim_data;
    }
    else if (claim == 'all' && event.target.checked == false) {

      for (let i = 0; i < this.closed_claim_data.length; i++) {
        let claim = this.closed_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind, 1);
        this.selected_claim_nos.splice(ind, 1);

      }

      // this.selected_claims=[];
      // this.selected_claim_nos=[];
    }
    else if (event.target.checked == true) {
      this.selected_claims.push(this.closed_claim_data[index]);
      this.selected_claim_nos.push(claim);
    }
    else if (event.target.checked == false) {
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind, 1);
      this.selected_claim_nos.splice(ind, 1);

    }

  }


  associates_detail: Array<any> = [];

  //Render Associates data into Popup
  public assign_data(data) {
    this.associates_detail = data.data;
  }

  //Get Associates data
  public get_associates() {
    this.Jarwis.get_associates(this.setus.getId()).subscribe(
      data => this.assign_data(data),
      error => this.handleError(error)
    );
  }

  claim_assign_type: string = null;
  selected_associates = [];

  //Selected Associates
  public selected_id;
  public select_associates(event, id) {
    console.log('testing_id: ' + id);
    if (event.target.checked == true) {
      this.selected_associates.push(id);
      this.selected_id = id;
      console.log(this.selected_associates);

    }
    else if (event.target.checked == false) {
      let index = this.selected_associates.indexOf(id);
      this.selected_associates.splice(index, 1);

      //Reduce Assigned Numbers Unchecked Associates
      let x = this.assigned_claims_details.find(v => v.id == id);
      if (x != undefined) {
        let ind = this.assigned_claims_details.indexOf(x);
        this.assigned_claims_details.splice(ind, 1);
        if (x.value != 0) {
          this.assigned_claim_nos = this.assigned_claim_nos - Number(x.value);
        }
      }

      // console.log(x);
      // Limit Remove
      let limit_index = this.limit_exceeds.indexOf(x);
      this.limit_exceeds.splice(limit_index, 1);

      if (this.limit_exceeds.length == 0) {
        this.limit_clearance = true;
      }


    }

    this.associates_error_status = true;
    this.proceed_stats();
  }

  //Manual or Automatic Assign
  public assign_type(type) {
    if (this.selected_associates.length == 0) {
      this.toastr.errorToastr("Please select Associate");
    }
    else {
      this.claim_assign_type = type;
    }
  }



  //Auto assign Claims

  tested;
  public auto_post_claims(data) {
    this.assign_status = [];
    console.log("Claim Stats", data.import_det);
    let claim_stats = data.data;
    //   this.for(let j=0;j < assigned_associates.length;j++)
    // {
    let reassigned_claims = [];
    let new_claim = [];
    this.tested = data.import_det;

    console.log(this.selected_claim_nos.length);

    for (let i = 0; i < this.selected_claim_nos.length; i++) {
      console.log(this.selected_claim_nos[i]);
      let curr_claim = this.selected_claim_nos[i];
      console.log(curr_claim);

      if (curr_claim != null) {
        let reass_index = reassigned_claims.findIndex(v => v.id == this.selected_id);
        console.log(reass_index);
        if (reass_index < 0) {
          reassigned_claims.push({ id: this.selected_id, value: [curr_claim] })
          console.log("Reassigned", reassigned_claims);
        }
        else {
          reassigned_claims[reass_index]['value'].push(curr_claim);
          console.log("Reassigned2", reassigned_claims);
        }
      }
      else {
        new_claim.push(this.selected_claim_nos[i]);
        console.log(new_claim);
      }

    }


    // console.log("associate",new_claim,this.associates_detail,this.selected_associates);

    let process_associates = [];
    let claim_assign_nos = []

    console.log(this.selected_associates);

    if (this.selected_associates.length == 0) {
      process_associates = this.associates_detail;
    }
    else {
      for (let i = 0; i < this.selected_associates.length; i++) {
        console.log(this.selected_associates[i]);
        process_associates.push(this.associates_detail.find(v => v.id == this.selected_associates[i]));
        console.log(process_associates);
      }
    }

    let assign_total = 0;
    let total_new_cliam = new_claim.length;
    process_associates.forEach(element => {
      assign_total += Number(2);
      console.log("Assoc", assign_total, "TOT", element['assign_limit']);
    });


    for (let i = 0; i < process_associates.length; i++) {
      var assigned = {};
      let associate_data = process_associates[i];
      // console.log("Assoc_data",associate_data,associate_data['assign_limit'],associate_data);

      // let assignable_nos=Number(associate_data['assign_limit']) - Number(associate_data['assigned_claims']);

      console.log('total_new_cliam ' + total_new_cliam);
      console.log('assign_total ' + assign_total);
      console.log('associate_data ' + associate_data['assign_limit']);

      let assignable = (Number('1') / Number(assign_total)) * Number('1');
      console.log(assignable);
      let assignable_nos = Number(assignable.toFixed());

      console.log("Ass_nos", total_new_cliam, assignable, assignable_nos);


      let assigned_claims = [];
      console.log(associate_data['id']);
      if (reassigned_claims.findIndex(x => x.id == associate_data['id']) >= 0) {
        let claims_ref = reassigned_claims.find(x => x.id = associate_data['id']);

        console.log(claims_ref);

        let claims = claims_ref['value'];
        console.log("=>", claims)
        assigned_claims = claims;
      }

      console.log(assignable_nos);

      if (assignable_nos > 0) {
        console.log(new_claim);
        let new_assigned = new_claim.splice(0, assignable_nos);
        console.log(new_assigned);
        new_assigned.forEach(element => {
          assigned_claims.push(element);
        });

      }

      assigned['assigned'] = assigned_claims.length;
      assigned['assigned_to'] = associate_data['id'];
      assigned['claims'] = assigned_claims;
      assigned['max'] = assigned_claims.length;

      console.log(assigned);
      console.log(this.assign_status);
      this.assign_status.push(assigned);
      console.log(this.assign_status);
    }

    // console.log("Assigned",reassigned_claims,"New",new_claim,this.associates_detail);
    // console.log("Final",this.assign_status);



    // Old Logic
    /*
    if(assignable_nos >0)
    {
      // console.log("Ind",reassigned_claims,associate_data['id'],reassigned_claims.findIndex(x => x.id == associate_data['id'] ));
      if( reassigned_claims.findIndex(x => x.id == associate_data['id'] ) >= 0 )
      {
        let claims_ref=reassigned_claims.find(x => x.id = associate_data['id'] );
        console.log("claims_ref",claims_ref,claims_ref['value'].length,claims_ref['value'].length <= assignable_nos)

        let assigned_claims=[];

        if(claims_ref['value'].length <= Number(assignable_nos) )
        {
          let claims=claims_ref['value'];
          // console.log("Fmm,f",claims);
          assigned_claims.push(claims);
          assignable_nos=assignable_nos-claims.length;
          let new_assigned;
          if(assignable_nos >0 && new_claim.length >= assignable_nos)
          {
            new_assigned= new_claim.splice(0,assignable_nos);

          }
          else if(new_claim.length !=0)
          {
            new_assigned=new_claim;
            new_claim=[];
          }
          assigned_claims.push(new_assigned);

        }
        else
        {
          // console.log("comp",claims_ref['value'],claims_ref['value'].length,assignable_nos);
          claims_ref['value'].length = assignable_nos ;
          assigned_claims= claims_ref['value'];
          // console.log("Exceed,f",claims_ref['value']);
        }

        // console.log("Old and New",assigned_claims);
        // var assigned={
        //   assigned:assigned_claims.length,
        //   assigned_to:associate_data['id'],
        //   claims:assigned_claims,
        //   max:assignable_nos
        // };
        assigned['assigned']= assigned_claims.length;
        assigned['assigned_to']= associate_data['id'];
        assigned['claims']= assigned_claims;
        assigned['max']= assignable_nos;
        // console.log(assigned);
        this.assign_status.push(assigned);


      }
      else if(new_claim.length > 0)
      {
        let claims_assigned;
        if(new_claim.length >= assignable_nos)
        {
          claims_assigned=new_claim.splice(0,assignable_nos)
        }
        else if(new_claim.length !=0)
        {
          claims_assigned=new_claim;
          new_claim=[];
        }
        // console.log("New",claims_assigned,assignable_nos,"NC",new_claim)

        // var assigned_here={
        //   assigned:associate_data.length,
        //   assigned_to:associate_data['id'],
        //   claims:claims_assigned,
        //   max:assignable_nos
        // };

        assigned['assigned']= claims_assigned.length;
        assigned['assigned_to']= associate_data['id'];
        assigned['claims']= claims_assigned;
        assigned['max']= assignable_nos;
        // console.log(assigned);
        this.assign_status.push(assigned);


      }
    }
  */





    // console.log(this.assign_status);

    let assigned_count = 0;
    console.log(this.assign_status);
    this.create_workorder();

    this.assign_status.forEach(element => {

      assigned_count += element.claims.length;

    });

    console.log(assigned_count);

    // if(this.assign_status.length == 0)
    if (assigned_count == 0) {
      this.null_assigned = true;

    }
    else {
      this.null_assigned = false;
      this.associates_error_status = false;
    }
  }


  //Manual Assign Function
  public assigned_claims_details: Array<any> = [];
  public associate_error: string;
  public associate_error_handler: string[];
  assigned_claim_nos: number = 0;
  public manual_assign(event, id) {
    let check = this.assigned_claims_details.some(function (value) {
      return value.id === id;
    });;
    console.log("Man", event.target.value, id, check);
    if (event.target.value != 0) {
      if (!check) {
        console.log(id);
        console.log(event.target.value);
        this.assigned_claims_details.push({ id: id, value: event.target.value });
        console.log(this.assigned_claims_details);
      }
      else {
        this.assigned_claims_details.find(v => v.id == id).value = event.target.value;
        console.log(this.assigned_claims_details);
      }
    }
    else if (this.assigned_claims_details.find(v => v.id == id) != 0 && this.assigned_claims_details.find(v => v.id == id) != undefined) {
      // console.log(this.assigned_claims_details.find(v => v.id == id));
      this.assigned_claims_details.find(v => v.id == id).value = 0;
      console.log(this.assigned_claims_details);
    }
    // console.log("assigned",this.assigned_claims_details);
    this.calculate_assigned();
    // this.check_limit();
    this.associates_error_status = true;
    this.proceed_stats();
  }

  public assigned_data: Array<any> = [];
  //Calculate Assigned and Unassigned Claims
  public calculate_assigned() {
    let total = 0;
    for (let i = 0; i < this.assigned_claims_details.length; i++) {
      total += Number(this.assigned_claims_details[i]['value']);
      this.assigned_data[this.assigned_claims_details[i]['id']] = this.assigned_claims_details[i]['value'];
    }

    this.assigned_claim_nos = total;
  }

  limit_clearance: boolean = false;
  limit_exceeds = [];
  //Monitor Limit of Associates
  check_limit() {
    // console.log("Here",this.assigned_claims_details)

    for (let i = 0; i < this.assigned_claims_details.length; i++) {
      let associate = this.associates_detail.find(x => x['id'] == this.assigned_claims_details[i]['id']);

      let total_assigned = Number(this.assigned_claims_details[i]['value']) + Number(associate['assigned_claims']);
      // console.log("Ta",total_assigned,associate['assign_limit'])
      if (associate['assign_limit'] < total_assigned) {
        //Filter duplicate
        if (this.limit_exceeds.indexOf(associate['id']) < 0) {
          this.limit_exceeds.push(associate['id']);
        }
        // console.log("Limit _exccede",this.limit_exceeds)
        this.limit_clearance = false;
      }
      else {
        // console.log("Entered")
        if (this.limit_exceeds.length == 0) {
          this.limit_clearance = true;
        }
        else {
          //Splice code
          let index = this.limit_exceeds.indexOf(associate['id']);
          this.limit_exceeds.splice(index, 1);

          if (this.limit_exceeds.length == 0) {
            this.limit_clearance = true;
          }
        }
      }
      // console.log("Associate",associate);
    }

  }

  claim_proceed: boolean = true;

  proceed_stats() {
    // console.log(this.assigned_claim_nos,this.selected_claim_nos)
    // console.log(this.selected_claim_nos.length ,',', this.assigned_claim_nos, this.selected_claim_nos.length,this.limit_exceeds  )
    if (this.selected_claim_nos.length >= this.assigned_claim_nos && this.selected_claim_nos.length != 0 && this.assigned_claim_nos != 0 && this.limit_exceeds.length == 0) {
      // console.log("P_Stats  -> True")
      this.claim_proceed = false;
    }
    else {
      // console.log("P_Stats  -> False")
      this.claim_proceed = true;
    }

  }

  //Verify Selected claims and associates
  public assign_claims() {

    this.Jarwis.check_claims(this.selected_claim_nos).subscribe(
      data => {
        // console.log("O/p",data)
        if (this.claim_assign_type == 'Manual') {
          this.assign_associates(data)
        } else if (this.claim_assign_type == 'Auto') {
          console.log('auto post');
          this.auto_post_claims(data);
          this.modalService.dismissAll()
        }
      },
      error => this.handleError(error)
    );

  }
  //associateCount : any [] = [];

  //Assign Claims to Associates
  public claims_assigned: Array<any> = [];
  public assign_status: Array<any> = [];
  public associates_error_status: boolean = true;
  public error_details: Array<any> = [];
  public null_assigned: boolean = true;
  public assign_associates(data) {
    let claim_numbers = this.selected_claim_nos;
    let assigned_associates = this.assigned_claims_details;

    console.log(assigned_associates);
    console.log(this.selected_claim_nos);
    console.log(assigned_associates);
    this.error_details = [];
    this.assign_status = [];

    this.associates_error_status = true;


    let unassigned_numbers = [];
    //Assign Logic
    for (let j = 0; j < assigned_associates.length; j++) {

      // this.assign_status.push({id:assigned_associates[j]['id'],to:assigned_associates[j]['value']});
      let assign = [];
      let count = 0;

      for (let x = 0; x < Object.keys(data.data).length; x++) {
        if (data.data[claim_numbers[x]] != null) {
          // console.log(data.data);
          if (data.data[claim_numbers[x]]['assigned_to'] == assigned_associates[j]['id'] && count < assigned_associates[j]['value']) {
            assign.push(claim_numbers[x]);
            count++;

            unassigned_numbers.push(claim_numbers[x]);

          }

        }
      }
      this.assign_status.push({ claims: assign, assigned: count, max: assigned_associates[j]['value'], assigned_to: assigned_associates[j]['id'] });;
      console.log(this.assign_status);
    }
    let missing = claim_numbers.filter(item => unassigned_numbers.indexOf(item) < 0);
    let new_claim = [];
    let reopen_claim = [];

    for (let z = 0; z < missing.length; z++) {
      if (data.data[missing[z]] == null) {
        new_claim.push(missing[z]);
      }
      else {
        reopen_claim.push(missing[z]);
      }
    }
    let cont = 0;
    for (let j = 0; j < assigned_associates.length; j++) {
      let count = this.assign_status.find(v => v.assigned_to == assigned_associates[j]['id']);
      count = Number(count['max']) - Number(count['assigned']);
      if (count != 0) {
        let assign = [];
        let loop_count = 0;
        for (let i = 0; i < count; i++) {
          if (new_claim[cont] != undefined) {
            assign.push(new_claim[cont]);
            cont++;
            loop_count++;
            ///Continue Here to update 'assign_status' and form it as 'claims_assigned' format  *************IMPORTANT-------******
          }
          // this.assigned_claims_details.find(v => v.id == id).value = event.target.value;
        }
        //Concat Claim Values
        if (loop_count != 0) {
          let array_data = this.assign_status.find(v => v.assigned_to == assigned_associates[j]['id']);
          let index = this.assign_status.findIndex(x => x.assigned_to == assigned_associates[j]['id']);
          let claims = array_data['claims'];
          let assigned_nos = array_data['assigned'];
          for (let z = 0; z < claims.length; z++) {
            assign.push(claims[z]);
          }
          array_data['claims'] = assign;
          array_data['assigned'] = Number(assigned_nos) + Number(loop_count);
          if (array_data['assigned'] > 0) {
            this.assign_status[index] = array_data;
          }
        }
      }
    }
    let unassigned_new_claims = [];
    if (cont < new_claim.length) {
      for (let i = cont; i < new_claim.length; i++) {
        unassigned_new_claims.push(new_claim[i]);
      }
    }

    //Final Check for Unassigned Claims and Associates
    let claim_array = [];
    let claim_name = [];
    for (let i = 0; i < this.assign_status.length; i++) {
      if (this.assign_status[i]['claims'].length == 0) {
        claim_array.push(this.assign_status[i]['assigned_to']);

        let name = this.associates_detail.find(v => v.id == this.assign_status[i]['assigned_to']);
        claim_name.push(name['firstname']);
        // let x=this.assigned_claims_details.find(v => v.id == id);
      }
    }

    if (claim_array.length != 0 || reopen_claim.length != 0 || unassigned_new_claims.length != 0) {
      this.error_details['associates'] = claim_array;
      this.error_details['reopen'] = reopen_claim;
      this.error_details['new_claims'] = unassigned_new_claims;
      this.error_details['associate_name'] = claim_name;
      this.associates_error_status = true;
    }
    else {
      this.associates_error_status = false;
    }
    let current_assigned = 0;
    let total_assigned = 0;
    this.assign_status.forEach(element => {
      current_assigned = element.assigned;
      total_assigned = Number(total_assigned) + Number(current_assigned);
    });

    if (total_assigned == 0) {
      this.null_assigned = true;
    }
    else {
      this.null_assigned = false;
    }
    console.log("Assigned", this.assign_status);
  }

  assigntype_reset;
  removeTextbox() {
    //this.assign_type().reset();
    this.assigntype_reset = this.assign_type(this.type);
    this.assigntype_reset = '';
    this.associateCount = '';
  }

  public work_order_notify(data) {
    this.assign_status = [];
    this.selected_associates = [];
    console.log(this.assign_status);
    this.selected_claim_nos = [];
    this.selected_claims = [];
    this.check_all = [];
    this.assigned_claims_details = [];
    this.assigned_data = [];
    this.workOrder.reset();
    this.pageChange(1, 'claim', null, null, null, null, null, null);
    this.toastr.successToastr('Work Order Created');
  }
  //Create Work Order
  public create_workorder() {
    console.log(this.assign_status);
    this.Jarwis.create_workorder(this.setus.getId(), this.workOrder.value, this.assign_status, 'followup').subscribe(
      data => this.work_order_notify(data),
      error => this.handleError(error)
    );
  }
  isChecked = true;
  checkedEvnt(val) {
    for (let i = 0; i < this.associates_detail.length; i++) {
      this.associates_detail[i].isChecked = val;
    }
    this.associateCount = '';
  }

  public clear_fields() {
    this.assigned_claims_details = [];
    this.workOrder.reset();
    this.formGroup.reset();
    this.associates_detail = [];
  }

  public ignore_error(type) {
    // alert(type);
    if (type == 'associates') {
      this.error_details['associates'] = [];
    }
    else if (type == 'reopen') {
      this.error_details['reopen'] = [];
    }
    else if (type == 'newclaims') {
      this.error_details['new_claims'] = [];
    }
    else if (type == 'assign_to_others') {
      let reopen = this.error_details['reopen']
      for (let x = 0; x < reopen.length; x++) {
        for (let i = 0; i < this.assign_status.length; i++) {
          let min = this.assign_status[i]['assigned'];
          let max = this.assign_status[i]['max'];
          if (Number(max) - Number(min) > 0) {
            let claims = this.assign_status[i]['claims'];
            claims.push(reopen[x]);
            this.assign_status[i]['claims'] = claims;
            this.assign_status[i]['assigned'] = Number(min) + 1;
            break;
          }
        }
      }

      this.error_details['reopen'] = [];
    }

    if (this.error_details['associates'] == '' && this.error_details['reopen'] == '' && this.error_details['new_claims'] == '') {
      this.associates_error_status = false;
    }
    console.log(this.assign_status);
  }

  sorting_name;
  order_list(type, table, sorting_name, sorting_method, createsearch, search) {
    this.sorting_name = type;

    if (this.sortByAsc == true) {
      this.sortByAsc = false;
      this.pageChange(this.pages, table, this.sortByAsc, type, sorting_name, sorting_method, null, search);
    } else {
      this.sortByAsc = true;
      this.pageChange(this.pages, table, this.sortByAsc, type, sorting_name, sorting_method, null, search);
    }

  }

  sort(property) {
    this.isDesc = false;
    this.column = property;
    let direction = this.isDesc ? 1 : -1;
    this.table_datas.sort(function (a, b) {
      if (a[property] < b[property]) {
        return -1 * direction;
      } else if (a[property] > b[property]) {
        return 1 * direction;
      } else {
        return 0;
      }
    });
  }

  desort(property) {
    this.isDesc = true;
    this.column = property;
    let direction = this.isDesc ? 1 : -1;
    this.table_datas.sort(function (a, b) {
      if (a[property] < b[property]) {
        return -1 * direction;
      } else if (a[property] > b[property]) {
        return 1 * direction;
      } else {
        return 0;
      }
    });
  }

  // desort(propertys) {
  //   this.isDesc = true;
  //   this.column = propertys;
  //   let descending = this.isDesc ? -1 : 1;
  //   this.table_datas.desort(function(a, b) {
  //     if (a[propertys] > b[propertys]) {
  //       return 1 * descending;
  //     } else if (a[propertys] < b[propertys]) {
  //       return -1 * descending;
  //     } else {
  //       return 0;
  //     }
  //   });
  // }


  createClaims_search(page: number, table, sort_data, sort_type, sorting_name, sorting_method, createsearch, search) {
    this.search = search;
    this.pageChange(page, table, sort_data, sort_type, sorting_name, sorting_method, createsearch, search);
  }


  //Table to list claims and Pagination
  upload_page: number;
  pages: number;
  total: number;
  claim_status_codes = [];
  claim_sub_status_codes = [];
  searchs;
  public pageChange(page: number, table, sort_data, sort_type, sorting_name, sorting_method, createsearch, search) {

    let searchs = this.search;

    this.searchValue = this.search;

    let page_count = 15;

    if (table == 'claim') {
      this.pages = page;
      if (sorting_name == 'null' && searchs == null) {
        console.log(sort_data);
        this.Jarwis.get_table_page(sort_data, page, page_count, sort_type, sorting_name, sorting_method, createsearch, search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      } else if (searchs == 'search') {
        console.log(this.createClaimsFind.value.dos);
        if (this.createClaimsFind.value.dos.startDate != null && this.createClaimsFind.value.dos.endDate != null) {
          console.log(this.createClaimsFind.controls.dos.value);
          this.createClaimsFind.value.dos.startDate = this.datepipe.transform(new Date(this.createClaimsFind.value.dos.startDate._d), 'yyyy-MM-dd');
          this.createClaimsFind.value.dos.endDate = this.datepipe.transform(new Date(this.createClaimsFind.value.dos.endDate._d), 'yyyy-MM-dd');
        }
        if (this.createClaimsFind.value.date.startDate != null && this.createClaimsFind.value.date.endDate != null) {
          console.log(this.createClaimsFind.controls.date.value);
          this.createClaimsFind.value.date.startDate = this.datepipe.transform(new Date(this.createClaimsFind.value.date.startDate._d), 'yyyy-MM-dd');
          this.createClaimsFind.value.date.endDate = this.datepipe.transform(new Date(this.createClaimsFind.value.date.endDate._d), 'yyyy-MM-dd');
        }
        if (this.createClaimsFind.value.bill_submit_date.startDate != null && this.createClaimsFind.value.bill_submit_date.endDate != null) {
          // console.log(this.createClaimsFind.controls.bill_submit_date.value);
          this.createClaimsFind.value.bill_submit_date.startDate = this.datepipe.transform(new Date(this.createClaimsFind.value.bill_submit_date.startDate._d), 'yyyy-MM-dd');
          this.createClaimsFind.value.bill_submit_date.endDate = this.datepipe.transform(new Date(this.createClaimsFind.value.bill_submit_date.endDate._d), 'yyyy-MM-dd');
        }
        this.Jarwis.get_table_page(sort_data, page, page_count, sort_type, this.sortByAsc, this.sorting_name, this.createClaimsFind.value, this.search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      } else {
        console.log(sort_data);
        this.Jarwis.get_table_page(sort_data, page, page_count, sort_type, this.sortByAsc, this.sorting_name, createsearch, this.search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      }

      // if(sorting_name == 'null' && searchs == null){
      //   this.Jarwis.get_workorder(filter,0,0,1,page,sort_type,sort_data,sorting_name,sorting_method,closedsearch,search).subscribe(
      //     data  => this.form_closedClaims_table(data,page),
      //     error => this.error_handler(error)
      //   );
      // }else if(searchs == 'search'){
      //   this.Jarwis.get_workorder(filter,0,0,1,page,sort_type,sort_data,this.closed_sorting_name,this.sortByAsc,this.closedClaimsFind.value,this.search).subscribe(
      //     data  => this.form_closedClaims_table(data,page),
      //     error => this.error_handler(error)
      //   );
      // }
      // else{
      //   this.Jarwis.get_workorder(filter,0,0,1,page,sort_type,sort_data,this.closed_sorting_name,this.sortByAsc,closedsearch,this.search).subscribe(
      //     data  => this.form_closedClaims_table(data,page),
      //     error => this.error_handler(error)
      //   );
      // }
    }
    /** Developer : Sathish
        Date : 09/01/2023
        Purpose : To get all Calims Table */
    else if (table == 'all_claim') {
      this.pages = page;
      if (sorting_name == 'null' && searchs == null) {
        this.Jarwis.all_claim_list(sort_data, page, page_count, sort_type, sorting_name, sorting_method, createsearch, search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      } else if (searchs == 'search') {
        if (this.allClaimsFind.value.dos !=null) {
          // console.log(this.allClaimsFind.value);
          this.allClaimsFind.value.dos.startDate = this.datepipe.transform(new Date(this.allClaimsFind.value.dos.startDate._d), 'yyyy-MM-dd');
          this.allClaimsFind.value.dos.endDate = this.datepipe.transform(new Date(this.allClaimsFind.value.dos.endDate._d), 'yyyy-MM-dd');
        }
        this.Jarwis.all_claim_list(sort_data, page, page_count, sort_type, this.sortByAsc, this.sorting_name, this.allClaimsFind.value, this.search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      } else {
        this.Jarwis.all_claim_list(sort_data, page, page_count, sort_type, this.sortByAsc, this.sorting_name, createsearch, this.search).subscribe(
          data => this.assign_page_data(data),
          error => this.handleError(error)
        );
      }
    }
    else if (table == 'upload') {
      this.upload_page = page;
      console.log(this.upload_page);
      this.Jarwis.get_upload_table_page(page, page_count).subscribe(
        data => this.handleResponse(data),
        error => this.handleError(error)
      );
    }
    else if (table == 'all') {
      console.log(table);
      this.pages = page;

      if (sorting_name == 'null') {
        this.Jarwis.claim_status_data_fork(sort_data, page, page_count, sort_type, this.setus.getId(), sorting_name, sorting_method, createsearch, search).subscribe(
          data => {
            this.assign_page_data(data[0]),
              this.assign_status_codes(data[1])
          },
          error => this.handleError(error)
        );
      } else {
        this.Jarwis.claim_status_data_fork(sort_data, page, page_count, sort_type, this.setus.getId(), this.sortByAsc, this.sorting_name, createsearch, search).subscribe(
          data => {
            this.assign_page_data(data[0]),
              this.assign_status_codes(data[1])
          },
          error => this.handleError(error)
        );
      }


      this.upload_page = page;
      this.Jarwis.get_upload_table_page(page, page_count).subscribe(
        data => this.handleResponse(data),
        error => this.handleError(error)
      );

    }
    else if (table == 'uploadall') {
      this.upload_page = page;
      this.Jarwis.get_upload_table_page(page, page_count).subscribe(
        data => this.handleResponse(data),
        error => this.handleError(error)
      );
    }
  }
  selected_status_code = [];
  selected_sub_status_code = [];
  //Assign Status codes
  public assign_status_codes(data) {
    this.claim_status_codes = data.status;
    this.claim_sub_status_codes = data.sub_status;
  }

  //Change values of substatus
  public change_sub_status_code($event) {
    this.selected_status_code = $event.target.value;
    this.selected_sub_status_code = this.claim_sub_status_codes[$event.target.value];
  }

  selected_filter_type = [];
  //set filter type
  public claim_filter_type($event) {
    this.selected_filter_type = $event.target.value;

    this.claim_sort_filter();
  }


  //sort with filter
  public claim_sort_filter() {
    this.pageChange(1, 'all', 'null', 'null', 'null', 'null', 'null', 'null')
  }


  //Assign Table data and `total values
  current_total;
  skip;
  total_row;
  skip_row;
  current_row;
  selected_claim_data;
  cwo_total;
  public assign_page_data(data) {
    this.table_datas = data.data;
    this.selected_claim_data = data.selected_claim_data;
    this.cwo_total = data.total;
    this.current_total = data.current_total;
    this.skip = data.skip + 1;

    this.skip_row = this.skip;
    this.current_row = this.skip + this.current_total - 1;
    this.total_row = data.total;
  }


  searchData: string;
  //Search filter function
  public sort_data(data) {
    this.pageChange(1, 'claim', data, 'searchFilter', 'null', 'null', 'null', 'null');
    this.searchData = data;
    //To reset the checklist
    this.check_all[1] = false;
    this.selected_claim_nos = [];

  }

  public sort_wo_data(data,) {
    // console.log(data);
    if (data == '') {
      this.get_workorder(null, null, null, 1, 1, null, null, 'null', 'null', null, null, null);
    }
    else {
      this.get_workorder('search', data, 0, 1, 1, null, null, 'null', 'null', null, null, null);
    }

  }

  public sort_table(data) {
    this.pageChange(1, 'claim', data, 'filters', 'null', 'null', 'null', 'null');
  }

  closed_sorting_name;

  closed_order_list(filter, from, to, type, sort_type, sort_data, sorting_name, sorting_method, closedsearch, workordersearch, search) {

    this.closed_sorting_name = sort_type;

    if (this.sortByAsc == true) {
      this.sortByAsc = false;
      this.get_workorder(filter, from, to, type, this.closed_pages, sort_type, this.sortByAsc, sorting_name, sorting_method, closedsearch, workordersearch, search);
    } else {
      this.sortByAsc = true;
      this.get_workorder(filter, from, to, type, this.closed_pages, sort_type, this.sortByAsc, sorting_name, sorting_method, closedsearch, workordersearch, search);
    }

  }


  workorder_search(filter, from, to, type, page, sort_type, sort_data, sorting_name, sorting_method, closedsearch, workordersearch, search) {
    this.search = search;
    console.log(page);
    this.get_workorder(filter, from, to, type, page, sort_type, this.sortByAsc, sorting_name, sorting_method, null, this.workOrderFind.value, search);
  }



  wo_page_number: number = 1;
  work_order_data = [];
  closed_page_number: number = 1;
  closed_data = [];

  wo_sorting_name;
  work_order_list(sort_type, sorting_name, sorting_method, search) {
    console.log(sort_type);

    let searchs = this.search;

    this.wo_sorting_name = sort_type;

    if (searchs == 'search') {

      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.get_workorder(null, null, null, 1, this.pages, sort_type, this.sortByAsc, sorting_name, sorting_method, null, null, search);
      } else {
        this.sortByAsc = true;
        this.get_workorder(null, null, null, 1, this.pages, sort_type, this.sortByAsc, sorting_name, sorting_method, null, null, search);
      }
    } else {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.get_workorder(null, null, null, 1, this.pages, this.sortByAsc, sort_type, sorting_name, sorting_method, null, null, search);
      } else {
        this.sortByAsc = true;
        this.get_workorder(null, null, null, 1, this.pages, this.sortByAsc, sort_type, sorting_name, sorting_method, null, null, search);
      }
    }
  }


  type;
  closed_pages;
  searchValue;
  public get_workorder(filter, from, to, type, page, sort_data, sort_type, sorting_name, sorting_method, closedsearch, workordersearch, search) {

    let searchs = this.search;

    this.searchValue = this.search;

    console.log(this.searchValue);

    let page_count = 15;

    this.type = filter;

    if (filter == null && from == null && to == null) {
      this.pages = page;

      let searchs = this.search;

      if (sorting_name == 'null' && searchs == null) {
        this.Jarwis.get_workorder(0, 0, 0, 1, page, sort_type, sort_data, sorting_name, sorting_method, closedsearch, workordersearch, search).subscribe(
          data => this.form_wo_table(data, page),
          error => this.error_handler(error)
        );
      } else if (searchs == 'search') {
        this.Jarwis.get_workorder(0, 0, 0, 1, page, sort_type, sort_data, this.wo_sorting_name, this.sortByAsc, null, this.workOrderFind.value, this.search).subscribe(
          data => this.form_wo_table(data, page),
          error => this.error_handler(error)
        );
      }
      else {
        this.Jarwis.get_workorder(0, 0, 0, 1, page, sort_type, sort_data, this.wo_sorting_name, this.sortByAsc, null, workordersearch, this.search).subscribe(
          data => this.form_wo_table(data, page),
          error => this.error_handler(error)
        );
      }
    }
    else if (filter == 'search') {
      this.pages = page;

      this.Jarwis.get_workorder(filter, from, 0, 1, page, sort_type, sort_data, sorting_name, sorting_method, null, null, search).subscribe(
        data => this.form_wo_table(data, page),
        error => this.error_handler(error)
      );
    } else if (filter == 'closedClaims') {
      this.closed_pages = page;

      if (sorting_name == 'null' && searchs == null) {
        this.Jarwis.get_workorder(filter, 0, 0, 1, page, sort_type, sort_data, sorting_name, sorting_method, closedsearch, workordersearch, search).subscribe(
          data => this.form_closedClaims_table(data, page),
          error => this.error_handler(error)
        );
      } else if (searchs == 'search') {
        this.Jarwis.get_workorder(filter, 0, 0, 1, page, sort_type, sort_data, this.closed_sorting_name, this.sortByAsc, this.closedClaimsFind.value, null, this.search).subscribe(
          data => this.form_closedClaims_table(data, page),
          error => this.error_handler(error)
        );
      }
      else {
        this.Jarwis.get_workorder(filter, 0, 0, 1, page, sort_type, sort_data, this.closed_sorting_name, this.sortByAsc, closedsearch, null, this.search).subscribe(
          data => this.form_closedClaims_table(data, page),
          error => this.error_handler(error)
        );
      }
    }


  }


  search;

  closedClaims_search(filter, from, to, type, sort_type, sort_data, sorting_name, sorting_method, closedsearch, workordersearch, search) {
    this.search = search;
    this.get_workorder(filter, from, to, type, this.closed_pages, sort_type, this.sortByAsc, sorting_name, sorting_method, this.closedClaimsFind.value, null, search);
  }





  wo_total: Number;
  closed_total: Number;
  w_total;
  w_current_total;
  w_skip;
  w_current_row;
  w_skip_rows;
  w_total_row;
  public form_wo_table(data, page_no) {
    // console.log(data);
    this.work_order_data = data.data;
    console.log(this.work_order_data);
    this.wo_total = data.count;
    this.wo_page_number = page_no;

    this.w_total = data.count;
    this.w_current_total = data.current_total;
    this.w_skip = data.skip + 1;

    this.w_skip_rows = this.w_skip;
    this.w_current_row = this.w_skip + this.w_current_total - 1;
    this.w_total_row = this.w_total;

  }
  totals; current_totals; skips; skip_rows; current_rows; total_rows;
  closed_claim_data;
  public form_closedClaims_table(data, page_no) {


    this.closed_data = data.data;
    console.log(this.closed_data);
    this.closed_claim_data = data.closed_claim_data;
    this.closed_total = data.count;
    this.closed_page_number = page_no;

    this.current_totals = data.current_total;
    this.skips = data.skip + 1;

    this.skip_rows = this.skips;
    this.current_rows = this.skips + this.current_totals - 1;
    this.total_rows = data.count;
  }

  wo_details = [];
  wo_name: string;
  wo_created: string;
  public get_wo_details(id, name, assigned) {
    this.loading = true;
    this.wo_details = []
    this.wo_name = name;
    this.wo_created = assigned;
    this.Jarwis.get_workorder_details(id).subscribe(
      data => this.wo_details_table(data),
      error => this.error_handler(error)
    );
  }

  public searchClaims;
  public workordersearch;
  public export_excel_files(type, table_name, search) {
    console.log(table_name);
    if (table_name == 'Create_work_order_claims') {
      this.searchClaims = this.createClaimsFind.value;
    } else if (table_name == 'Closed_claims') {
      this.searchClaims = this.closedClaimsFind.value;
    } else if (table_name == 'work_orders') {
      this.workordersearch = this.workOrderFind.value;
    }

    this.Jarwis.fetch_create_claims_export_data(this.setus.getId(), table_name, this.search, this.searchClaims, this.workordersearch).subscribe(
      data => this.export_handler.create_claim_export_excel(data),
      error => this.error_handler(error)
    );
  }

  public export_pdf_files(type, table_name) {
    let filter = 'all claims';
    let s_code = 'adjustment';

    this.Jarwis.fetch_create_claims_export_data_pdf(this.setus.getId(), table_name, this.search).subscribe(
      data => this.export_handler.sort_export_data(data, type, 'claim'),
      error => this.error_handler(error)
    );
  }


  public export_excel_wo_files(type, table_name) {
    console.log(this.searchValue);
    this.Jarwis.fetch_work_order_export_data(this.setus.getId(), table_name, this.searchValue, this.workOrderFind.value).subscribe(
      data => this.export_handler.create_wo_export_excel(data),
      error => this.error_handler(error)
    );
  }

  public export_pdf_wo_files(type, table_name) {
    let filter = 'all claims';
    let s_code = 'adjustment';

    this.Jarwis.fetch_work_order_export_data_pdf(this.setus.getId(), table_name).subscribe(
      data => this.export_handler.sort_export_data(data, type, 'claim'),
      error => this.error_handler(error)
    );
  }

  export_Excel_handler() {

  }

  public wo_details_table(data) {
    this.loading = false;
    this.wo_details = data.data;

    console.log(this.wo_details);
  }


  public export_files(type) {
    let filter = 'all claims';
    let s_code = 'adjustment';

    this.Jarwis.fetch_calim_export_data(filter, s_code, this.setus.getId()).subscribe(
      data => this.export_handler.sort_export_data(data, type, 'claim'),
      error => this.error_handler(error)
    );
  }


  public export_wo_files(type) {
    let filter = 'all claims';
    let s_code = 'adjustment';
    let wo_type = 1;
    this.Jarwis.fetch_wo_export_data(filter, s_code, wo_type, this.setus.getId()).subscribe(
      data => this.export_handler.ready_wo_export(data, type),
      error => this.error_handler(error)
    );

  }


  public wo_export_function(type) {
    this.export_handler.sort_export_data(this.wo_details, type, 'wo_detail');
  }


  public get_line_items(claim) {
    let stat = 0;

    for (let i = 0; i < this.line_item_data.length; i++) {
      let array = this.line_item_data[i];
      let x = array.find(x => x.claim_id == claim['claim_no']);

      if (x != undefined) {
        this.line_data = array;
        stat = 1;
      }
    }

    if (stat == 0) {
      this.Jarwis.get_line_items(claim).subscribe(
        data => this.assign_line_data(data),
        error => this.handleError(error)
      );
    }

    this.pageChange(1, 'claims', 'null', 'null', 'null', 'null', 'null', 'null');

  }

  line_item_data = [];
  assign_line_data(data) {
    this.line_item_data.push(data.data);
    this.line_data = data.data;

    console.log(this.line_data);
  }

  touch_count: number;

  reload_data(page) {

    this.pages = page;

    console.log(this.modalService.hasOpenModals());
    if (this.modalService.hasOpenModals() == false) {
      this.pageChange(this.pages, 'claim', null, null, 'null', 'null', null, 'null');

      for (let i = 0; i < this.selected_claim_data.length; i++) {
        let claim = this.selected_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind, 1);
        this.selected_claim_nos.splice(ind, 1);

      }

      let page_count = 15;

      this.Jarwis.get_table_page(null, this.pages, page_count, null, null, null, 'null', 'null').subscribe(
        data => this.assign_page_data(data),
        error => this.handleError(error)
      );

      this.checkboxes.forEach((element) => {
        element.nativeElement.checked = false;
      });

      this.formGroup.reset();

    }
  }

  reload_datas(page) {

    this.pages = page;

    console.log(this.modalService.hasOpenModals());
    if (this.modalService.hasOpenModals() == false) {
      this.pageChange(this.pages, 'claim', null, null, 'null', 'null', null, 'null');

      for (let i = 0; i < this.selected_claim_data.length; i++) {
        let claim = this.selected_claim_data[i]['claim_no'];
        let ind = this.selected_claim_nos.indexOf(claim);
        this.selected_claims.splice(ind, 1);
        this.selected_claim_nos.splice(ind, 1);

      }

      let page_count = 15;

      this.Jarwis.get_table_page(null, this.pages, page_count, null, null, null, null, null).subscribe(
        data => this.assign_page_data(data),
        error => this.handleError(error)
      );

      this.checkboxes.forEach((element) => {
        element.nativeElement.checked = false;
      });

      this.formGroup.reset();

    }

  }


  public unCheck() {
    this.checkboxes.forEach((element) => {
      element.nativeElement.checked = false;
    });
  }


  public un_selected(event, claim, index) {
    console.log(this.selected_claim_nos);

    if (claim == 'all' && event.target.checked == false) {



      // this.selected_claims=[];
      // this.selected_claim_nos=[];
    }
    else if (event.target.checked == false) {
      let ind = this.selected_claim_nos.indexOf(claim);
      this.selected_claims.splice(ind, 1);
      this.selected_claim_nos.splice(ind, 1);

    }

  }






  // fetch_count()
  // {
  //  let x = this.notify_service.get_tl();
  //  console.log("Get count",x);

  // }


  // get_touch_limit()
  // {
  //   this.Jarwis.get_practice_stats().subscribe(
  //     data =>this.set_prac_settings(data)
  //     );
  // }

  //
  // set_prac_settings(data)
  // {
  //   let prac_data=data.data;
  //   this.touch_count=prac_data.touch_limit;

  //   console.log(this.touch_count);

  // }

  delete_file(id) {
    //console.log(id);
    this.Jarwis.delete_upload_file(id, this.setus.getId()).subscribe(
      data => {
        this.pageChange(this.upload_page, 'upload', 'null', 'null', 'null', 'null', 'null', 'null');
        this.deleteMessage(data)
      },
      error => this.error_handler(error)
    );
  }

  deleteMessage(data) {
    console.log(data);
    if (data.message == 'success') {
      this.toastr.successToastr('File Deleted');
    } else if (data.message == 'failure') {
      this.toastr.errorToastr('Unable to delete Processed file');
    }


  }

  error_handler(error) {
    //console.log(error);

    if (error.error.exception == 'Illuminate\Database\QueryException') {
      this.toastr.warningToastr('File Cannot Be Deleted', 'Foreign Key Constraint');
    }
    else {
      this.toastr.errorToastr(error.error.exception, 'Error!');
    }


  }


  process_uld_file(id) {
    console.log(id);

    this.Jarwis.process_upload_file(id, this.setus.getId()).subscribe(
      data => { this.handlemessage(data), this.pageChange(1, 'upload', 'null', 'null', 'null', 'null', 'null', 'null') },
      error => this.error_handler(error)
    );

  }
  myOptions = {
    'placement': 'right',
    'hide-delay': 3000,
    'theme': 'light'
  }

  line: any;
  line2: any;
  public get_graph_stats() {
    this.Jarwis.get_graph_stats_fork(this.setus.getId()).subscribe(
      data => {
        this.assign_graph_values(data[0]),
        this.assign_table_values(data[1])
      },
      error => this.handleError(error)
    );

    // const isDisabled = (date: NgbDate, current: {month: number}) => date.day === 13;


    // this.Jarwis.get_claim_graph_stats(this.setus.getId()).subscribe(
    //   data  => ,
    //   error => console.log(error)
    //   );
  }

  // graph_data=[];
  // graph_data_year=[];
  insurance_table_data = [];
  insurance_total = [];
  insurance_per = [];

  status_data = [];
  status_total = [];
  status_perc = [];

  assoc_data = [];
  assoc_total = [];
  assoc_perc = [];

  assign_graph_values(data) {
    //console.log(data);
    let graph_data_year = [];
    let graph_data_flow = [];
    let graph_data = [];
    if (data.data.length == 0) {
      this.line = "";
    }
    else {
      graph_data_year = data.data['year'][0];
      graph_data_flow = data.data['data'];
      graph_data = data.daily;

      this.line = {
        "chart": {
          // "caption": "Store footfall vs Online visitors ",
          // "subCaption": "Last Year",
          "xAxisName": "Quarter",
          "yAxisName": "Claims",
          "base": "10",
          // "numberprefix": "",
          "theme": "fusion"
        },
        "categories": [
          {
            "category": [
              {
                "label": graph_data_year[0] + " Q1"
              },
              {
                "label": graph_data_year[1] + " Q2"
              },
              {
                "label": graph_data_year[2] + " Q3"
              },
              {
                "label": graph_data_year[3] + " Q4"
              },
              // {
              //     "label": "2019"
              // }

            ]
          }
        ],
        "dataset": [
          {
            "seriesname": "Assigned Claims",
            "data": [
              {
                "value": graph_data_flow[0][0]
              },
              {
                "value": graph_data_flow[1][0]
              },
              {
                "value": graph_data_flow[2][0]
              },
              {
                "value": graph_data_flow[3][0]
              }
            ]
          },
          {
            "seriesname": "Completed Claims",
            "data": [
              {
                "value": graph_data_flow[0][1]
              },
              {
                "value": graph_data_flow[1][1]
              },
              {
                "value": graph_data_flow[2][1]
              },
              {
                "value": graph_data_flow[3][1]
              }
            ]
          }
        ]
      }
    }


    // console.log("Ststus",this.status);

    if (data.daily['work'].length == 0) {
      this.line2 = [];
    }
    else {
      let value1 = [];
      let value2 = [];
      // console.log("Here");


      // console.log("Here222");
      // graph_data=[];
      value1 = [];
      value2 = [];
      let days = graph_data['dates'];
      let data_days = graph_data['days'];
      let data_data = graph_data['work'];
      // console.log("Chc",days,data_days,data_data)

      let graph_data2 = [];

      for (let i = 0; i < days.length; i++) {
        graph_data2.push({ "label": '' + days[i] + '' });

        let index = data_days.indexOf(days[i]);

        if (index >= 0) {
          value1.push({ "value": data_data[index][0] });
          value2.push({ "value": data_data[index][1] });
        }
        else {
          value1.push({ "value": '0' });
          value2.push({ "value": '0' });
        }

      }
      // console.log("Graph Check",graph_data2,value1,value2);

      // console.log("Here3333");
      this.line2 = {
        "chart": {
          // "caption": "Store footfall vs Online visitors ",
          // "subCaption": "Last Year",
          "xAxisName": "Date",
          "yAxisName": "Claims",
          "base": "10",
          "theme": "fusion"
        },
        "categories": [
          {
            "category": graph_data2

          }
        ],
        "dataset": [
          {
            "seriesname": "Assigned",
            "data": value1
          },
          {
            "seriesname": "Completed",
            "data": value2
          }
        ]
      }
    }


  }
  insurance_table_data_count: number;
  status_data_count: number;
  assoc_data_count: number;

  assign_table_values(data) {
    //  console.log("Asign",data.insurance);
    if (data.insurance.ins_data != undefined && data.insurance.ins_data.length != 0) {

      this.insurance_table_data = data.insurance.ins_data;
      this.insurance_total = data.insurance.total_data;
      this.insurance_per = data.insurance.total_per;
      this.insurance_table_data_count = data.insurance.ins_data.length;
    }
    else if (data.insurance.ins_data == undefined || data.insurance.ins_data.length == 0) {
      this.insurance_table_data_count = 0;
    }
    if (data.status.ins_data != undefined && data.status.ins_data.length != 0) {
      this.status_data = data.status.ins_data;
      this.status_total = data.status.total_data;
      this.status_perc = data.status.total_per;
      this.status_data_count = data.status.ins_data.length;
    }
    else if (data.status.ins_data == undefined || data.status.ins_data.length == 0) {
      this.status_data_count = 0;
    }
    if (data.associate.ins_data != undefined && data.associate.ins_data != 0) {
      this.assoc_data = data.associate.ins_data;
      this.assoc_total = data.associate.total_data;
      this.assoc_perc = data.associate.total_per;
      this.assoc_data_count = data.associate.ins_data.length;
    }
    else if (data.associate.ins_data == undefined || data.associate.ins_data == 0) {
      this.assoc_data_count = 0;
    }


    // console.log("I/p",data);
  }
  detailed_claims = [];
  public get_detailed(data) {
    this.detailed_claims = [];
    this.week_count = [];
    this.assoc_target_data = [];
    this.assoc_ach_data = [];

    this.Jarwis.fork_user_month_det(this.setus.getId(), data.assoc_id).subscribe(
      data => {
        this.set_detailed(data[0]),
          this.weekly_data(data[1])
      }
    );


    // console.log(data);
    // this.detailed_claims=[];
    // this.Jarwis.get_detailed(data.assoc_id).subscribe(
    //   data  => this.set_detailed(data),
    //   error => this.handleError(error)
    // );
  }



  public set_detailed(data) {
    // this.detailed_claims=[];
    //console.log("Detailed",data.claims);

    this.detailed_claims = data.claims;

  }

  week_count = [];
  assoc_target_data = [];
  assoc_ach_data = [];
  weekly_data(data) {
    //console.log(data);
    this.week_count = data.weeks;
    this.assoc_ach_data = data.ach_per;
    this.assoc_target_data = data.target;
  }

  user_name: string;
  ngOnInit() {

    // this.getclaims();
    this.getSearchResults();
    this.user_role_maintainer();
    this.formValidators();
    this.claimValidators();
    this.pageChange(1, 'all', null, null, 'null', 'null', 'null', 'null');
    // this.formGroup = new FormGroup({
    //   report_date: new FormControl('', [
    //     Validators.required
    //   ]),
    //   file: new FormControl('', [
    //     Validators.required
    //   ]) ,
    //   notes: new FormControl('', [
    //     Validators.required
    //   ])
    // });

    this.closedClaimsFind = this.formBuilder.group({
      dos: [],
      age_filter:[],
      claim_no: [],
      acc_no: [],
      patient_name: [],
      responsibility: [],
      total_charge: [],
      total_ar: new FormControl(null, [
        Validators.required,
        Validators.pattern(this.decimal_pattern),
      ]),
      rendering_provider:[],
      date:[],
      status_code: [],
      sub_status_code: [],
      payer_name:[],
      claim_note: [],
      insurance: [],
      prim_ins_name: [],
      prim_pol_id: [],
      sec_ins_name: [],
      sec_pol_id: [],
      ter_ins_name: [],
      ter_pol_id: [],
    });

    this.createClaimsFind = this.formBuilder.group({
      file_id: [],
      dos: [],
      age_filter:[],
      claim_no: [],
      acc_no: [],
      patient_name: [],
      responsibility: [],
      total_charge: [],
      total_ar: new FormControl(null, [
        Validators.required,
        Validators.pattern(this.decimal_pattern),
      ]),
      rendering_provider:[],
      payer_name:[],
      date:[],
      claim_note: [],
      insurance: [],
      prim_ins_name: [],
      prim_pol_id: [],
      sec_ins_name: [],
      sec_pol_id: [],
      ter_ins_name: [],
      ter_pol_id: [],
      bill_submit_date: [],
      denial_code: []
    });

    this.allClaimsFind = this.formBuilder.group({
      dos: [],
      age_filter:[],
      claim_no: [],
      acc_no: [],
      patient_name: [],
      responsibility: [],
      total_charge: [],
      total_ar: new FormControl(null, [
        Validators.required,
        Validators.pattern(this.decimal_pattern),
      ]),
      rendering_provider:[],
      payer_name:[],
      date:[],
      status_code: [],
      sub_status_code: [],
    });

    this.reassignedClaimsFind = this.formBuilder.group({
      dos: [],
      age_filter:[],
      claim_no: [],
      acc_no: [],
      patient_name: [],
      responsibility: [],
      total_ar: new FormControl(null, [
        Validators.required,
        Validators.pattern(this.decimal_pattern),
      ]),
      rendering_provider:[],
      date:[],
      status_code: [],
      sub_status_code: [],
      payer_name:[],      
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
      root_cause: new FormControl('', [
        Validators.required
      ]),
      error_type: new FormControl('', [
        Validators.required
      ])
    });

    this.autoclose_claim = this.formBuilder.group({
      file: ['', Validators.required]
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
    // this.workOrder = new FormGroup({
    //   workorder_name: new FormControl('', [
    // Validators.required
    // ]),
    // due_date: new FormControl('', [
    //   Validators.required
    // ]),
    // priority: new FormControl('', [
    //   Validators.required
    // ]),
    // wo_notes: new FormControl('', [
    //   Validators.required
    // ])
    // });
console.log(this.age_options);


    const debouncetime = pipe(debounceTime(700));
    this.search_data.valueChanges.pipe(debouncetime)
      .subscribe(result => this.sort_data(result)
      );

    this.wo_search_data.valueChanges.pipe(debouncetime)
      .subscribe(result => this.sort_wo_data(result)
      );

    this.filter_option.valueChanges
      .subscribe(result => this.sort_table(result)
      );
    // this.fetch_count();
    this.subscription = this.notify_service.fetch_touch_limit().subscribe(message => {
      this.touch_count = message
    });
    this.user_name = this.setus.getname();
    this.get_graph_stats();
    this.file_count();

  }

  user_role: Number = 0;
  user_role_maintainer() {
    let role_id = Number(this.setus.get_role_id());
    console.log(role_id);
    if (role_id == 5 || role_id == 3 || role_id == 2) {
      this.user_role = 2;
    }
    else if (role_id == 1) {
      this.user_role = 1;
    }
    else if (role_id == 16){
      this.user_role = 16;
    }
    else if (role_id == 11){
      this.user_role = 16;
    }
  }

  file_count() {
    this.Jarwis.get_file_ready_count().subscribe(res => {
      this.handlesuccess(res);
    },
      error => this.notify(error)
    )
  }

  get f() { return this.formGroup.controls; }
  get auto_cc() { return this.autoclose_claim.controls; }

  formValidators() {
    this.formGroup = this.formBuilder.group({
      report_date: ['', Validators.required],
      file: ['', Validators.required],
      notes: ['', Validators.required]
    });
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
  // onSubmit() {
  //   this.submitted = true;
  //   if (this.formGroup.invalid) {
  //     console.log('Error');
  //       return;
  //   }
  // }
  onClaimSubmit() {
    this.submitted = true;
    if (this.modalform.invalid) {
      console.log('Error');
      return;
    }

    this.processdata();
  }

  ngAfterViewInit() {
    if (this.touch_count == undefined) {
      this.touch_count = this.notify_service.manual_touch_limit();
      console.log(this.touch_count);
    }
  }



  ngOnDestroy() {
    // prevent memory leak when component destroyed
    this.subscription.unsubscribe();
    this.observalble.unsubscribe();
  }


  public sort_details(type) {

    if (this.sortByAsc == true) {
      this.sortByAsc = false;

      this.Jarwis.claims_order_list(type, this.setus.getId(), this.sortByAsc).subscribe(
        data => this.orderListResponse(data),
        error => this.notify(error)
      );

    } else {
      this.sortByAsc = true;

      this.Jarwis.claims_order_list(type, this.setus.getId(), this.sortByAsc).subscribe(
        data => this.orderListResponse(data),
        error => this.notify(error)
      );

    }


  }

  public orderListResponse(data) {

  }



//Get Status codes from Backend
public get_statuscodes()
{
  this.Jarwis.get_status_codes(this.setus.getId(),'all').subscribe(
    data  => {this.status_list = data['status'], this.process_codes(data)}
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

  public allClaim_status_code_changed(event:any)
  {
    if(event.value!=undefined)
    {
      let sub_status=this.sub_status_codes_data[event.value.id];
      let sub_status_option=[];
      console.log('sub_status_option');
      if(sub_status == undefined || sub_status =='' )
      {
        this.sub_options=[];
        this.allClaimsFind.patchValue({
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
            this.allClaimsFind.patchValue({
              sub_status_code: {id:this.sub_options[0]['id'],description:this.sub_options[0]['description']}
            });
          }
          else{
            this.allClaimsFind.patchValue({
              sub_status_code: ""
            });
          }
        }
      }
      // this.modified_stats.push(event);
    }
  }

  public closedClaims_status_code_changed(event:any)
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
  
  public reassignedClaims_status_code_changed(event:any)
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

  public sort_claims(type) {
    if (type == 'acct_no') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.acct_no.localeCompare(b.acct_no));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.acct_no.localeCompare(a.acct_no));
      }
    }
    if (type == 'claim_no') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.claim_no.localeCompare(b.claim_no));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.claim_no.localeCompare(a.claim_no));
      }
    }
    if (type == 'patient_name') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.patient_name.localeCompare(b.patient_name));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.patient_name.localeCompare(a.patient_name));
      }
    }
    if (type == 'dos_date') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.dos.localeCompare(b.dos));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.dos.localeCompare(a.dos));
      }
    }
    if (type == 'prim_ins_name') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.prim_ins_name.localeCompare(b.prim_ins_name));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.prim_ins_name.localeCompare(a.prim_ins_name));
      }
    }
    if (type == 'total_charges') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.total_charges.localeCompare(b.total_charges));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.total_charges.localeCompare(a.total_charges));
      }
    }
    if (type == 'total_ar') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.total_ar.localeCompare(b.total_ar));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.total_ar.localeCompare(a.total_ar));
      }
    }
    if (type == 'claim_Status') {
      if (this.sortByAsc == true) {
        this.sortByAsc = false;
        this.wo_details.sort((a, b) => a.claim_Status.localeCompare(b.claim_Status));
      } else {
        this.sortByAsc = true;
        this.wo_details.sort((a, b) => b.claim_Status.localeCompare(a.claim_Status));
      }
    }
  }
  // tooltipOptions= {
  //   'placement': 'right',
  //   'show-delay': '200',
  //   'tooltip-class': 'new-tooltip-class',
  //   'background-color': '#9ad9e4'
  //   };

  getSearchResults(): void {
    this.Jarwis.get_payer_name().subscribe(sr => {
      this.searchResults = sr['payer_names'];
      console.log(this.searchResults);
    });
  }
  searchOnKeyUp(event) {
		let input = event.target.value;
		console.log('event.target.value: ' + input);
		console.log('this.searchResults: ' + this.searchResults);
		if (input.length > 0) {
			this.results = this.searchFromArray(this.searchResults, input);
		}
    else{
      this.isValueSelected = false;
    }    
	}
	searchFromArray(arr, regex) {
		let matches = [], i;
		for (i = 0; i < arr.length; i++) {
			if (arr[i].match(regex)) {
				matches.push(arr[i]);
			}
		}
		console.log('matches: ' + matches);
		return matches;
	};
  onselectvalue(value) {
    if(value !='' || value !=null){
      this.isValueSelected = true;
    this.selected_val = value;
    }
    else{
      this.selected_val = '';      
      this.isValueSelected = false;
    }
  }
}
