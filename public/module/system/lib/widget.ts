/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
import {EventManager} from "./event-manager";

export abstract class Widget extends EventManager {
    protected el: JQuery;

    constructor(el: any) {
        super();
        this.el = $(el);
        this.init();
        this.registerEventHandlers();
    }

    protected registerEventHandlers(): void {
    }

    protected init(): void {
    }
}
/*
 class ProgressBar extends Widget {

 }

 class Menu extends Widget {

 }
 */
export class Window extends Widget {

}
/*
 class ModalWindow extends Window {

 }
 */
