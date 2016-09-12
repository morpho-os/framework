declare namespace Morpho.System {
    class Exception extends Error {
        message: string;
        constructor(message: string);
        toString(): string;
    }
    class NotImplementedException extends Exception {
    }
}