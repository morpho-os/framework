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
            handlers[i](...args);
        }
    }
}
