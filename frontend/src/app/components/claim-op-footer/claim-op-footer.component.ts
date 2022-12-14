import { Component, OnInit,TemplateRef,ViewEncapsulation,Input } from '@angular/core';
import { JarwisService } from '../../Services/jarwis.service';
import { LoadingBarService } from '@ngx-loading-bar/core';
import { SetUserService } from '../../Services/set-user.service';
import { FormControl, FormGroup, Validators } from "@angular/forms";
import { NotesHandlerService } from '../../Services/notes-handler.service';
import { Subscription } from 'rxjs';
import { Router } from '@angular/router';
import { IfStmt } from '@angular/compiler';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { ToastrManager } from 'ng6-toastr-notifications';
import { FormsModule } from '@angular/forms';
import {NgbDatepickerConfig, NgbCalendar, NgbDate, NgbDateStruct,NgbDateParserFormatter} from '@ng-bootstrap/ng-bootstrap';
@Component({
  selector: 'app-claim-op-footer',
  templateUrl: './claim-op-footer.component.html',
  styleUrls: ['./claim-op-footer.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ClaimOpFooterComponent implements OnInit {
    @Input() tab; //Note you need to import Input from @angular/core  

    //console.log(tab);
//   @Input() bankName=[];

//   ngOnChanges() {
//     console.log("click",this.bankName);
//     // this.set_current_tab(this.bankName['claim_no']);
// }
  claimeds : any;
  fclaims : any;
  formGroup: FormGroup;
  subscription: Subscription;
  subscription2: Subscription;
  subscription3: Subscription;
  subscription4: Subscription;
  public submitted_claims:string[]=[];

  public status_codes_data:Array<any> =[];
  public sub_status_codes_data:string[];
  public options;
  public sub_options;
  public selected_claim_data:string[];
  public associates_options;
  minDate = undefined;
  constructor(
    private Jarwis: JarwisService,
    private setus: SetUserService,
    private loadingBar: LoadingBarService,
    private notes_handler:NotesHandlerService,
    public router: Router,
    private modalService: NgbModal,
    public toastr: ToastrManager,
    private date_config  : NgbDatepickerConfig,

  ) {
    this.subscription=this.notes_handler.get_current_tab().subscribe(message => {this.set_current_tab(message);
    });
     this.subscription2=this.notes_handler.get_claim_details().subscribe(message => { this.set_status_codes(message) });
     this.subscription3=this.notes_handler.get_notes().subscribe(message => { this.recieve_values(message) });
     this.subscription4=this.notes_handler.process_get_notes().subscribe(message => { this.process_recieve_values(message) });


     const current = new Date();
     this.minDate = {
       year: current.getFullYear(),
       month: current.getMonth() + 1,
       day: current.getDate()
     };

  }

  //Get Status codes from Backend
  public get_statuscodes()
  {
    this.Jarwis.get_status_codes(this.setus.getId(),'all').subscribe(
      data  => this.process_codes(data)
    );
  }

  //Get Associates from backend
  public get_associates()
  {
    this.Jarwis.get_associates(this.setus.getId()).subscribe(
      data  => this.process_associates(data)
    );
  }

model;

public clear(): void {
  this.model = undefined;
}




  //Set Associates Value

  public process_associates(data:any)
  {
     console.log("Assoc",data);
    if(data.type == 'Assign')
    {
      let index=this.associates_options.findIndex(v => v.id == data.associate);
      this.formGroup.patchValue({
        associates: this.associates_options[index],
          });
    }
    else{
      let associates=data.data;
      let associate_option_data=[];
      if(associates!=undefined || associates != '')
      {
      for(let i=0;i<associates.length;i++)
      {
        associate_option_data.push({id: associates[i]['id'], description: associates[i]['firstname'] +' '+ associates[i]['lastname'] })
      }
      }
      this.associates_options=associate_option_data;
      }
  }



  //Set selected claim_details
  public set_status_codes(data:any)
  {
    
    //alert(data);
    this.selected_claim_data=[];
    this.selected_claim_data=data;
    console.log(this.selected_claim_data);
    if(this.note_update_monitor == true)
    {
     // alert('dd0');
      this.assign_codes(data);
    }

     this.assign_codes(data);

  }
  selected_claim;
  public assign_codes(data)
  {
    console.log(data);

    console.log(this.selected_claim_data);
        
    if(this.selected_claim_data['claim_closing'] == 1)
    {
      if(this.selected_claim_data['claim_closing'] == 1)
      {
        this.formGroup.patchValue({
          closed: 1
          });
       
        //alert(this.selected_claim_data['status_code']);
        if(this.selected_claim_data['status_code'] !='' && this.selected_claim_data['status_code'] != undefined && this.selected_claim_data['status_code'] != null )
        {  
          console.log(this.sub_status_codes_data);
          console.log(this.selected_claim_data['status_code']);
          let status_id=this.status_codes_data.find(v => v.id == this.selected_claim_data['status_code']);
          console.log(status_id);
          let substatus_id=this.sub_status_codes_data[status_id['id']];
          console.log(substatus_id);
          let data={type:'initialisation',status_code:status_id['id'],sub_status_id:substatus_id};
          console.log(data);
          this.status_code_changed(data);
          let associate_data={type:'Assign',associate:this.selected_claim_data['followup_associate']};

            this.process_associates(associate_data);
          
              // this.formGroup.patchValue({
              //   status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
              //   followup_date: {year:this.selected_claim_data['followup_date'][2],month:this.selected_claim_data['followup_date'][1],day:this.selected_claim_data['followup_date'][0]},
              // });
              console.log(status_id['id']);
              console.log(status_id['status_code']);
              console.log(status_id['description']);

              if(Array.isArray(this.selected_claim_data['followup_date'])){
              console.log('It is an array');
              console.log(this.selected_claim_data['followup_date']);
              if(this.selected_claim_data['followup_date'][2] == 1970){
                this.formGroup.patchValue({
                    status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                    followup_date: '',
                    closed: 1
                  }); 
              }else{
                this.formGroup.patchValue({
                    status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                    followup_date: {year:this.selected_claim_data['followup_date'][2],month:this.selected_claim_data['followup_date'][1],day:this.selected_claim_data['followup_date'][0]},
                    closed: 1
                  });
              }
              } else {
              console.log('Not an array');
              console.log(this.selected_claim_data['followup_date']);
              var str = this.selected_claim_data['followup_date'];
              console.log(str); 
              if(str == null){
                this.selected_claim_data['followup_date'] = ''; 
              }else{
                this.selected_claim_data['followup_date'] = str.split("-"); 
              }
              console.log(this.selected_claim_data['followup_date']);
              if(Number(this.selected_claim_data['followup_date'][0]) == 1970){
                  this.formGroup.patchValue({
                    status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                    followup_date: '',
                    closed: 1
                  });
                }else{
                  this.formGroup.patchValue({
                    status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                    followup_date: {year:Number(this.selected_claim_data['followup_date'][0]),month:Number(this.selected_claim_data['followup_date'][1]),day:Number(this.selected_claim_data['followup_date'][2])},
                    closed: 1
                  });
                } 
              }


          //}
        }

      }
      this.claim_closed=true;
      
      this.formGroup.controls['status_code'].enable();
      this.formGroup.controls['sub_status_code'].enable();
      this.formGroup.controls['followup_date'].enable();
      if(this.router.url == '/audit')
      {
        this.formGroup.controls['status_code'].disable();
        this.formGroup.controls['sub_status_code'].disable();
        this.formGroup.controls['followup_date'].disable();
        this.formGroup.controls['associates'].disable();
      }

    }
    else
    {
      this.claim_closed =false;
      this.formGroup.controls['status_code'].enable();
      this.formGroup.controls['sub_status_code'].enable();
      this.formGroup.controls['followup_date'].enable();
      if(this.router.url == '/audit')
      {
        this.formGroup.controls['associates'].enable();
      }
      if(this.selected_claim_data['status_code'] !='' && this.selected_claim_data['status_code'] != undefined && this.selected_claim_data['status_code'] != null )
      {
        //  alert('test '+this.selected_claim_data['followup_date']);
        let status_id=this.status_codes_data.find(v => v.id == this.selected_claim_data['status_code']);
        let substatus_id=this.sub_status_codes_data[status_id['id']];
        let data={type:'initialisation',status_code:status_id['id'],sub_status_id:substatus_id};
        this.status_code_changed(data);
        let associate_data={type:'Assign',associate:this.selected_claim_data['followup_associate']};

          this.process_associates(associate_data);
          if(this.selected_claim_data['followup_date'] == null){
            this.formGroup.patchValue({
              status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
              followup_date: '',
              closed: 0
            });
          }else{
            if(Array.isArray(this.selected_claim_data['followup_date'])){
             console.log('It is an array');
             console.log(this.selected_claim_data['followup_date']);
             if(this.selected_claim_data['followup_date'][2] == 1970){
               this.formGroup.patchValue({
                  status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                  followup_date: '',
                  closed: 0
                }); 
             }else{
               this.formGroup.patchValue({
                  status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                  followup_date: {year:this.selected_claim_data['followup_date'][2],month:this.selected_claim_data['followup_date'][1],day:this.selected_claim_data['followup_date'][0]},
                  closed: 0
                });
             }
            } else {
             console.log('Not an array');
             console.log(this.selected_claim_data['followup_date']);
             var str = this.selected_claim_data['followup_date']; 
             console.log(str);
             if(str == null){
               this.selected_claim_data['followup_date'] = ''; 
             }else{
               this.selected_claim_data['followup_date'] = str.split("-"); 
             }
             console.log(this.selected_claim_data['followup_date']);
             if(Number(this.selected_claim_data['followup_date'][0]) == 1970){
                this.formGroup.patchValue({
                  status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                  followup_date: '',
                  closed: 0
                });
              }else{
                this.formGroup.patchValue({
                  status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
                  followup_date: {year:Number(this.selected_claim_data['followup_date'][0]),month:Number(this.selected_claim_data['followup_date'][1]),day:Number(this.selected_claim_data['followup_date'][2])},
                  closed: 0
                });
              } 
            }

            // const myArray = ["A", "B", "C"]
            // console.log(isStringArray(myArray)); 


            // if(this.selected_claim_data['followup_date']){

            //   this.formGroup.patchValue({
            //     status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
            //     followup_date: {year:this.selected_claim_data['followup_date'][2],month:this.selected_claim_data['followup_date'][1],day:this.selected_claim_data['followup_date'][0]},
            //     closed: 0
            //   });
            // }else{

            //   this.formGroup.patchValue({
            //     status_code: {id:status_id['id'],description:status_id['status_code']+'-'+status_id['description']},
            //     followup_date: {year:this.selected_claim_data['followup_date'][2],month:this.selected_claim_data['followup_date'][1],day:this.selected_claim_data['followup_date'][0]},
            //     closed: 0
            //   });
            // }

          }

          console.log(this.formGroup.value['status_code']);
        //}
    }

    }
   }

  //Process and Display Claim Codes
  public process_codes(data:any)
  {
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
    this.options=status_option;

    if(this.router.url=='/rcm')
    {
      this.note_validation=true;

    }
    if(this.router.url=='/claims')
    {
      this.note_validation=true;

    }
  }

  //Handle Claim Code Changed event
  public status_code_changed(event:any)
  {

 if(event.value!=undefined)
 {
    let sub_status=this.sub_status_codes_data[event.value.id];

    let sub_status_option=[];

    console.log('sub_status_option');

    if(sub_status == undefined || sub_status =='' )
    {
      this.sub_options=[];
      this.formGroup.patchValue({
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
          this.formGroup.patchValue({
            sub_status_code: {id:this.sub_options[0]['id'],description:this.sub_options[0]['description']}
          });
        }
        else{
          this.formGroup.patchValue({
            sub_status_code: ""
          });
        }
      }
    }
    this.modified_stats.push(event);
  }
  if(event.type=="initialisation")
  {
    let sub_status=this.sub_status_codes_data[event.status_code];
    let sub_status_selected=event.sub_status_id;
    let sub_status_option=[];
    for(let i=0;i<sub_status.length;i++)
    {
      if(sub_status[i]['status']==1)
      {
        sub_status_option.push({id: sub_status[i]['id'], description: sub_status[i]['status_code'] +'-'+ sub_status[i]['description'] });
      }

      // this.formGroup.patchValue({
      //   sub_status_code: sub_status[index]['id']
      //  });
      }


      this.sub_options=sub_status_option;

      //console.log("Sub Option",this.sub_options,this.selected_claim_data['substatus_code']);
      // console.log("Event",event,this.selected_claim_data,this.sub_options);
      // this.formGroup.get('sub_status_code').setValue(sub_status_selected[0]['id']);

      let index=this.sub_options.findIndex(x => x.id == this.selected_claim_data['substatus_code']);

      // console.log("SStstus",this.sub_options,sub_status,sub_status[index]['id']);

      //  this.formGroup.get('sub_status_code').setValue({id:this.selected_claim_data['substatus_code']});

       this.formGroup.patchValue({
        sub_status_code: {id:this.selected_claim_data['substatus_code'],description:this.sub_options[index]['description']}
      });
      }
      }

 //Configuration of Dropdown Search
 config = {
  displayKey:"description",
  search:true,
  result:'single'
 }
  public active_tab;
  public notes_details:Array <any>=[];
  public process:Array <any>=[];
  public note_validation:boolean=false;
  public tab_status:boolean=false;
  //Recieve selected data form other Component and Set data of Selected tab
  public set_current_tab(claim:any)
  {
    this.active_tab=claim;
    console.log(this.active_tab);
    console.log(this.notes_details);
    // console.log("Active tab",this.active_tab)


    if(!this.notes_details.find(x => x.claim_no ==  this.active_tab) )
    {
      this.note_validation=false;
      console.log(this.note_validation);
    }
    else
    {
      this.note_validation=true;
    }



    if(this.submitted_claims.includes(this.active_tab))
    {
      this.tab_status=true;
    if(this.submit_tab_types[this.active_tab] == 'Audit')
    {
      this.message="Audit Processing Completed";
    }
    else{
      this.message="Moved to Audit Processing";
    }

    // this.toastr.successToastr('Claim Processed', this.message) ;
      }
      else
      {
        // When its not undefined
        // console.log("Claim_datra",this.claim_data);
        this.get_note_details();
      }
  }

  //Handle Validation of Notes
  public recieve_values(data:any)
  {
     console.log("Notse data",data);
    if(this.notes_details.find(x => x.claim_no == this.active_tab))
    {
      let claim=this.notes_details.find(x => x.claim_no == this.active_tab);
      let index=this.notes_details.findIndex(x => x == claim);
      this.notes_details[index]=data;

    }
    else{
      this.notes_details.push(data);
    }

    if(!this.notes_details.find(x => x.claim_no ==  this.active_tab) )
    {
      this.note_validation=false;
    }
    else
    {
      this.note_validation=true;
    }
  }

  public process_recieve_values(data:any)
  {
     console.log("Notse data",data);
    if(this.process.find(x => x.claim_no == this.active_tab))
    {
      let claim=this.process.find(x => x.claim_no == this.active_tab);
      let index=this.process.findIndex(x => x == claim);
      this.process[index]=data;

    }
    else{
      this.process.push(data);
    }

    if(!this.process.find(x => x.claim_no ==  this.active_tab) )
    {
      this.note_validation=false;
    }
    else
    {
      this.note_validation=true;
    }
  }

  public handleError(error)
    {
      console.log(error);
    }

  public check_updates()
  {
    if(this.note_validation==true)
    {
      if(this.router.url != '/rcm')
      {
        let selected_details=this.notes_details.find(x => x.claim_no ==  this.active_tab);
        let user_notes=selected_details['note'];
        let user=selected_details['user'];
        let input_type=selected_details['type'];
        let claim_details=selected_details['claim'];
        // if(input_type=='claim_create')
        //     {
              this.Jarwis.check_notes_update(claim_details,'all',this.claim_data).subscribe(
                data  => this.set_note_update_val(data),
                error => this.handleError(error)
                );
            // }
            // else if(input_type=='create_qcnotes')
            // {
            //   this.Jarwis.check_notes_update(claim_details,'qc_notes',this.claim_data['claim_note']).subscribe(
            //     data  => this.set_note_update_val(data),
            //     error => console.log(error)
            //     );
            // }
      }

    }

  }

  //Save reference for later use
  confirm_modal: string | TemplateRef<any>;
  save_ref(ref)
  {
    this.confirm_modal=ref;
  }

  note_update_monitor:boolean=false;

  set_note_update_val(data)
  {
    console.log("Its goods",data);
    if(data.updated == true)
    {
      this.note_update_monitor = true;
    this.open(this.confirm_modal);

    if(data.cc == true)
    {
      this.notes_handler.refresh_notes('all');
    }

    else
    {
      this.notes_handler.refresh_notes('notes');
    }
     this.get_note_details();
    }
    else
    {
      this.note_update_monitor= false;
      this.followup_process();
      this.finish_followup();
      this.followup_process_notes();
    }
  }

  closeResult:string;
  open(content) {
    this.modalService.open(content, { windowClass: 'dark-modal_footer' });
  }

  public followup_process_notes()
  {
          let selected_details=this.process.find(x => x.claim_no ==  this.active_tab);
          let user_notes=selected_details['note'];
          let user=selected_details['user'];
          let input_type=selected_details['type'];
          let claim_details=selected_details['claim'];
          // this.Jarwis.claim_note(this.setus.getId(),user_notes,claim_details,'claim_create').subscribe(
          //   data  => this.response_handler(data,'followup'),
          //   error => this.handleError(error)
          // );
          this.Jarwis.process_note(this.setus.getId(), user_notes, claim_details, 'processcreate', 'followup').subscribe(
            data  => this.response_handler(data,'followup'),
            error => this.handleError(error)
          );
  }

  //Create Notes after Validation of Notes and Status Code
  public followup_process()
  {
    if(this.router.url!='/rcm')
    {
    if(this.note_validation==true)
    {
      let selected_details=this.notes_details.find(x => x.claim_no ==  this.active_tab);
      let user_notes=selected_details['note'];
      let user=selected_details['user'];
      let input_type=selected_details['type'];
      let claim_details=selected_details['claim'];

      console.log(selected_details);
      if(input_type=='claim_create')
      {
          this.Jarwis.claim_note(this.setus.getId(),user_notes,claim_details,'claim_create').subscribe(
            data  => this.response_handler(data,'followup'),
            error => this.handleError(error)
          );
      }

      else if(input_type=='create_qcnotes'){
          this.Jarwis.qc_note(this.setus.getId(),user_notes,claim_details,'create_qcnotes').subscribe(
            data  => this.response_handler(data,'audit'),
            error => this.handleError(error)
          );
      }
      else if(input_type=='create_client_notes')
      {
          this.Jarwis.client_notes(this.setus.getId(),user_notes,claim_details,'client_create').subscribe(
            data  => this.response_handler(data,'CA'),
            error => this.handleError(error)
          );
      }
    }
  }
  }

  //Set Status-code details for claims and Moving claims to next Level of processing
  public finish_followup()
  {
    let input_type:any;
    let claim_details:any;
    if(this.router.url!='/rcm')
    {
      console.log('rcm');

    let selected_details=this.notes_details.find(x => x.claim_no ==  this.active_tab);

     // console.log(x.claim_no);
      console.log(this.active_tab);

     console.log(selected_details);
     input_type=selected_details['type'];
     claim_details=selected_details['claim'];
    }
    else{
        console.log('rcm_create');
       input_type='rcm_create';
       claim_details=this.selected_claim_data;
    }
    console.log(claim_details);
    console.log('finish_followup');
    console.log(this.formGroup.value);
    // console.log("i\p",input_type,this.formGroup.value,input_type,claim_details)
    this.Jarwis.finish_followup(this.setus.getId(),this.formGroup.value,claim_details,input_type).subscribe(
    data  => this.handle_resources(data,this.formGroup.value),
    error => this.handleError(error)
    );
  }
  public submit_tab_types:Array <any>=[];
  public message:string;

  //Set Status code data and Handle claim status
  public handle_resources(data:any,form_value)
  {
    console.log(data.message);

    if(data.message==1)
    {
      this.submitted_claims.push(this.active_tab);
      this.tab_status=true;

      // this.formGroup.get('sub_status_code').setValue(form_value.sub_status_code);
      // this.formGroup.patchValue({
      //   status_code: {disabled: true},
      //   sub_status_code: {disabled: true},
      //   associates: {disabled: true},
      //   followup_date: {disabled: true}
      // });

      if(data.type == "Followup")
      {
        this.submit_tab_types[this.active_tab]=data;
        this.message="Claim Processed";

      }
      else if(data.type== "Audit"){

      this.submit_tab_types[this.active_tab]=data;
      this.message="Audit Completed";
      }
      else if(data.type== "CA")
      {
        this.submit_tab_types[this.active_tab]=data;
      this.message="Claim Processed";
      }
      this.toastr.successToastr( this.message) ;

    }
    this.modalService.hasOpenModals() == false;
  }

  //Handling Response after creation of followup trigerring functoions in other components
  public response_handler(data,type)
  {
    this.notes_handler.set_response(data,type);
  }

  claim_data:Array<any> =[];
  public set_note_details(data)
  {

    this.claim_data=data;

    console.log(this.claim_data);
    // console.log("Claim_datra",this.claim_data);
  }

  get_note_details()
  {
    console.log("active",this.active_tab)
    this.Jarwis.get_note_details(this.active_tab).subscribe(
      data  => this.set_note_details(data)
    );
  }

  modified_stats=[];
  alert_message:string;
  save_alert_function(alert_mod,conf_mod)
  {
    if(this.router.url == '/followup')
    {
      // console.log('inside',this.modified_stats);
      if(this.modified_stats.length > 0)
      {
        this.check_updates();
        this.save_ref(conf_mod);
      }
      else{
         this.alert_message='Status not changed. Do you wish to continue?';
        this.open(alert_mod);

      }
    }
    else{
      if(this.modified_stats.length > 1 )
      {
        this.check_updates();
        this.save_ref(conf_mod);
      }
      else{
        this.alert_message='Status not changed. Do you wish to continue?';
        this.open(alert_mod);
      }
    }
  }

claim_closed:boolean=false;
  closed_event(event)
  {
    let checked = event.currentTarget.checked;

    if(checked == true)
    {
      this.claim_closed=true;
      this.formGroup.controls['status_code'].enable();
      this.formGroup.controls['sub_status_code'].enable();
      this.formGroup.controls['followup_date'].disable();
      this.modified_stats.push('closed');

      if(this.router.url == '/audit')
      {
        this.formGroup.controls['associates'].enable();
      }

    }
    else{
      this.claim_closed =false;

      this.formGroup.controls['status_code'].enable();
      this.formGroup.controls['sub_status_code'].enable();
      this.formGroup.controls['followup_date'].enable();

      if(this.router.url == '/audit')
      {
        this.formGroup.controls['associates'].enable();
      }

      if(this.modified_stats.length >0)
      {
        this.modified_stats.pop();
      }

    }

  }
  public followup_closed()
  {
this.formGroup.controls['closed'].disable();
  }
  public followtoggle(){
// this.formGroup.controls['followup_date'].reset();
this.formGroup.controls['closed'].enable();
  }
get_values()
{
  //alert('te');
      //Test of fork join
      this.Jarwis.get_process_associates(this.setus.getId(),this.active_tab,this.router.url).subscribe(
        data  => {
          this.process_codes(data[0]),
          this.process_associates(data[1]),
          // this.set_note_details(data[2]),
          this.assign_codes(data);
        }
      );
}



  ngOnInit() {

    this.get_values();
    // this.get_associates();
    // this.get_statuscodes();
   //Observables for claim selection
   if(this.router.url == '/followup'){
     //alert(this.router.url);
      this.formGroup = new FormGroup({
        status_code: new FormControl('', [
          Validators.required
        ]),
        sub_status_code: new FormControl('', [
          Validators.required
        ]) ,
        associates: new FormControl('', [
          Validators.required
        ]),
        followup_date: new FormControl('',[]),
        closed: new FormControl('', [
        ])
      }); 
   }else{
      this.formGroup = new FormGroup({
        status_code: new FormControl('', [
          Validators.required
        ]),
        sub_status_code: new FormControl('', [
          Validators.required
        ]) ,
        associates: new FormControl('', [
          Validators.required
        ]),
        followup_date: new FormControl('',[
          Validators.required
        ]),
        closed: new FormControl('', [
        ])
      });  
   }
    

    console.log(this.formGroup.value);

    if(this.router.url == '/followup')
    {
      if(this.tab == 'allocated'){
        console.log(this.tab);
        this.formGroup.removeControl('associates')
      }else{
        console.log('this.tab');
        this.formGroup.removeControl('associates')
      }
    }


  }

//   ngAfterViewInit()
//   {
// this.get_statuscodes();
//   }
ngOnDestroy(){
  this.subscription.unsubscribe();
  this.subscription2.unsubscribe();
  this.subscription3.unsubscribe();

}
claimed() {
  return this.claimeds;
}
fclaim() {
  return this.fclaims;
}

}
