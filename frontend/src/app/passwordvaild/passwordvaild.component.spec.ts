import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PasswordvaildComponent } from './passwordvaild.component';

describe('PasswordvaildComponent', () => {
  let component: PasswordvaildComponent;
  let fixture: ComponentFixture<PasswordvaildComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PasswordvaildComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PasswordvaildComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
