import { CalendarDay } from "./calendar-day";
import { Hour } from "./hour";

export class CalendarWeek {


    private _days: CalendarDay[];
    public get days(): CalendarDay[] {
        return this._days;
    }
    public set days(v: CalendarDay[]) {
        this._days = v;
    }

    constructor(days: CalendarDay[]) {
        this.days = days;
    }

    public day(dayName: 'mon' | 'tue' | 'wed' | 'thu' | 'fri' | 'sat' | 'sun'): CalendarDay {
        let dayIndex: 0 | 1 | 2 | 3 | 4 | 5 | 6;
        switch (dayName) {
            case 'mon':
                dayIndex = 0;
                break;
            case 'tue':
                dayIndex = 1;
                break;
            case 'wed':
                dayIndex = 2;
                break;
            case 'thu':
                dayIndex = 3;
                break;
            case 'fri':
                dayIndex = 4;
                break;
            case 'sat':
                dayIndex = 5;
                break;
            case 'sun':
                dayIndex = 6;
                break;
        }

        if (dayIndex >= this.days.length) {
            throw new Error('Week does not contain a day at index ' + dayIndex);
        }

        return this.days[dayIndex];
    }

    public get allHours(): Hour[] {
        const all: Hour[] = [];
        this.days.forEach(day => {
            day.hours.forEach(hour => {
                all.push(hour);
            });
        });

        return all;
    }

    public get hoursTotalNum(): number {
        let total: number = 0;
        this.days.forEach(day => {
            day.hours.forEach(hour => {
                total += hour.hours
            });
        });

        return total;
    }
}