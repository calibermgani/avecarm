import { Component, OnInit } from '@angular/core';
import { JarwisService } from '../../Services/jarwis.service';
@Component({
  selector: 'app-error-log',
  templateUrl: './error-log.component.html',
  styleUrls: ['./error-log.component.css']
})
export class ErrorLogComponent implements OnInit {

  constructor(
    private Jarwis: JarwisService,
  ) { }

  logs_list;
  public handleError(error)
  {
    console.log(error);
  }

public getLogs()
  {
    this.Jarwis.get_logs().subscribe(
      data => this.list_logs(data),
      error => this.handleError(error)
    );

  }

  list_logs(data)
  {
    //console.log(data);
    this.logs_list=data.data;
  }

  viewLog(fileName)
  {
    this.Jarwis.viewLog(fileName).subscribe(
      data => this.display_log(data),
      error => this.handleError(error)
    );
  }

  logInfo:string;
  display_log(data)
  {
    this.logInfo=data.data;
  }


  ngOnInit() {
    this.getLogs();
  }

}
