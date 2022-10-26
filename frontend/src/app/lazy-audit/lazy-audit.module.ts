import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LazyAuditRoutingModule } from './lazy-audit-routing.module';
import { AuditComponent } from '../components/audit/audit.component';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';

@NgModule({
  imports: [
    CommonModule,
    LazyAuditRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    NgxDaterangepickerMd
  ],
  declarations: [AuditComponent]
})
export class LazyAuditModule { }
