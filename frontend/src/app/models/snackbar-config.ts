import { SnackbarAction } from "./snackbar-action";

export class SnackbarConfig {

    constructor(
        public message: string,
        public actions: SnackbarAction[] = [],
        public messageColor?: string
    ) {
        if(actions == null){
            this.actions = [];
        }
    }
}