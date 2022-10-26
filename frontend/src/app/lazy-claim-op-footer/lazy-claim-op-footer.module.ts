import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LazyClaimOpFooterRoutingModule } from './lazy-claim-op-footer-routing.module';
import { ClaimOpFooterComponent } from '../components/claim-op-footer/claim-op-footer.component';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import * as moment from 'moment';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';

@NgModule({
  imports: [
    CommonModule,
    LazyClaimOpFooterRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    NgxDaterangepickerMd
  ],
  declarations: [ClaimOpFooterComponent]
})
export class LazyClaimOpFooterModule {

  constructor(){ }


 }


