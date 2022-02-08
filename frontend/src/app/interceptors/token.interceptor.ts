import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor
} from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { UserService } from '../services/user.service';

@Injectable()
export class TokenInterceptor implements HttpInterceptor {

  constructor(private userService: UserService) { }

  intercept(request: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    if (!request.url.includes(environment.apiUrl)) {
      return next.handle(request);
    }

    let accessToken = this.userService.accessTokenValue;
    if (accessToken) {
      request = request.clone({
        setHeaders: {
          'Authorization': `Bearer ${accessToken}`
        }
      });
    }

    return next.handle(request);
  }
}
