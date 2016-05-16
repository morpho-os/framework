/// <reference path="bom" />
/// <reference path="jquery-ext" />
/// <reference path="event-manager" />

namespace Morpho.System {
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

        protected showEl($el: JQuery): void {
            $el.removeClass('hide').show();
        }

        protected hideEl($el: JQuery): void {
            $el.hide();
        }

        protected forEach(items: any, fn: any): void {
            $.each(items, (key: any, value: any) => {
                fn.call(this, value);
            });
        }

        protected forEachEl(items: any, fn: any): void {
            $.each(items, (key: any, value: any) => {
                fn.call(this, $(value));
            });
        }
    }

    class ProgressBar extends Widget {

    }

    class Menu extends Widget {

    }

    export class Window extends Widget {

    }

    class ModalWindow extends Window {

    }
}