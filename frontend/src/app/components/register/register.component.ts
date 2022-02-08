import { Component, OnDestroy, OnInit } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, ValidationErrors, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Subject } from 'rxjs';
import { filter, take, takeUntil } from 'rxjs/operators';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {

  public loginValid = true;
  public errorMsg = "";


  private readonly returnUrl: string;

  form: FormGroup = this.fb.group({
    username: [null, [Validators.required, Validators.minLength(3)]],
    email: [null, [Validators.required, Validators.email]],
    password: [null, [Validators.required]],
    passwordConfirm: [null, [Validators.required, this.matchValues('password')]]
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

    this.userService.register(this.form.value.password, this.form.value.email, this.form.value.username).pipe(
      take(1)
    ).subscribe({
      next: response => {
        this.router.navigateByUrl('/login');
      },
      error: response => {
        this.errorMsg = response.error;
      }
    });
  }

  public matchValues(matchTo: string): (AbstractControl) => ValidationErrors | null {
    return (control: AbstractControl): ValidationErrors | null => {
      return !!control.parent &&
        !!control.parent.value &&
        control.value === control.parent.controls[matchTo].value
        ? null
        : { isMatching: false };
    };
  }
}