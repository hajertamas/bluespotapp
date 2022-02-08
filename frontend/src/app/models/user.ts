export class User{
    
    private _username : string;
    public get username() : string {
        return this._username;
    }
    public set username(v : string) {
        this._username = v;
    }
    
    private _email : string;
    public get email() : string {
        return this._email;
    }
    public set email(v : string) {
        this._email = v;
    }
    
}