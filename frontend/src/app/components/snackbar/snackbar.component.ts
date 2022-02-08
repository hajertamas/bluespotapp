import { Component, Inject, OnInit } from '@angular/core';
import { MatSnackBarRef, MAT_SNACK_BAR_DATA } from '@angular/material/snack-bar';
import { SnackbarAction } from 'src/app/models/snackbar-action';
import { SnackbarConfig } from 'src/app/models/snackbar-config';

@Component({
  selector: 'app-snackbar',
  templateUrl: './snackbar.component.html',
  styleUrls: ['./snackbar.component.css']
})
export class SnackbarComponent implements OnInit {

  constructor(
    @Inject(MAT_SNACK_BAR_DATA) public cfg: SnackbarConfig,
    private ref: MatSnackBarRef<SnackbarComponent>
  ) { }

  ngOnInit(): void {
  }

  do(action: SnackbarAction) {
    if (action.action != null) action.action();
    this.ref.dismiss();
  }
}
