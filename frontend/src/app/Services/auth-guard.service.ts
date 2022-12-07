import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { Observable } from 'rxjs/observable';
import { AuthService } from './auth.service';

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {

    constructor(private auth: AuthService,private myRoute: Router) { }
    public str = null;
    public maan = null;
    public man = null;
    public goos = null;
    public result(value,value2)
    {
      if(value.message==value2.token)
      {
        this.str="True";
        return true;
        }
        else{
          this.str="False";
          return false;
          }
          }
          canActivate(
            next: ActivatedRouteSnapshot,
            state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
              let data=localStorage.getItem('token');
              this.auth.login(data);

              console.log("Auth",this.auth.loggedIn);
              if(this.auth.loggedIn.value == true ){
                return true;
                }else
                {
                  this.myRoute.navigate(["login"]);
                  return false;
                  }
                    
  }
}