import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class TokenService {
  private baseUrl = `${environment.apiUrl}`;
  private iss = {
    /* login:   "http://localhost:8000/api/login",
    dashboard:  "http://localhost:8000/api/dashboard",
    getroles:   "http://localhost:8000/api/getroles",
    checktoken: "http://localhost:8000/api/checktoken",
    getfields:  "http://localhost:8000/api/getfields" */

    // login:      "http://127.0.0.1:8000/api/login",
    // dashboard:  "http://127.0.0.1:8000/api/dashboard",
    // getroles:   "http://127.0.0.1:8000/api/getroles",
    // checktoken: "http://127.0.0.1:8000/api/checktoken",
    // getfields:  "http://127.0.0.1:8000/api/getfields"

    /* login:   "http://35.226.72.203/avecarm/backend/public/index.php/api/login",
    dashboard:  "http://35.226.72.203/avecarm/backend/public/index.php/api/dashboard",
    getroles:   "http://35.226.72.203/avecarm/backend/public/index.php/api/getroles",
    checktoken: "http://35.226.72.203/avecarm/backend/public/index.php/api/checktoken",
    getfields:  "http://35.226.72.203/avecarm/backend/public/index.php/api/getfields" */

    login:      `${this.baseUrl}/login`,
    dashboard:  `${this.baseUrl}/dashboard`,
    getroles:   `${this.baseUrl}/getroles`,
    checktoken: `${this.baseUrl}/checktoken`,
    getfields:  `${this.baseUrl}/getfields`


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
    console.log(token);
    if (token) {
      console.log(token);
      const payload = this.payload(token);
      if (payload) {
        let payloadindex = Object.values(this.iss).indexOf(payload.iss);
        console.log(payloadindex);
        let data=Object.values(this.iss).indexOf(payload.iss) > -1 ? true : false;
        console.log("payload-present");
        console.log(data);
        return data;
        }
        else{
          console.log("no-payload");
          return true;
        }
        }
        console.log("no-token");
        return false;
        }
      
  payload(token) {
    const payload = token.split('.')[1];
    console.log(payload);
    return this.decode(payload);
  }
  decode(payload) {
    console.log(JSON.parse(atob(payload)));
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
