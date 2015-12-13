declare namespace System {
    const enum MessageType {
        Error = 1,
        Warning = 2,
        Info = 4,
        Debug = 8,
        All = 15,
    }
    class Message {
        type: MessageType;
        text: string;
        constructor(type: MessageType, text: string);
        typeToString(): string;
        hasType(type: MessageType): boolean;
    }
}