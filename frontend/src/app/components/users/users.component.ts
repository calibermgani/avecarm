import { Component, OnInit } from '@angular/core';
import { JarwisService } from '../../Services/jarwis.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {FormControl, FormGroup, Validators,FormArray,FormBuilder } from "@angular/forms";
import { SetUserService } from '../../Services/set-user.service';
import { ToastrManager } from 'ng6-toastr-notifications';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css']
})
export class UsersComponent implements OnInit {
  userEdit: FormGroup;
  submitted = false;

  closeResult:any;
  aims_list = [];
  user_details_list=[];
  user_profile_det=[];
  user_address_det=[];
  user_work_profiles=[];
  roles:string[];
  process_fields : FormGroup;
  practice=[];

  public modal;

  constructor(private Jarwis: JarwisService,private formBuilder:FormBuilder,
    private modalService: NgbModal,private setus: SetUserService,public toastr: ToastrManager) { }

    public open(content) {
     this.modal = this.modalService.open(content, { centered: true ,windowClass:'custom-class' });
     this.modal.result.then((result) => {
        this.closeResult = `Closed with: ${result}`;
      }, (reason) => {
        this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
      });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return  `with: ${reason}`;
    }
  }

get_users_list()
{
  // this.Jarwis.get_practice_user_list(this.setus.getId()).subscribe(
  //   data => this.set_user_list(data)
  //   );


  this.Jarwis.get_users_list(this.setus.getId()).subscribe(
    data => this.set_user_list(data)
    );
  this.modal.dismiss();
}

get_aimsusers_list()
{
  let data={token:'1a32e71a46317b9cc6feb7388238c95d',
    department_id:1};
  this.Jarwis.get_aimsusers_list(data).subscribe(
    res => {
      console.log(res);
;      this.set_user_list(res);}
    );
  this.modal.dismiss();
}

set_user_list(data)
{
  console.log('Set User List',data);
  this.user_details_list = data.data;
  console.log('length',this.user_details_list.length);

  this.aims_list = data.data;
  this.user_profile_det = data.profile;
  this.user_address_det = data.address;
  this.user_work_profiles = data.work_profile;
  console.log('WorkProfile',this.user_work_profiles);
}

selected_user=<any>[];
edit_user_details(user)
{
  console.log('Selected User',user);

  let practice_details=[];
  //console.log("User",this.user_work_profiles.filter(x => x['user_id'] == user.id));
this.selected_user['det']=user;

this.selected_user['prof'] =  this.user_profile_det.find(x => x['user_id'] == user.id);
this.selected_user['addr'] =  this.user_address_det.find(x => x['id'] == this.selected_user['prof']['address_flag_id']);
this.selected_user['w_p'] = this.user_work_profiles.filter(x => x['user_id'] == user.id);
console.log('User ID',user.id);

practice_details = this.user_work_profiles.filter(x => x['user_id'] == user.id);
console.log('practice Details',practice_details);

//console.log(this.selected_user);
  // this.user_update.selected_user(user);
  this.fetchrole();
  // console.log(this.selected_user['prof']['dob']);

  // this.selected_user['prof']['dob']
        // console.log(this.selected_user['prof']['dob']);
        this.userEdit.patchValue({
          username:user.user_name,
          firstname:user.firstname,
          lastname:user.lastname,
          // dob: {startDate: this.selected_user['prof']['dob'], endDate: this.selected_user['prof']['dob']},
          dob:{year:this.selected_user['prof']['dob'][2],month:this.selected_user['prof']['dob'][1],day:this.selected_user['prof']['dob'][0]},
          phone:this.selected_user['prof']['mobile_phone'],
          address1:this.selected_user['addr']['address_line_1'],
          address2:this.selected_user['addr']['address_line_2'],
          city:this.selected_user['addr']['city'],
          state:this.selected_user['addr']['state'],
          zip:this.selected_user['addr']['zip4'],
          // role:user.role_id,
          // assign_limit:this.selected_user['w_p']['claim_assign_limit'],
          // caller_benchmark:this.selected_user['w_p']['caller_benchmark']

        });

this.listPractice(practice_details);


}
onBlur()
{
  // console.log(this.userEdit.value);
if(this.userEdit.value.username != '' && this.userEdit.value.username != this.selected_user['det']['user_name'] )
{
	 this.Jarwis.validateusername(this.userEdit.value).subscribe(

      message=> this.handlemessage(message),
      error => this.handleError(error)
    );
  }
}

field_details=[];
practice_nos :number=0;
listPractice(practice){
let fieldDet=[];

practice.forEach(element => {

  let field_name=[]
  //console.log(element.practice_id,this.practice);
  this.process_fields.addControl('role_'+element.id, new FormControl(element.role_id, [Validators.required]));
  this.process_fields.addControl('practice_' +element.id , new FormControl(element.practice_id, [Validators.required]));
  this.process_fields.addControl('assign_limit_' +element.id , new FormControl(element.claim_assign_limit, [Validators.required]));
  this.process_fields.addControl('caller_bm_' +element.id , new FormControl(element.caller_benchmark, [Validators.required]));

  field_name['line_id']=element.id;
  field_name['role']='role_'+element.id;
  field_name['practice']='practice_' +element.id;
  field_name['assign_lim']='assign_limit_' +element.id;
  field_name['caller_bm']='caller_bm_' +element.id;

  console.log('Field Name',field_name);
  fieldDet.push(field_name);
});
this.field_details=fieldDet;
this.practice_nos = fieldDet.length;
//console.log(this.field_details);
}


delete_row(data)
{
  this.practice_nos--;
//console.log(data);

let index = this.field_details.findIndex(x => x == data)
this.field_details.splice(index,1);

this.process_fields.removeControl(data.practice);
this.process_fields.removeControl(data.role);
this.process_fields.removeControl(data.assign_lim);
this.process_fields.removeControl(data.caller_bm);

}

add_practice()
{
  let index=Math.floor(Math.random() * 99) + 1;
this.practice_nos++;
  if(this.practice.length >= this.practice_nos)
  {

    this.process_fields.addControl('role_'+index, new FormControl(null, [Validators.required]));
    this.process_fields.addControl('practice_' +index , new FormControl(null, [Validators.required]));
    this.process_fields.addControl('assign_limit_' +index , new FormControl(null, [Validators.required]));
    this.process_fields.addControl('caller_bm_' +index , new FormControl(null, [Validators.required]));

    let fields=[];
    fields['line_id']=index;
    fields['practice']='practice_' +index;
    fields['role']='role_'+index;
    fields['assign_lim']='assign_limit_' +index;
    fields['caller_bm']='caller_bm_' +index;

    this.field_details.push(fields);
  }


}


selected_practices=[];
check_dup_prac()
{
  // console.log(this.process_fields.value);
  // let values=this.process_fields.value;
  let used_practices=[];
  this.field_details.forEach(element => {
    // console.log(element.line_id);

    // console.log(this.process_fields.value['practice_'+element.line_id])

    let practice_id=this.process_fields.value['practice_'+element.line_id];

    let prac_index = used_practices.findIndex(x => x == practice_id);
    //console.log(prac_index,used_practices);
  if(prac_index < 0)
  {
    used_practices.push(practice_id);
  }
  else{

    this.process_fields.get('practice_'+element.line_id).reset();
    this.process_fields.get('role_'+element.line_id).reset();
    this.process_fields.get('assign_limit_'+element.line_id).reset();
    this.process_fields.get('caller_bm_'+element.line_id).reset();
  }


  });




}

public handlemessage(data)
{
  if(data.message !='Clear')
  {
    this.userEdit.get('username').setValue(this.selected_user['det']['user_name']);
    this.toastr.warningToastr('Select Different Username', 'Unavaiable!')
  }

}

update_user() {

  //console.log("SubmitEdit",this.userEdit.value);
this.submitted = true;
    if (this.userEdit.invalid) {
        return;
    }
    try{
  let process_values=this.userEdit.value.process_details;
  let prac_settings=[];
  this.field_details.forEach(element => {
    let prac_value=  process_values['practice_'+element.line_id];
    let role_value= process_values['role_'+element.line_id];
    let assign_lim= process_values['assign_limit_'+element.line_id];
    let caller_bm= process_values['caller_bm_'+element.line_id];
    prac_settings.push({practice_id:prac_value,role_id:role_value,assign_limit:assign_lim,caller_bench:caller_bm,upd_id:element.line_id});
});
//console.log(prac_settings);
this.userEdit.value.process_details=prac_settings;
//console.log(this.userEdit.value,this.selected_user['det']['id']);
  this.Jarwis.update_user_details(this.userEdit.value,this.selected_user['det']['id'], this.setus.getId()).subscribe(
    data =>{ this.get_users_list();
      this.toastr.successToastr('User details Updated successfully.')
    },

  );
  }catch (error) {

  }
}


fetchrole()
{
    this.Jarwis.getrole().subscribe(
      data => this.handleResponse(data),
    );
}

getPractices()
{
  this.Jarwis.getPracticesList().subscribe(
    data =>this.listPractices(data),
    error=>this.toastr.errorToastr(error, 'Error')
  );

}

listPractices(data)
{
  console.log("Practices New ",data);
  this.practice = data.data;
}

public handleError(error)
  {
    console.log(error);
  }
handleResponse(data){
  this.roles=data.access_token;
  }
  ngOnInit() {

    this.getPractices();

    this.process_fields = new FormGroup({
      // role_0: new FormControl('', [
      //   Validators.required
      // ]),
      // practice_0: new FormControl('', [
      //   Validators.required
      // ]),
      // assign_limit_0: new FormControl('',[Validators.pattern(/^[0-9-]*$/)]),
      // caller_bm_0: new FormControl('',[Validators.pattern(/^\d+(\.\d{1,2})?$/)])
    });

    this.userEdit = new FormGroup({
      username: new FormControl('', [
        Validators.required,
        Validators.pattern(/^[a-zA-Z]+\w*$/)
      ]),
      // password: new FormControl('', [
      //   Validators.required
      // ]),
      firstname: new FormControl('', [
        Validators.required
      ]),
      lastname: new FormControl('', [
        Validators.required
      ]),
      dob : new FormControl('', [
        Validators.required
      ]),
      phone: new FormControl('', [
        Validators.required,
        Validators.pattern(/^[0-9-]*$/),
        Validators.minLength(10)

      ]),
      address1: new FormControl('', [
        Validators.required
      ]),
      address2: new FormControl('', [
        Validators.required
      ]),
      city: new FormControl('', [
        Validators.required
      ]),
      state: new FormControl('', [
        Validators.required
      ]),
      zip: new FormControl('', [
        Validators.required,
         Validators.pattern(/^[0-9-]*$/)
      ]),
      process_details:this.process_fields,
});
  }

}
