import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Subject } from 'rxjs';
import { filter, take, takeUntil } from 'rxjs/operators';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  public loginValid = true;
  public errorMsg = "";


  private readonly returnUrl: string;

  form: FormGroup = this.fb.group({
    email: [null, [Validators.required, Validators.email]],
    password: [null, [Validators.required]]
  });

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private fb: FormBuilder
  ) {
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/';
  }

  public ngOnInit(): void {
    if (this.userService.accessTokenValue) {
      this.router.navigateByUrl(this.returnUrl);
    }
  }


  public onSubmit(): void {
    this.loginValid = true;

    this.userService.login(this.form.value.password, this.form.value.email).pipe(
      take(1)
    ).subscribe({
      next: response => {
        this.loginValid = true;
        this.router.navigateByUrl('/');
      },
      error: response => {
        this.loginValid = false;
        this.errorMsg = response.error;
      }
    });
  }
}