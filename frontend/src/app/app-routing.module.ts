import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClaimsComponent } from './components/claims/claims.component';
import { BeforeLoginService } from './Services/before-login.service';
import { UserregistrationComponent } from './components/userregistration/userregistration.component';
import { FormsModule }   from '@angular/forms';
import { ProfileComponent } from './components/profile/profile.component';
import { SettingsComponent } from './components/settings/settings.component';
import { AuthGuard } from './Services/auth-guard.service';
import { PracticeComponent } from './components/practice/practice.component';
import { FollowupComponent } from './components/followup/followup.component';
import { AuditComponent } from './components/audit/audit.component';
import { ClientAssistanceComponent } from './components/client-assistance/client-assistance.component';
import { RcmComponent } from './components/rcm/rcm.component';
import { DocumentsComponent } from './components/documents/documents.component';
import { CustomerCreationComponent } from './components/customer-creation/customer-creation.component';
import { UsersComponent } from './components/users/users.component';
import { PracticeListComponent } from './components/practice-list/practice-list.component';
import { ErrorLogComponent } from './components/error-log/error-log.component';
import { RolesComponent } from './components/roles/roles.component';
import { MedcubicsIntegComponent } from './components/medcubics-integ/medcubics-integ.component';
import { ReportComponent } from './components/report/report.component';

const appRoutes: Routes = [
  {
    path: 'login',
    component: LoginComponent,
    canActivate: [BeforeLoginService],
    },
  // {
  //   path: 'dashboard',
  //   component: DashboardComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'claims',
  //   component: ClaimsComponent,
  //   canActivate: [AuthGuard]
  // },
 {
    path: 'registration',
    component: UserregistrationComponent,
    canActivate: [BeforeLoginService]
  },
  {
    path: 'profile',
    component: ProfileComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'settings',
    component: SettingsComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'vendorCreation',
    component: CustomerCreationComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'users',
    component: UsersComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'errorlog',
    component: ErrorLogComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'medcubics',
    component: MedcubicsIntegComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'roles',
    component: RolesComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'practiceList',
    component: PracticeListComponent,
    canActivate: [AuthGuard]
  },
  {
    path: 'reports',
    component: ReportComponent,
    canActivate: [AuthGuard]
  },
  
  // {
  //   path: 'practice',
  //   component: PracticeComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'followup',
  //   component: FollowupComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'audit',
  //   component: AuditComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'client_assistance',
  //   component: ClientAssistanceComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'rcm',
  //   component: RcmComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: 'documents',
  //   component: DocumentsComponent,
  //   canActivate: [AuthGuard]
  // },
  // {
  //   path: '',
  //   component: DashboardComponent,
  //   canActivate: [AuthGuard]
  // },
  {path: '', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  // {path: 'settings', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  // {path: 'profile', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  {path: 'practice', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  {path: 'documents', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  {path: 'audit', loadChildren: './lazy-audit/lazy-audit.module#LazyAuditModule',canActivate: [AuthGuard]},
  {path: 'client_assistance', loadChildren: './lazy-ca/lazy-ca.module#LazyCAModule',canActivate: [AuthGuard]},
  {path: 'claims', loadChildren: './lazy-claims/lazy-claims.module#LazyClaimsModule',canActivate: [AuthGuard]},
  {path: 'followup', loadChildren: './lazy-followup/lazy-followup.module#LazyFollowupModule',canActivate: [AuthGuard]},
  {path: 'rcm', loadChildren: './lazy-rcm/lazy-rcm.module#LazyRCMModule',canActivate: [AuthGuard]},
  {path: 'dashboard', loadChildren: './lazy/lazy.module#LazyModule',canActivate: [AuthGuard]},
  {path: '**',component: PracticeListComponent,canActivate: [AuthGuard]
  },
];

@NgModule({
  imports: [
    RouterModule.forRoot(appRoutes),FormsModule
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
