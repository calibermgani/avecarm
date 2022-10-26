import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class TokenService {
  private iss = {
    login:      "http://:8000/api/login",
    dashboard:  "http://localhost:8000/api/dashboard",
    getroles:   "http://localhost:8000/api/getroles",
    checktoken: "http://localhost:8000/api/checktoken",
    getfields:  "http:///api/getfields"

      /*  login:      "http://35.226.72.203/avecarm/backend/public/index.php/api/login",
       dashboard:  "http://35.226.72.203/avecarm/backend/public/index.php/api/dashboard",
       getroles:   "http://35.226.72.203/avecarm/backend/public/index.php/api/getroles",
       checktoken: "http://35.226.72.203/avecarm/backend/public/index.php/api/checktoken",
       getfields:  "http://35.226.72.203/avecarm/backend/public/index.php/api/getfields" */


    };
  constructor() { }

  handle(token) {
    this.set(token);
  }
  set(token) {
    localStorage.setItem('token', token);
  }
  get() {
    return localStorage.getItem('token');
  }
  remove() {
    localStorage.removeItem('token');
  }
  isValid() {
    const token = this.get();
    if (token) {
      const payload = this.payload(token);
      if (payload) {
        let data=Object.values(this.iss).indexOf(payload.iss) > -1 ? true : false;
        return data;
        }
        }
        return false;
        }
      
  payload(token) {
    const payload = token.split('.')[1];
    return this.decode(payload);
  }
  decode(payload) {
    return JSON.parse(atob(payload));
  }
  loggedIn() {
    return this.isValid();
  }
  decodetoken(value) {
    const jwtData = value.split('.')[1];
    const decodedJwtJsonData = window.atob(jwtData)
    const decodedJwtData = JSON.parse(decodedJwtJsonData)
  }

}
