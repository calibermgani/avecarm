import { Component, OnInit,ViewEncapsulation } from '@angular/core';
import { FormControl, FormGroup, Validators } from "@angular/forms";
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
import { NotesHandlerService } from '../../Services/notes-handler.service';
import { Subscription } from 'rxjs';
import { JarwisService } from '../../Services/jarwis.service';
import { SetUserService } from '../../Services/set-user.service';
@Component({
  selector: 'app-client-notes',
  templateUrl: './client-notes.component.html',
  styleUrls: ['./client-notes.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class ClientNotesComponent implements OnInit {
  clientNotes: FormGroup;
  closeResult : string;
  subscription: Subscription;
  active_tab:string;
  loading:boolean=true;
  constructor(
    private modalService: NgbModal,
    private notes_handler:NotesHandlerService,
    private Jarwis: JarwisService,
    private setus: SetUserService,
  ) { 
    this.subscription=this.notes_handler.get_current_tab().subscribe(message => { this.curr_active_tab(message) });
  }

  open(content) {
    this.modalService.open(content, { centered: true,windowClass:'custom-class' }).result.then((result) => {
      this.closeResult = `${result}`;
    }, (reason) => {
      this.closeResult = `${this.getDismissReason()}`;
    }); 
}
private getDismissReason() {
  // console.log(this.closeResult);
}


//Save Notes
public savenotes(type)
{
  let claim_id;
  // console.log(this.active_tab);
  claim_id=this.active_tab;

  if(type=='client_notes')
  {
    this.Jarwis.client_notes(this.setus.getId(),this.clientNotes.value['client_notes'],claim_id,'client_create').subscribe(
    data  =>this.display_notes(data,type),
    error => console.log(error)
  );

  // this.client_notes_data.push({notes:this.clientNotes.value['client_notes'],id:claim_id['claim_no']});
  // this.client_notes_data_list.push(claim_id['claim_no']);
  // this.notes_hadler.set_notes(this.setus.getId(),this.clientNotes.value['client_notes'],claim_id,'create_client_notes');
  // this.send_calim_det('footer_data');



  }

}

client_notes=[];
// public getnotes(claim)
// {
//   this.client_notes=[];
//   let type='All';
//   this.Jarwis.get_client_notes(claim).subscribe(
//     data  => this.display_notes(data,type),
//     error => this.handleError(error)
//   ); 
// }

public display_notes(data,type)
{
console.log(data,type);
      if(type=='client_notes')
      {
        this.client_notes=data.data;
        console.log(this.client_notes);
        }
           else if(type=='All')
           {
             this.client_notes=data.data;
             } 
             this.loading=false; 
  this.clientNotes.reset();

}

public handleError(error)
{
  console.log(error);
}

public curr_active_tab(data)
{
  if(this.active_tab != data && data != undefined)
  {
  this.active_tab=data;
  // this.getnotes(data);   
  }

}

  ngOnInit() {
    this.clientNotes = new FormGroup({
      client_notes: new FormControl('', [
        Validators.required
      ])
    });

  }

}
