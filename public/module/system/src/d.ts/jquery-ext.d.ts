/// <reference path="jquery.d.ts" />

interface JQueryStatic {
    resolvedPromise<T>(value?: T, ...args: any[]): JQueryPromise<T>;

    isPromise(value: any): boolean;
}
