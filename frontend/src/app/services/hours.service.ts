import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class HoursService {

  constructor(
    private http: HttpClient
  ) { }

  public add(day: Date, hours: Number, description?: string, user_id?: number): Observable<any> {
    const body = {
      date_day: [day.getFullYear(), (day.getMonth() + 1), day.getDate()].join("-"),
      hours: hours,
      description: description,
      user_id: user_id
    }

    const url = environment.apiUrl + "?query=hours";
    return this.http.post(url, body)
  }

  public getForMonth(date: Date): Observable<HttpErrorResponse|any> {
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);

    const from = [firstDay.getFullYear(), (firstDay.getMonth() + 1), firstDay.getDate()].join("-");
    const to = [lastDay.getFullYear(), (lastDay.getMonth() + 1), lastDay.getDate()].join("-");


    const url = environment.apiUrl + "?query=hours/" + from + "to" + to;

    return this.http.get(url)
  }
}
