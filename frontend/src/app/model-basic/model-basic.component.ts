import { Component, OnInit } from '@angular/core';
import {NgbModal, ModalDismissReasons} from '@ng-bootstrap/ng-bootstrap';
@Component({
  selector: 'app-model-basic',
  templateUrl: './model-basic.component.html',
  styleUrls: ['./model-basic.component.css']
})
export class ModelBasicComponent implements OnInit 
  {
    closeResult: string;
  
    constructor(private modalService: NgbModal) {}
  
    open(content) {
      this.modalService.open(content, { centered: true });
    }
  /*
    private getDismissReason(reason: any): string {
      if (reason === ModalDismissReasons.ESC) {
        return 'by pressing ESC';
      } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
        return 'by clicking on a backdrop';
      } else {
        return  `with: ${reason}`;
      }
    }

    */
    ngOnInit() {
    }
  }



