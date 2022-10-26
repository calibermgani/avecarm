import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LazyFollowupRoutingModule } from './lazy-followup-routing.module';
import { FollowupComponent } from '../components/followup/followup.component';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';

@NgModule({
  imports: [
    CommonModule,
    LazyFollowupRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    NgxDaterangepickerMd,
  ],
  declarations: [FollowupComponent]
})
export class LazyFollowupModule { }
