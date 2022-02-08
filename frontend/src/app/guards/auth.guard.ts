import { Injectable } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { UserService } from '../services/user.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private dialog: MatDialog,
    private userService: UserService,
    private router: Router
  ) {

  }

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    if (this.dialog.openDialogs.length > 0) {
      this.dialog.closeAll();
      let savedpopstate = window.onpopstate;
      window.onpopstate = function (event) {
        event.preventDefault();
        window.onpopstate = savedpopstate;
      }
      window.history.forward();
      return false;
    }

    const accessToken = this.userService.accessTokenValue;
    if (accessToken) {
      return true;
    }
    
    this.router.navigate(['login'], { queryParams: { returnUrl: state.url } });
    return false;
  }

}
