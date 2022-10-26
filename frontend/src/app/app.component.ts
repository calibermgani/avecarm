import { Component } from '@angular/core';
import { AuthService } from '../app/Services/auth.service';
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'frontend';

 
  constructor(private Auth:AuthService) {
    this.Auth.authStatus.subscribe(value => this.loggedIn = value);
   }
   loggedIn:boolean;


  
}
