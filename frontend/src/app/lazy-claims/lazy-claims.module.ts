import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LazyClaimsRoutingModule } from './lazy-claims-routing.module';
import { ClaimsComponent } from '../components/claims/claims.component';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import * as moment from 'moment';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';

@NgModule({
  imports: [
    CommonModule,
    LazyClaimsRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    NgxDaterangepickerMd
  ],
  declarations: [ClaimsComponent]
})
export class LazyClaimsModule {

  constructor(){ }


 }


