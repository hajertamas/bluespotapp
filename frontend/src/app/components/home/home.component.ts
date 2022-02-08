import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { MatNativeDateModule } from '@angular/material/core';
import { MatDatepicker } from '@angular/material/datepicker';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar, MatSnackBarRef, TextOnlySnackBar } from '@angular/material/snack-bar';
import { map } from 'rxjs/operators';
import { CalendarDay } from 'src/app/models/calendar-day';
import { CalendarWeek } from 'src/app/models/calendar-week';
import { Hour } from 'src/app/models/hour';
import { SnackbarAction } from 'src/app/models/snackbar-action';
import { SnackbarConfig } from 'src/app/models/snackbar-config';
import { HoursService } from 'src/app/services/hours.service';
import { AddHoursComponent } from '../add-hours/add-hours.component';
import { SnackbarComponent } from '../snackbar/snackbar.component';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {

  public monthNames: string[] = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ];

  public readonly cellColors: any = {
    blank: '#ffffff00',
    disabled: '#e5e5e5',
    today: '#eeffee',
    full: '#ffeeee',
    fullToday: '#eeeeff'
  }

  public weeks: CalendarWeek[] = [];

  displayedColumns: string[] = ['mon', 'tue', 'wed', 'thu', 'fri', 'hrs_total'];

  private _currentMonth: Date;
  public get currentMonth(): Date {
    return this._currentMonth;
  }
  public set currentMonth(v: Date) {
    this._currentMonth = v;

    const firstDayOfMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
    this.generateCalendarDays(firstDayOfMonth);

  }

  public get currentMonthDisplay(): string {
    return "" + this.currentMonth.getFullYear() + " " + this.monthNames[this.currentMonth.getMonth()].substring(0, 3);
  }

  public switchMonth(date: Date, datepicker: MatDatepicker<Date>) {
    this.currentMonth = date;
    datepicker.close();
  }

  public jumpToCurrentMonth() {
    this.currentMonth = new Date();
  }

  public refresh() {
    this.currentMonth = this.currentMonth;
  }

  public nextMonth() {
    const next = new Date(this.currentMonth.setMonth(this.currentMonth.getMonth() + 1));
    this.currentMonth = next;
  }

  public prevMonth() {
    const prev = new Date(this.currentMonth.setMonth(this.currentMonth.getMonth() - 1));
    this.currentMonth = prev;
  }


  constructor(
    private dialog: MatDialog,
    private snackbar: MatSnackBar,
    private hoursService: HoursService
  ) { }

  ngOnInit(): void {
    this.currentMonth = new Date();
  }


  public shouldShowRow(row: 'calendar'|'error'|'loading'): boolean{
    switch(row){
      case 'loading':
        return this.loading;
      case 'error':
        return (!this.loading && this.errorMsg != null);
      case 'calendar':
        return (!this.loading && this.errorMsg == null);
    }

  }

  public errorMsg: string;

  public loading = true;
  private generateCalendarDays(day: Date = new Date()): void {

    this.loading = true;
    this.errorMsg = null;
    this.weeks = [];

    this.hoursService.getForMonth(this.currentMonth).subscribe(
      response => {
        this.loadData(response);
        this.loading = false;
      },
      (error: HttpErrorResponse) => {
        switch(true){
          case error.status == 0:
            this.errorMsg = "Server down, please retry later";
            break;
          default:
            this.errorMsg = "Something went wrong";
            break;
        }
        this.loading = false;
      }
    )

    const startingDateOfCalendar = this.getStartDateForCalendar(day);

    let dateToAdd = startingDateOfCalendar;

    for (var i = 0; i < 6; i++) {
      const week: CalendarDay[] = [];
      let stop = false;
      let hasFromPrevMonth = false;
      let hasFromCurrentMonth = false;
      let hasFromNextMonth = false;

      for (let p = 0; p < 7; p++) {
        const date = new Date(dateToAdd);

        if (date.getMonth() == this.currentMonth.getMonth() + -1 && p < 5) {
          hasFromPrevMonth = true;
        }

        if (date.getMonth() == this.currentMonth.getMonth() && p < 5) {
          hasFromCurrentMonth = true;
        }

        if (date.getMonth() == this.currentMonth.getMonth() + 1 && p < 5) {
          hasFromNextMonth = true;
        }

        week.push(new CalendarDay(date));
        dateToAdd = new Date(dateToAdd.setDate(dateToAdd.getDate() + 1));
      }
      if(hasFromCurrentMonth){
        this.weeks.push(new CalendarWeek(week));
      }
    }
  }

  private getStartDateForCalendar(selectedDate: Date) {

    let lastDayOfPreviousMonth = new Date(selectedDate.setDate(0));

    let startingDateOfCalendar: Date = lastDayOfPreviousMonth;

    if (startingDateOfCalendar.getDay() != 1) {
      do {
        startingDateOfCalendar = new Date(startingDateOfCalendar.setDate(startingDateOfCalendar.getDate() - 1));
      } while (startingDateOfCalendar.getDay() != 1);
    }

    return startingDateOfCalendar;
  }

  private loadData(hours: Hour[] = []) {
    const hrLookup: any = {};
    hours.forEach(hour => {
      const date = new Date(hour.date_day);
      if (!hrLookup[date.toLocaleDateString('en-GB')]) {
        hrLookup[date.toLocaleDateString('en-GB')] = [];
      }
      hrLookup[date.toLocaleDateString('en-GB')].push(hour);
    });

    this.weeks.forEach(week => {
      week.days.forEach(day => {
        if (!this.isCurrentMonth(day)) { return }
        const date = day.date;
        if (hrLookup[date.toLocaleDateString('en-GB')]) {
          day.hours = hrLookup[date.toLocaleDateString('en-GB')];
        }
      });
    });
  }

  public weekNumByDay(day: CalendarDay): number {
    const date = day.date;
    const jan = new Date(date.getFullYear(), 0, 1);

    //@ts-ignore
    const numberOfDays = Math.floor((date - jan) / (24 * 60 * 60 * 1000));
    return Math.ceil((date.getDay() + 1 + numberOfDays) / 7);
  }

  public canReserve(day: CalendarDay): boolean {
    return day.hoursTotalNum < 24;
  }

  public dayCellColor(day: CalendarDay) {
    if (!this.isCurrentMonth(day)) return this.cellColors.disabled;
    if (day.isToday && day.hoursTotalNum >= 24) return this.cellColors.fullToday;
    if (day.isToday) return this.cellColors.today
    if (day.hoursTotalNum >= 24) return this.cellColors.full;
    return this.cellColors.blank;
  }

  public isCurrentMonth(day: CalendarDay) {
    return (day.date.getMonth() == this.currentMonth.getMonth())
  }

  public addHours(day: CalendarDay) {
    this.dialog.open(AddHoursComponent, {
      "data": day,
    }).afterClosed().subscribe((result: HttpErrorResponse | any) => {
      if (!result) {
        return;
      }
      if (result instanceof HttpErrorResponse) {
        const actions: SnackbarAction[] = [
          new SnackbarAction("Retry", () => {
            this.addHours(day);
          }),
          new SnackbarAction("close", null, "maticon")
        ]
        const config = new SnackbarConfig("⚠ Failed to save hours: " + result.error, actions, "#ee3333");
        this.snackbar.openFromComponent(SnackbarComponent, {
          data: config,
          duration: 6000,
          panelClass: 'snackbar-white'
        });
      } else {
        this.snackbar.openFromComponent(SnackbarComponent, {
          data: new SnackbarConfig("✔ Successfully saved", [], '#338833'),
          duration: 3000,
          panelClass: 'snackbar-white'
        });
        this.refresh();
      }
    });
  }
}
