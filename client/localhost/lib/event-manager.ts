/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/// <amd-module name="localhost/lib/event-manager" />

export class EventManager {
    private eventHandlers: { [eventName: string]: ((...args: any[]) => any)[] } = {};

    public on(eventName: string, handler: (...args: any[]) => any): void {
        this.eventHandlers[eventName] = this.eventHandlers[eventName] || [];
        this.eventHandlers[eventName].push(handler);
    }

    public trigger(eventName: string, ...args: any[]): void {
        let handlers = this.eventHandlers[eventName];
        if (!handlers) {
            return;
        }
        for (let i = 0; i < handlers.length; ++i) {
            if (false === handlers[i](...args)) {
                break;
            }
        }
    }
}
