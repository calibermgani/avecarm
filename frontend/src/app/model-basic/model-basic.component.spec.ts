import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ModelBasicComponent } from './model-basic.component';

describe('ModelBasicComponent', () => {
  let component: ModelBasicComponent;
  let fixture: ComponentFixture<ModelBasicComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModelBasicComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ModelBasicComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
