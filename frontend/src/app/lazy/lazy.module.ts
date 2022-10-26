import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LazyRoutingModule } from './lazy-routing.module';
import { DashboardComponent } from '../components/dashboard/dashboard.component';
import { PracticeComponent } from '../components/practice/practice.component';
import { DocumentsComponent } from '../components/documents/documents.component';
import { CommonModuleModule } from '../common-module/common-module.module';
import { SidebarModule } from 'ng-sidebar';
import { ReportService } from '../Services/report.service';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';


@NgModule({
  imports: [
    CommonModule,
    LazyRoutingModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    Ng2SearchPipeModule,
    NgxDaterangepickerMd
  ],
  declarations: [DashboardComponent,
    PracticeComponent,
    DocumentsComponent
  ],
    providers:[ReportService]
})
export class LazyModule { }