export class SnackbarAction{
    constructor(
        public title: string,
        public action?: Function,
        public type: 'text'|'icon'|'maticon' = 'text',
    ){
        if(this.action == null){
            this.action = () => {};
        }
    }
}