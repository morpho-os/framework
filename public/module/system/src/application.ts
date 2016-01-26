namespace System {
    export class Application {
        public static main() {
            (<any>window).pageMessenger = new System.PageMessageManager('#page-messages');
        }
    }
}