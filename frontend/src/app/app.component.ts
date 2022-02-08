import { Component } from '@angular/core';
import { User } from './models/user';
import { UserService } from './services/user.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  
  isAuthenticated: boolean = false
  user: User;

  constructor(private userService: UserService){
    userService.accessToken.subscribe(token => {
      this.isAuthenticated = false;
      if(token){
        this.isAuthenticated = true;
      }
    });
  }

  logout(){
    this.userService.logout();
  }
}
