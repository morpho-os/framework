define(["require", "exports", "./event-manager"], function (require, exports, event_manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    class Widget extends event_manager_1.EventManager {
        constructor(config) {
            super();
            this.config = this.normalizeConfig(config);
            this.init();
            this.registerEventHandlers();
        }
        init() {
            if (this.config && this.config.el) {
                this.el = $(this.config.el);
            }
        }
        registerEventHandlers() {
        }
        normalizeConfig(config) {
            return config;
        }
    }
    exports.Widget = Widget;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoid2lkZ2V0LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsid2lkZ2V0LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztJQVdBLE1BQXNCLE1BQW9ELFNBQVEsNEJBQVk7UUFLMUYsWUFBbUIsTUFBZTtZQUM5QixLQUFLLEVBQUUsQ0FBQztZQUNSLElBQUksQ0FBQyxNQUFNLEdBQUcsSUFBSSxDQUFDLGVBQWUsQ0FBQyxNQUFNLENBQUMsQ0FBQztZQUMzQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7WUFDWixJQUFJLENBQUMscUJBQXFCLEVBQUUsQ0FBQztRQUNqQyxDQUFDO1FBRVMsSUFBSTtZQUNWLElBQUksSUFBSSxDQUFDLE1BQU0sSUFBSSxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsRUFBRTtnQkFDL0IsSUFBSSxDQUFDLEVBQUUsR0FBRyxDQUFDLENBQVMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsQ0FBQzthQUN2QztRQUNMLENBQUM7UUFFUyxxQkFBcUI7UUFDL0IsQ0FBQztRQUVTLGVBQWUsQ0FBQyxNQUFlO1lBQ3JDLE9BQU8sTUFBTSxDQUFDO1FBQ2xCLENBQUM7S0FDSjtJQXhCRCx3QkF3QkMifQ==