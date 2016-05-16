declare namespace Morpho.System {
    class EventManager {
        private eventHandlers;
        on(eventName: string, handler: (...args: any[]) => any): void;
        trigger(eventName: string, ...args: any[]): void;
    }
}