import { Injectable, Output , EventEmitter } from '@angular/core';
import { Observable, Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class NotesHandlerService {
  // @Output() claim_details: EventEmitter<any> = new EventEmitter();
  // @Output() response_emitter: EventEmitter<any> = new EventEmitter();
  private claim_details = new Subject<any>();
  private process_claim_details = new Subject<any>();
  private subject = new Subject<any>();
  private response_emitter = new Subject<any>();
  private claim_data = new Subject<any>();
  private audit_response_emitter = new Subject<any>();
  private ca_response_emitter = new Subject<any>();
  private notes_reloader = new Subject<any>();

  constructor() { }
  notes_detail : Array<any> =[];
  process : Array<any> =[];

public set_notes(user_id,notes,claim_id,command_type)
{

  if(!this.notes_detail.find(x => x.claim_no == claim_id['claim_no']))
  {
    this.notes_detail.push({user:user_id,note:notes,claim:claim_id,type:command_type,claim_no:claim_id['claim_no']});
    let claim_details=this.notes_detail.find(x => x.claim_no == claim_id['claim_no']);
    this.claim_details.next(claim_details);
  }
  else if(this.notes_detail.find(x => x.claim_no == claim_id['claim_no']) && !this.notes_detail.find(x => x.note == notes) )
  {
    // console.log("in here2",claim_id['claim_no']);
    let claim=this.notes_detail.find(x => x.claim_no == claim_id['claim_no']);
    let index=this.notes_detail.findIndex(x => x == claim);
    this.notes_detail[index] = {user:user_id,note:notes,claim:claim_id,type:command_type,claim_no:claim_id['claim_no']};
    let claim_details=this.notes_detail.find(x => x.claim_no == claim_id['claim_no']);
    // console.log("ND",this.notes_detail);
    this.claim_details.next(claim_details);
  }
  else
  {
    let claim=this.notes_detail.find(x => x.claim_no == claim_id['claim_no']);
    this.claim_details.next(claim);
  }

}


public set_notesest(user_id,notes,claim_id,command_type)
{
console.log(this.process);

//console.log(this.notes_detail);

if(!this.process.find(x => x.claim_no == claim_id['claim_no']))
{
  this.process.push({user:user_id,note:notes,claim:claim_id,type:command_type,claim_no:claim_id['claim_no']});
  let process_claim_details=this.process.find(x => x.claim_no == claim_id['claim_no']);
  this.process_claim_details.next(process_claim_details);
  
}
else if(this.process.find(x => x.claim_no == claim_id['claim_no']) && !this.process.find(x => x.note == notes) )
{
  // console.log("in here2",claim_id['claim_no']);
  let claim=this.process.find(x => x.claim_no == claim_id['claim_no']);
  let index=this.process.findIndex(x => x == claim);
  this.process[index] = {user:user_id,note:notes,claim:claim_id,type:command_type,claim_no:claim_id['claim_no']};
  let process_claim_details=this.process.find(x => x.claim_no == claim_id['claim_no']);
  // console.log("ND",this.notes_detail);
  this.process_claim_details.next(process_claim_details);
}
else
{
  let claim=this.process.find(x => x.claim_no == claim_id['claim_no']);
  this.process_claim_details.next(claim);
}

}


 get_notes(): Observable<any> {

  return this.claim_details.asObservable();
}

process_get_notes(): Observable<any> {

  return this.process_claim_details.asObservable();
}



public selected_tab(data)
{
  //alert(data);
  this.subject.next(data);
}
get_current_tab(): Observable<any> {
  // console.log(this.subject);

  return this.subject.asObservable();
}

public set_response(data,type)
{
if(type == 'followup')
{
  this.audit_response_emitter.next(data);
}
else if(type=="audit"){
  this.response_emitter.next(data);
}
else if(type == "CA")
{
  this.ca_response_emitter.next(data);
}


}

get_response_data(data): Observable<any> 
{
    console.log(this.audit_response_emitter.asObservable());
  if(data == 'followup')
  {
    return this.audit_response_emitter.asObservable();
  }
  else if(data=="audit"){
    return this.response_emitter.asObservable();
  }
  else if(data == "CA")
  {
    return this.ca_response_emitter.asObservable();
  }
  
}


public set_claim_details(data)
{
  // alert("audit");
  this.claim_data.next(data);
}

public get_claim_details(): Observable<any> {
  return this.claim_data.asObservable();
}

public refresh_notes(data)
{
this.notes_reloader.next(data);
}

refresh_update():Observable<any> {
  return this.notes_reloader.asObservable();
}

}
