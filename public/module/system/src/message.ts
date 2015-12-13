namespace System {
    export const enum MessageType {
        Error = 1,
        Warning = 2,
        Info = 4,
        Debug = 8,
        All = Error | Warning | Info | Debug
    }

    function messageTypeToString(messageType: MessageType): string {
        switch (messageType) {
            case MessageType.Debug:
                return 'debug';
            case MessageType.Info:
                return 'info';
            case MessageType.Warning:
                return 'warning';
            case MessageType.Error:
                return 'error';
            default:
                throw new Error("Invalid message type");
        }
    }

    export class Message {
        constructor(public type: MessageType, public text: string) {
        }

        public typeToString(): string {
            return messageTypeToString(this.type);
        }

        public hasType(type: MessageType): boolean {
            return this.type === type;
        }
    }
}