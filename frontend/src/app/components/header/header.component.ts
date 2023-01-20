import { Component, OnInit,ElementRef, ViewChild, AfterViewInit, ChangeDetectorRef } from '@angular/core';
import { AuthService } from '../../Services/auth.service';
import { Router } from '@angular/router';
import { TokenService } from '../../Services/token.service';
import { LoadingBarService } from '@ngx-loading-bar/core';
import { SetUserService } from '../../Services/set-user.service';
import { Subscription } from 'rxjs';
import { NotifyService } from '../../Services/notify.service';
import {NgbModal, ModalDismissReasons,NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';
import { JarwisService } from '../../Services/jarwis.service';
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit  {
  observalble: Subscription;
  subscibe:Subscription;
  subscription : Subscription;
  public loggedIn :boolean;
  public practiceLogIn :boolean;
  public user : string=null;
  public user_type : string[]=[null];
  public user_role : string=null;
  public touch_count:number;
  @ViewChild('confirm_modal') mymodal: ElementRef;

  public practice_name;
  
  constructor(
    private Auth:AuthService,
    private router: Router,
    private Token: TokenService,
    private loadingBar: LoadingBarService,
    private setus: SetUserService,
    private notify_service:NotifyService,
    private modalService: NgbModal,
    private cd: ChangeDetectorRef,
    private Jarwis: JarwisService,
  ) { 
    this.observalble=this.setus.update_role().subscribe(message => {this.user_type = message, this.update_user_role()} );

    this.subscibe=this.notify_service.get_notify_data().subscribe(message => {  this.notify_data = message;

      this.process_notify()
      
  });
  }
update_user_role()
{
  this.user=this.setus.getname();
    this.user_role=this.setus.get_role();

}

  open(content)
{
  if(this.modalService.hasOpenModals() == false)
  {
  this.modalService.open(content, { centered: true ,windowClass:'alert-class'}); 
}
}



  logout(event: MouseEvent){
    event.preventDefault();
    this.Token.remove();
    localStorage.clear();
    this.Auth.changeAuthStatus(false);
    this.router.navigateByUrl('/login');
    this.subscibe.unsubscribe();
    
  }
  notify_data;

  process_notify()
  {
   if(this.notify_data == undefined)
    {
      this.notify_data=this.notify_service.manual_notify();

      
    } 
    // console.log("note data",this.notify_data,this.loggedIn);

    if(this.notify_data != undefined && this.loggedIn == true)
    {
      // console.log(this.mymodal)
      // this.open(this.mymodal);
    }
  


  }


  monitor_change(data)
  {
    console.log("Hit!!!",data)
  }

  changePractice()
  {
    this.Auth.changePractice();
  }

  getprecticename(){
    let str = localStorage.getItem('practice_name');
    this.practice_name = str[0].toUpperCase() + str.slice(1);
    console.log(this.practice_name);
  }

  ngOnInit() {
    this.Auth.authStatus.subscribe(value => this.loggedIn = value);
    this.Auth.practiceStatus.subscribe(value => this.practiceLogIn = value);
    this.loadingBar.start();
    this.notify_service.getuser_Id();

    // this.setus.change.subscribe(value => this.user_type = value,this.update_user_role());
    this.update_user_role();
    this.subscription=this.notify_service.fetch_touch_limit().subscribe(message => { 
    this.touch_count = message });
    this.getprecticename();
    
  }

  public alertValue(){

    this.Jarwis.getAlertNotification(this.setus.getId()).subscribe(
      data  => this.handleResponse(data),
      error => this.handleError(error)
    );

    // this.Jarwis.getAlertNotification(this.setus.getId()).subscribe(
    //   data  => this.handleResponse(data),
    //   error => this.handleError(error)
    // );
  }

  client_assistance_count;
  pending_claim_count;
  touch_counts;

  public handleResponse(data){
    console.log(data.client_assistance_count);

    this.client_assistance_count = data.client_assistance_count;
    this.pending_claim_count = data.pending_claim_count;
    this.touch_counts = data.touch_count;

  }

  public handleError(error){

  }

}
