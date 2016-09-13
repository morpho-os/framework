/// <reference path="message"/>

export class Application {
    public static main() {
        (<any>window).pageMessenger = new PageMessageManager('#page-messages');
    }
}