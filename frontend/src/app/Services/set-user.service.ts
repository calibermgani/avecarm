import { Injectable,Output , EventEmitter } from '@angular/core';
import { Observable, Subject } from 'rxjs';
import { idLocale } from 'ngx-bootstrap';
@Injectable({
  providedIn: 'root'
})
export class SetUserService {

  constructor() { }
  // @Output() change: EventEmitter<any> = new EventEmitter();
  private change = new Subject<any>();
  private edit_permission = new Subject<any>();
  public user_role:string[];
  setId(token,data){
    localStorage.setItem('id', token);
    localStorage.setItem('name',data);
    // localStorage.setItem('role',role);
    // localStorage.setItem('role_id',role_id);

    // this.change.emit(role);
// this.set_type(role);
  }

  getId(){
    return localStorage.getItem('id');
  }
  getname(){
    return localStorage.getItem('name');
  }
  set_type(data)
  {
    this.user_role=data;
    if(this.user_role!=undefined)
    {
      this.change.next(this.user_role);
    }
  }
  update_role() : Observable<any> {
    return this.change.asObservable();
  }

  set_edit_type(data)
  {
    if(data != undefined)
    {
      this.edit_permission.next(data);
    }

  }
  update_edit_perm() : Observable<any> {
    return this.edit_permission.asObservable();
  }


  
  get_type()
  {
    return this.user_role;
  }
  get_role()
  {
    return localStorage.getItem('role');
  }

  get_role_id()
  {
    return localStorage.getItem('role_id');
  }

  setPractice(prac_id)
  {
    localStorage.setItem('practice_id',prac_id.data);
    localStorage.setItem('role_id',prac_id.role);
  }




  public display_error:string;

  public dashboard_warning(data)
  {
    this.display_error=data;
  }
  public get_error()
    {
    return this.display_error;
    }
  



}
