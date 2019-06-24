import {PageMessenger} from "./message";

export class App {
    public context: Record<string, any> = {};

    public constructor() {
        this.context.pageMessenger = new PageMessenger({el: $('#page-messages')});
        this.context = {}
    }
}

declare global {
    interface Window {
        app: App;
    }
}


