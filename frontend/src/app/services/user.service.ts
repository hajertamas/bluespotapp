import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { BehaviorSubject, from, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from 'src/environments/environment';
import { User } from '../models/user';

@Injectable({
  providedIn: 'root'
})
export class UserService {


  private accessTokenSubject: BehaviorSubject<String>;
  public accessToken: Observable<String>;

  constructor(private http: HttpClient, private router: Router) {
    this.accessTokenSubject = new BehaviorSubject<String>(localStorage.getItem('hours_app_access_token'));
    this.accessToken = this.accessTokenSubject.asObservable();
  }

  public get accessTokenValue(): String {
    return this.accessTokenSubject.value;
  }

  login(password: string, email: string = null, username: string = null) {
    const body = {
      username: username,
      email: email,
      password: password,
      action: 'login'
    }
    return this.http.post<any>(`${environment.apiUrl}?query=user`, body)
      .pipe(map(response => {
        localStorage.setItem('hours_app_access_token', response.token);
        this.accessToken = response.token;
        this.accessTokenSubject.next(response.token);
        return response;
      }));
  }

  register(password: string, email: string, username: string) {
    const body = {
      username: username,
      email: email,
      password: password,
      action: 'register'
    }
    return this.http.post<any>(`${environment.apiUrl}?query=user`, body)
      .pipe(map(response => {
        localStorage.setItem('hours_app_access_token', response.token);
        this.accessToken = response.token;
        this.accessTokenSubject.next(response.token);
        return response;
      }));
  }

  logout() {
    localStorage.removeItem('currentUser');
    localStorage.removeItem('hours_app_access_token');
    this.accessTokenSubject.next(null);
    this.router.navigate(['/login']);
  }
}
