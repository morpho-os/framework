///<amd-module name="localhost/lib/base/app" />

import {PageMessenger} from "./message";

type TAppContext = Record<string, any>;

export class App {
    public context: TAppContext = {};

    public constructor() {
        this.context.pageMessenger = new PageMessenger({el: $('#page-messages')});
        this.bindEventHandlers();
    }

    protected bindEventHandlers(): void {
    }
}

declare global {
    interface Window {
        app: App;
    }
}
