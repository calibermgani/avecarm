import { Injectable } from '@angular/core';
import { BehaviorSubject, of  } from 'rxjs';
import { TokenService } from './token.service';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import {  map } from 'rxjs/operators';
import { SetUserService } from './set-user.service';
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  public loggedIn = new BehaviorSubject<boolean>(this.Token.loggedIn());
  public PracticelogIn = new BehaviorSubject<boolean>(false);
  authStatus = this.loggedIn.asObservable();

  practiceStatus = this.PracticelogIn.asObservable();

  public validate = new BehaviorSubject<boolean>(this.Token.isValid());
  //private url: string = 'http://localhost:8000/api';
  private url = 'http://127.0.0.1:8000/api';
  // private url: string =  'http://35.226.72.203/avecarm/backend/public/index.php/api';
 
    public errorhandler(data)
    {
      // if(data.status)
      // {
      //   console.log('Token Error');
      //   }
        this.changeAuthStatus(false);
    }
    
  public login(user) {
    let user_id=this.set_us.getId();

    user={token:user,id:user_id};
    // console.log("Before",user);
    // console.log("Before",this.authStatus);
    if(user_id!=null)
    {
      return this.http.post(`${this.url}/checktoken`, user).pipe(map(response => response))
      .subscribe(
          message => this.afterUserLog(message),
          error => this.errorhandler(error)
       );
    }
    else{
      this.errorhandler('nulluser');
    }
    
  }

  afterUserLog(message)
  {
    // console.log('Token',message)
    this.Token.set(message['access_token']);
    // let newVal = this.myRoute.url.replace(/[^\w\s]/gi, '')
    // // let permission=message['permission']; 
    // if(permission.includes(newVal))
    // {
    // this.Token.set(message['message']);
    // this.set_us.set_type(message['permission']);
    // this.set_us.set_edit_type(message['edit_permission']);
    // }
    // else{
    //   this.Token.set(message['message']);
    //   this.myRoute.navigate(["dashboard"]);
    //   this.set_us.dashboard_warning('No Access to the Page');
    
    // }

    // this.set_us.set_type(null);
    // this.set_us.set_edit_type(null);

    if(localStorage.getItem('practice_id'))
    {
      this.practicePermission();
    }
    else if(localStorage.getItem('role'))
    {
      this.practicePermission();
    }

    if(!this.loggedIn.value)
    {
      this.errorhandler('error');
    }
  }





  practicePermission()
  {
    let practice=localStorage.getItem('practice_id');
    let role=localStorage.getItem('role');
    if(practice != null)
    {
      let user_id=this.set_us.getId();

      let user={id:user_id,practice_id:practice};
      // console.log(user);
      return this.http.post(`${this.url}/getPermissions`, user).pipe(map(response => response))
      .subscribe(
          message => this.after_check(message),
          error => this.errorhandler(error)
       );
    }
    else if(role == 'Admin')
    {
      let user_id=this.set_us.getId();

      let user={id:user_id,user_role:role};
      // console.log("In Hrar",user);
      return this.http.post(`${this.url}/getPermissions`, user).pipe(map(response => response))
      .subscribe(
          message => this.after_check(message),
          error => this.errorhandler(error)
       );
    }
  }

  authPractice(status:boolean)
  {
    if(status==false)
    {
      localStorage.removeItem('practice_id');
      localStorage.removeItem('role_id');
      this.myRoute.navigate(["practiceList"]);

    }
  }


  changePractice()
  {
    localStorage.removeItem('practice_id');
    localStorage.removeItem('role_id');
    this.set_us.set_type(null);
    this.set_us.set_edit_type(null);
      this.PracticelogIn.next(false);
  }





  public after_check(message)
  {
    // console.log("AC",message);

    let newVal = this.myRoute.url.replace(/[^\w\s]/gi, '');
    let permission=message['permission']; 
    // console.log(newVal,permission);

    if(permission.includes(newVal))
    {
    // this.Token.set(message['message']);
    this.set_us.set_type(message['permission']);
    this.set_us.set_edit_type(message['edit_permission']);
    // this.myRoute.navigate(["dashboard"]);
    this.PracticelogIn.next(true);
    }
    else if(newVal == 'practiceListdashboard' || localStorage.getItem('role') =='Admin' )
    {
      this.set_us.set_type(message['permission']);
      this.set_us.set_edit_type(message['edit_permission']);
      this.myRoute.navigate(["dashboard"]);
      this.PracticelogIn.next(true);
    }
    else{
      // this.Token.set(message['message']);
      this.set_us.set_type(null);
    this.set_us.set_edit_type(null);
      this.PracticelogIn.next(false);
      this.myRoute.navigate(["practiceList"]);
      this.set_us.dashboard_warning('No Access to the Page');
    
    }

    if(!this.loggedIn.value)
    {
      this.errorhandler('error');
    }
  }

  changeAuthStatus(value:boolean){
    // console.log('called',value);
    if(value==false)
    {
      localStorage.clear();
      this.myRoute.navigate(["login"]);

    }
    this.loggedIn.next(value);
  } 

  constructor(private Token: TokenService,private http: HttpClient,private myRoute: Router, private set_us :SetUserService, ) { }

}
