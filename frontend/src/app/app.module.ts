import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { NgDatepickerModule } from 'ng2-datepicker';
import { AppComponent } from './app.component';
import { NavbarComponent } from './components/navbar/navbar.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { LoginComponent } from './components/login/login.component';
import { SignupComponent } from './components/signup/signup.component';
import { AppRoutingModule } from './/app-routing.module';
import { JarwisService } from './Services/jarwis.service';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClaimsComponent } from './components/claims/claims.component';
import { PasswordvaildComponent } from './passwordvaild/passwordvaild.component';
import { UserregistrationComponent } from './components/userregistration/userregistration.component';
// import { NgBootstrapFormValidationModule } from 'ng-bootstrap-form-validation';


import { ProfileComponent } from './components/profile/profile.component';
import { GooglePlaceModule } from "ngx-google-places-autocomplete";
import { LoadingBarHttpClientModule } from '@ngx-loading-bar/http-client';
import { LoadingBarRouterModule } from '@ngx-loading-bar/router';
import { LoadingBarModule } from '@ngx-loading-bar/core';
import { LoadingBarHttpModule } from '@ngx-loading-bar/http';
import { HttpModule } from '@angular/http';
import {NgxPaginationModule} from 'ngx-pagination';
import { SettingsComponent } from './components/settings/settings.component';
import { PracticeComponent } from './components/practice/practice.component';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ModelBasicComponent } from './model-basic/model-basic.component';
import { FollowupComponent } from './components/followup/followup.component';
import { AuditComponent } from './components/audit/audit.component';
import { FollowupTemplateComponent } from './components/followup-template/followup-template.component';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import { NgxDaterangepickerMd } from 'ngx-daterangepicker-material';
import { FollowupViewComponent } from './components/followup-view/followup-view.component';
import { NgKnifeModule } from 'ng-knife';
import { SearchFilter } from './search-filter.pipe';
import { ClaimOpFooterComponent } from './components/claim-op-footer/claim-op-footer.component';
import { SelectDropDownModule } from 'ngx-select-dropdown';
import {AtomSpinnerModule} from 'angular-epic-spinners';
import { ToastrModule } from 'ng6-toastr-notifications';
import { ClientAssistanceComponent } from './components/client-assistance/client-assistance.component';
import { RcmComponent } from './components/rcm/rcm.component';
import {NgxPrintModule} from 'ngx-print';
import { ClientNotesComponent } from './components/client-notes/client-notes.component';
import { DocumentsComponent } from './components/documents/documents.component';
import { NotifyService } from './Services/notify.service';
import { NotifyPopupComponent } from './components/notify-popup/notify-popup.component';
import { FusionChartsModule } from 'angular-fusioncharts';
import * as FusionCharts from 'fusioncharts';
import * as Charts from 'fusioncharts/fusioncharts.charts';
import * as FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion';
import * as TimeSeries from 'fusioncharts/fusioncharts.timeseries';
import * as PowerCharts from 'fusioncharts/fusioncharts.powercharts';
import * as Widgets from 'fusioncharts/fusioncharts.widgets';
import * as overlappedcolumn2d from 'fusioncharts/fusioncharts.overlappedcolumn2d';
import {TooltipModule} from 'ng2-tooltip-directive';
import { DatePipe } from '@angular/common'
import {NgbDateAdapter, NgbDateStruct, NgbDateNativeAdapter,NgbDate,NgbDateParserFormatter} from '@ng-bootstrap/ng-bootstrap';
import { NgbDateCustomParserFormatter} from '../app/date_file';
import { SummaryComponent } from './components/summary/summary.component';
import { CommonModuleModule } from '../app/common-module/common-module.module';
import { CustomerCreationComponent } from './components/customer-creation/customer-creation.component';
import { UsersComponent } from './components/users/users.component';
import { PracticeListComponent } from './components/practice-list/practice-list.component';
import { ErrorLogComponent } from './components/error-log/error-log.component';
import { RolesComponent } from './components/roles/roles.component';
import { MedcubicsIntegComponent } from './components/medcubics-integ/medcubics-integ.component';
import { SortableDirective } from './sortable.directive';
import { SidebarModule } from 'ng-sidebar';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { ReportComponent } from './components/report/report.component';
import { TestUsersComponent } from './components/test-users/test-users.component';



// FusionChartsModule.fcRoot(FusionCharts,PowerCharts, Charts,Widgets,FusionTheme,TimeSeries,overlappedcolumn2d)

@NgModule({
  declarations: [
    AppComponent,
    // NavbarComponent,
    LoginComponent,
    SignupComponent,
    // DashboardComponent,
	  // ClaimsComponent,
	  // HeaderComponent,
	  // FooterComponent,
    PasswordvaildComponent,
    UserregistrationComponent,
     ProfileComponent,
     SettingsComponent,
     CustomerCreationComponent,
     UsersComponent,
     PracticeListComponent,
     ErrorLogComponent,
     RolesComponent,
     MedcubicsIntegComponent,
     SortableDirective,
     ReportComponent,
     TestUsersComponent,
     
    //  PracticeComponent,
    //  ModelBasicComponent,
    //  FollowupComponent,
    //  AuditComponent,
    //  FollowupTemplateComponent,
    //  FollowupViewComponent,
    //  SearchFilter,
    //  ClaimOpFooterComponent,
    //  ClientAssistanceComponent,
    //  RcmComponent,
    //  ClientNotesComponent,
    //  DocumentsComponent,
    //  NotifyPopupComponent,
    //  SummaryComponent,
    
     
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    HttpClientModule,
    ReactiveFormsModule,
    //  NgBootstrapFormValidationModule.forRoot(),
    //  NgBootstrapFormValidationModule,
    NgDatepickerModule,
    GooglePlaceModule,
    LoadingBarHttpClientModule,
    LoadingBarRouterModule,
    LoadingBarModule.forRoot(),
    HttpModule,
    LoadingBarHttpModule,
    NgxPaginationModule,
    BrowserAnimationsModule,
    NgxDaterangepickerMd,
    // NgxDaterangepickerMd,
    // NgKnifeModule,
    // SelectDropDownModule,
    // AtomSpinnerModule,
    // ToastrModule.forRoot(),
    // NgxPrintModule,
    // NgbModule.forRoot(),
    // FusionChartsModule,
    TooltipModule,
    Ng2SearchPipeModule,
    CommonModuleModule,
    SidebarModule.forRoot(),
    
  ],
  providers: [JarwisService,NotifyService],
  bootstrap: [AppComponent],
  
})
export class AppModule { }
