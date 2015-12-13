/// <reference path="bom.d.ts" />
/// <reference path="jquery-ext.d.ts" />
/// <reference path="event-manager.d.ts" />

declare namespace System {
    abstract class Widget extends EventManager {
        protected el: JQuery;
        constructor(el: any);
        protected registerEventHandlers(): void;
        protected init(): void;
        protected showEl(el: JQuery): void;
        protected hideEl(el: JQuery): void;
        protected forEach(items: any, fn: any): void;
        protected forEachEl(items: any, fn: any): void;
    }
    class Window extends Widget {
    }
}