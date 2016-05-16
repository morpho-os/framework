/// <reference path="widget.d.ts" />

declare namespace Morpho.System {
    const enum MessageType {
        Error = 1,
        Warning = 2,
        Info = 4,
        Debug = 8,
        All = 15,
    }
    class MessageManager extends Morpho.System.Widget {
        protected getNumberOfMessages(): number;
        protected getMessageEls(): JQuery;
    }
    class PageMessageManager extends MessageManager {
        protected registerEventHandlers(): void;
        protected registerCloseMessageHandler(): void;
    }
    class Message {
        type: MessageType;
        text: string;
        constructor(type: MessageType, text: string);
        typeToString(): string;
        hasType(type: MessageType): boolean;
    }
}