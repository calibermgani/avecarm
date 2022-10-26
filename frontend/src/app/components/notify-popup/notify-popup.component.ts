import { Component, OnInit,Input, TemplateRef, ElementRef, ViewChild } from '@angular/core';
import { NotifyService } from '../../Services/notify.service';
import {NgbModal, ModalDismissReasons,NgbActiveModal} from '@ng-bootstrap/ng-bootstrap';
import { Subscription } from 'rxjs';
import { ElementDef } from '@angular/core/src/view';

// @Component({
//   selector: 'ngbd-modal-content',
//   template: `
//     <div class="modal-header">
//       <h4 class="modal-title">Hi there!</h4>
//       <button type="button" class="close" aria-label="Close" (click)="activeModal.dismiss('Cross click');">
//         <span aria-hidden="true">&times;</span>
//       </button>
//     </div>
//     <div class="modal-body">
//       <p>Hello</p>
//     </div>
//     <div class="modal-footer">
//       <button type="button" class="btn btn-outline-dark" (click)="activeModal.close('Close click');">Close</button>
//     </div>
//   `
// })
// export class NgbdModalContent {
//   @Input() name;

//   constructor(public activeModal: NgbActiveModal) {}
// }



@Component({
  selector: 'app-notify-popup',
  templateUrl: './notify-popup.component.html',
  styleUrls: ['./notify-popup.component.css']
})


export class NotifyPopupComponent implements OnInit  {
//   @ViewChild('confirm_modal') mymodal: ElementRef;
//   subscription: Subscription;
//   constructor(private notify_service:NotifyService,
//     private modalService: NgbModal,) {
// if(this.modalService.hasOpenModals() == false)
// {
// this.process_notify()
// }

//    }

//    notify_data;

//   process_notify()
//   {
//    if(this.notify_data == undefined)
//     {
//       this.notify_data=this.notify_service.manual_notify();

      
//     } 
//     console.log("oo",this.notify_data);

//   if(this.notify_data != undefined)
//   {
    // this.open();
    // if(this.modalService.hasOpenModals() == false)
    // {
    //   this.open(confirm_modal);
    // } 



    // this.open(this.mymodal);
  // }

  // }


//   open(content)
// {
//   console.log(content);
//   this.modalService.open(content, { centered: true ,windowClass:'alert-class'}); 
// }



// open() {
//   const modalRef = this.modalService.open(NgbdModalContent);
// }


  ngOnInit() {
//  console.log("HERE erE")
//     this.notify_service.get_notify_data().subscribe(message => {  this.notify_data = message;
//       if(this.modalService.hasOpenModals() == false)
//       {
//       this.process_notify()
//       }
//       console.log("Test2");
//   });
 
  }

  // ngonDestroy()
  // {
  //   console.log('destroyed');
  // }
 



}
