import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { RcmComponent } from '../components/rcm/rcm.component';

const routes: Routes = [
  {path: '', component: RcmComponent}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class LazyRCMRoutingModule { }
