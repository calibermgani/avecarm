import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators, FormBuilder } from "@angular/forms";
import { JarwisService } from '../../Services/jarwis.service';
import { TokenService } from '../../Services/token.service';
import { Router } from '@angular/router';
import { AuthService } from '../../Services/auth.service';
import { SetUserService } from '../../Services/set-user.service';
import { DomSanitizer } from '@angular/platform-browser';
import { ToastrManager } from 'ng6-toastr-notifications';
@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})

export class ProfileComponent implements OnInit {
  url ;
  public len = null;
  // public notification = null;
  public error;
  zip = '';
  public result = null;

  constructor(
    private Jarwis: JarwisService,
    private Token: TokenService,
    private router: Router,
    private auth: AuthService,
    private setus: SetUserService,
    private sanitizer:DomSanitizer,
    public toastr: ToastrManager,
    private formBuilder: FormBuilder
  ) { }



  formGroup: FormGroup;
  submitted = false;

  public form = {
    username: null,
    firstname: null,
    lastname: null,
    dob: null,
    phone: null,
    address1: null,
    address2: null,
    city: null,
    state: null,
    zip: null
  };

  clearImage() {
    this.url = "assets/image/No_Img.png";
  }


  fileData(data) {
    //console.log(data)
    this.url = data.data;
  }

  fileError(error) {
    this.handleError(error);
  }

  onFileUpload(event) {
    if (event.target.files && event.target.files[0]) {
      var reader = new FileReader();
      reader.readAsDataURL(event.target.files[0]); // read file as data url
      reader.onload = (event) => { // called once readAsDataURL is completed
      let res = event.target;
      this.url = res['result'];

      this.Jarwis.setimage( res['result'], this.setus.getId()).subscribe(
      data => this.fileData(data),
      error => this.fileError(error)
        );
      }

    }
  }

  base64Image;
  handleResponse(data) {
    this.formGroup.patchValue({
      username: data.user.firstname+' '+data.user.lastname,
      firstname: data.user.firstname,
      lastname: data.user.lastname,
      dob:{year:data.profile.dob[2],month:data.profile.dob[1],day:data.profile.dob[0]},
      // dob: {startDate: data.profile.dob, endDate: data.profile.dob},
      phone: data.profile.mobile_phone,
      address1: data.address.address_line_1,
      address2: data.address.address_line_2,
      city: data.address.city,
      state: data.address.state,
      zip: data.address.zip4,
    });


    this.base64Image =data.image;
    this.transform();
  }
  transform(){
    this.url =  this.sanitizer.bypassSecurityTrustResourceUrl("data:image/png;base64,"+this.base64Image);
}



public handleError(error) {
    console.log(error);
  }

  private fetchdata() {
    this.Jarwis.getprofile(this.setus.getId()).subscribe(
      data => this.handleResponse(data),
      error => this.handleError(error)
    );
  }
  // onSubmit() {
  //   this.Jarwis.updateprofile(this.formGroup.value, this.setus.getId()).subscribe(
  //     data => this.notifyy(data)
  //   );
  // }
  onSubmit() {
    this.submitted = true;
    if (this.formGroup.invalid) {
      console.log('Error');
        return;
    }
    try{
      this.Jarwis.updateprofile(this.formGroup.value, this.setus.getId()).subscribe(
      data => this.notifyy(data)
    );
    }catch (error) {
     this.toastr.errorToastr('Error in updating profile')
    }

  }
  RespponseHandleCreatePractice(data){
    this.toastr.successToastr("Profile updated successfully!")
  }
  public validation(error){
    this.toastr.errorToastr("Fill the all required fields")
  }
  notifyy(data) {
     this.toastr.successToastr('Profile Updated');
    this.fetchdata();
    // setTimeout(() => {
    //   this.notification = '';
    // }, 1500);
  }
  notify(error) {
    this.error = error.error.error;
  }
  handlemessage(data) {
    this.error = '';
  }
  onBlur() {
    this.Jarwis.validateUsername(this.formGroup.value, this.setus.getId()).subscribe(
      message => this.handlemessage(message),
      error => this.notify(error)
    );
  }
  public handleAddressChange(address) {
    this.len = address.address_components.length;
    if (!Number('this.len-1') == false) {
      this.zip = "";
    }
    else {
      this.zip = address.address_components[this.len - 1].long_name;
    }
    this.formGroup.patchValue({
      zip: this.zip,
      state: address.address_components[this.len - 3].long_name,
      city: address.address_components[this.len - 4].long_name,
      address2: address.address_components[this.len - 5].long_name
    });
  }
  ngOnInit() {

    // this.formGroup = new FormGroup({
    //   username: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[a-zA-Z]+\w*$/)
    //   ]),
    //   firstname: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[A-Za-z]+$/)
    //   ]),
    //   lastname: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[A-Za-z]+$/)
    //   ]),
    //   dob: new FormControl('', [
    //     Validators.required
    //   ]),
    //   phone: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[0-9-]*$/),
    //     Validators.minLength(10)
    //   ]),
    //   address1: new FormControl('', [
    //     Validators.required
    //   ]),
    //   address2: new FormControl('', [
    //     Validators.required
    //   ]),
    //   city: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[A-Za-z ]+$/)
    //   ]),
    //   state: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[A-Za-z ]+$/)
    //   ]),
    //   zip: new FormControl('', [
    //     Validators.required,
    //     Validators.pattern(/^[0-9-]*$/)
    //   ])
    //
    // });

    this.fetchdata();
    this.formValidators();
  }
  get f() { return this.formGroup.controls; }

  formValidators(){
      this.formGroup = this.formBuilder.group({
        username: [''],
        firstname: ['', [Validators.required, Validators.pattern(/^[A-Za-z]+$/)]],
        lastname: ['', [Validators.required, Validators.pattern(/^[A-Za-z]+$/)]],
        dob: ['', Validators.required],
        phone: ['', [Validators.required, Validators.pattern(/^[0-9-]*$/), Validators.minLength(10)]],
        address1: ['', Validators.required],
        address2: ['', Validators.required],
        city: ['', [Validators.required, Validators.pattern(/^[A-Za-z ]+$/)]],
        state: ['', [Validators.required, Validators.pattern(/^[A-Za-z ]+$/)]],
        zip: ['', [Validators.required, Validators.pattern(/^[0-9-]*$/)]]
      });
    }
}
