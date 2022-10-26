import { Component, OnInit } from '@angular/core';
import { SetUserService } from '../../Services/set-user.service';
import { JarwisService } from '../../Services/jarwis.service';
import { AuthService } from '../../Services/auth.service';
@Component({
  selector: 'app-practice-list',
  templateUrl: './practice-list.component.html',
  styleUrls: ['./practice-list.component.css']
})
export class PracticeListComponent implements OnInit {

  constructor(private setus: SetUserService,
    private Jarwis: JarwisService, 
    private Auth:AuthService) { }

  user_name:string;
  practice_list;

  public handleError(error)
  {
    console.log(error);
  }

  get_practices()
  {
    this.Jarwis.getPractices(this.setus.getId()).subscribe(
      data => this.list_practice(data),
      error => this.handleError(error)
    );
 
  }

  list_practice(data)
  {
    //console.log(data);
    this.practice_list=data.data;

  }

  selectPractice(Practice_id)
  {
    this.Jarwis.selectPractice(Practice_id,this.setus.getId()).subscribe(
      data => this.set_practice(data),
      error => this.handleError(error)
    );
  }

  set_practice(data)
  {
    this.setus.setPractice(data);
    this.Auth.practicePermission();
  }

  

  ngOnInit() {
    this.user_name=this.setus.getname();
    this.get_practices();
  }

}
