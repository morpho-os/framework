/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

/// <amd-module name="localhost/lib/widget" />

import {EventManager} from "./event-manager";

export interface WidgetConfig {
    el?: JQuery | string;
}

export abstract class Widget<TConfig extends WidgetConfig = WidgetConfig> extends EventManager {
    protected el!: JQuery;

    protected config: TConfig;

    public constructor(config: TConfig) {
        super();
        this.config = this.normalizeConfig(config);
        this.init();
        this.registerEventHandlers();
    }

    protected init(): void {
        if (this.config && this.config.el) {
            this.el = $(<string>this.config.el);
        }
    }

    protected registerEventHandlers(): void {
    }

    protected normalizeConfig(config: TConfig): TConfig {
        return config;
    }
}
/*
 class ProgressBar extends Widget {

 }

 class Menu extends Widget {

 }

 class Window extends Widget {}

 class ModalWindow extends Window {

 }
 */
