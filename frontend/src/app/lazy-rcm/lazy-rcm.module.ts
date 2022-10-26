import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RcmComponent } from '../components/rcm/rcm.component';
import { LazyRCMRoutingModule } from './lazy-rcm-routing.module';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';

@NgModule({
  imports: [
    CommonModule,
    LazyRCMRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    Ng2SearchPipeModule,
    NgxDaterangepickerMd
  ],
  declarations: [RcmComponent]
})
export class LazyRCMModule { }
