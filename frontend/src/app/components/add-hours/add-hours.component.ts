import { HttpErrorResponse } from '@angular/common/http';
import { Component, Inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { CalendarDay } from 'src/app/models/calendar-day';
import { HoursService } from 'src/app/services/hours.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-add-hours',
  templateUrl: './add-hours.component.html',
  styleUrls: ['./add-hours.component.css']
})
export class AddHoursComponent implements OnInit {

  public title: string;
  public errorMsg: string;

  form: FormGroup;

  constructor(
    @Inject(MAT_DIALOG_DATA) public day: CalendarDay,
    private dialogRef: MatDialogRef<AddHoursComponent>,
    private fb: FormBuilder,
    private hoursService: HoursService
  ) {
    this.title = "Add hours on " + this.day.date.toLocaleDateString("hu-HU");
    this.form = this.fb.group({
      hours: [1, [Validators.required, Validators.min(1), Validators.max(this.maxHoursLeft)]],
      description: [null, [Validators.maxLength(250)]]
    });
  }

  ngOnInit(): void {
  }

  public get maxHoursLeft() {
    return 24 - this.day.hoursTotalNum;
  }

  public loading = false;

  public add() {
    if (!this.form.valid) return;
    this.loading = true;
    this.hoursService.add(this.day.date, this.form.value.hours, this.form.value.description)
      .subscribe((response: any) => {
        this.dialogRef.close(response);
      },
      (error: HttpErrorResponse) => {
        this.dialogRef.close(error);
      }, ()=>{
        setTimeout(() => {
          this.errorMsg = "Something went wrong"
          this.loading = false;
        }, 500);
      });
  }

}
