import { Component, OnInit } from '@angular/core';
import { JarwisService } from '../../Services/jarwis.service';
import { ToastrManager } from 'ng6-toastr-notifications';
import { SetUserService } from '../../Services/set-user.service';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import {FormControl, FormGroup, Validators,FormArray } from "@angular/forms";
@Component({
  selector: 'app-roles',
  templateUrl: './roles.component.html',
  styleUrls: ['./roles.component.css']
})
export class RolesComponent implements OnInit {

  constructor( private Jarwis: JarwisService,
    public toastr: ToastrManager,
    private setus: SetUserService,
    private modalService: NgbModal) { }
  formGroup: FormGroup;
  submitted = false;

  roles=[];
  editId=[];
  closeResult;

  public handleError(error)
  {
    console.log(error);
  }

  get_roles()
  {
    this.Jarwis.getrole().subscribe(
      message=> this.listRoles(message),
      error => this.handleError(error)
      );
  }

  get_user_role()
  {
    this.Jarwis.get_user_role('data').subscribe(
      message=> this.listRoles(message),
      error => this.handleError(error)
      );
  }

  listRoles(data)
  {
     //console.log(data.access_token);
    this.roles=data.access_token;
  }

  edit_roles(role)
  {
    this.editId=role;
    //console.log(this.editId['id']);
    this.formGroup.patchValue({
      roleName:role.role_name,
      status:role.status
    });
  }

 ngOnInit() {

    this.formGroup = new FormGroup({
      roleName : new FormControl('', [
        Validators.required
      ]),
      status : new FormControl('Active', [
        Validators.required
      ])
    });
  }

onUpdate()
{
  this.submitted = true;
  if (this.formGroup.invalid) {
      return;
  }try{
  this.Jarwis.updateRoles(this.formGroup.value, this.setus.getId(), this.editId['id']).subscribe(
    data => this.handleRespponseUpdateRole(data),
    error => this.validation(error)
  );
  }catch (error) {
   this.toastr.errorToastr('Error in update new Role.')
  }
}
public modal;
handleRespponseUpdateRole(data){
  this.get_roles();
  this.modal.dismiss();
  this.toastr.successToastr(data.status);
}

onSubmit(){
  this.submitted = true;
  if (this.formGroup.invalid) {
      return;
  }try{
  this.Jarwis.createRoles(this.formGroup.value, this.setus.getId()).subscribe(
    data => this.handleRespponseCreateRole(data),
    error => this.validation(error)
  );
  }catch (error) {
   this.toastr.errorToastr('Error in creating new Role.')
  }
}

handleRespponseCreateRole(data){
    this.toastr.successToastr(data.status);
}

validation(error){

}


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

 

}
