import { Hour } from "./hour";

export class CalendarDay {

    
    private _date : Date;
    public get date() : Date {
        return this._date;
    }
    public set date(v : Date) {
        this._date = v;
    }
    
    private _hours : Hour[];
    public get hours() : Hour[] {
        return this._hours;
    }
    public set hours(v : Hour[]) {
        this._hours = v;
    }

    constructor(date: Date, hours: Hour[] = []) {
        this.date = date;
        this.hours = hours
    }
    
    public get hoursTotalNum(): number{
        let total: number = 0;
        this.hours.forEach(hour => {
            total += hour.hours;
        });

        return total;
    }

    private _isPastDate: boolean;
    public get isPastDate(): boolean {
        if (this._isPastDate != null) {
            return this._isPastDate;
        }

        return this._isPastDate = (this.date.setHours(0, 0, 0, 0) < new Date().setHours(0, 0, 0, 0));
    }

    private _isToday: boolean;
    public get isToday(): boolean {
        if (this._isToday != null) {
            return this._isToday;
        }

        return this._isToday = (this.date.setHours(0, 0, 0, 0) == new Date().setHours(0, 0, 0, 0));
    }

    

}