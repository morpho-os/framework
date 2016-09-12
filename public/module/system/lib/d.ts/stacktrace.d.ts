/// <reference path="es6-shim.d.ts" />

declare module StackTrace {
    export function fromError(e: Error, options?: any): Promise<any>;
}