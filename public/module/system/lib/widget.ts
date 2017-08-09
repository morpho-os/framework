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
