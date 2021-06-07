/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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