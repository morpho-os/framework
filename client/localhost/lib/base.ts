/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/// <amd-module name="localhost/lib/base" />

export function id(value: any): any {
    return value;
}

export function isPromise(value: any): boolean {
    return value && $.isFunction(value.promise);
}

// found at Jasmine Testing framework, $.isDomNode
export function isDomNode(obj: any): boolean {
    return obj.nodeType > 0;
}

export function isGenerator(fn: Function) {
    return (<any>fn.constructor).name === 'GeneratorFunction';
}

export class Re {
    public static readonly email = /^[^@]+@[^@]+$/;
}
/*
// @TODO: Use global Application
export const uri = new Uri();*/

export function showUnknownError(message?: string): void {
    // @TODO
    alert("Unknown error, please contact support");
}

export function redirectToSelf(): void {
    //redirectTo(window.location.href);
    window.location.reload();
}

export function redirectToHome(): void {
    // @TODO:
    // redirectTo(uri.prependBasePath('/'));
    redirectTo('/');
}

export function redirectTo(uri: string, storePageInHistory = false): void {
    if (storePageInHistory) {
        window.location.href = uri;
    } else {
        window.location.replace(uri);
    }
}

// queryArgs() based on https://github.com/unshiftio/querystringify/blob/master/index.js
export function queryArgs(): JQuery.PlainObject {
    const decode = (input: string): string => decodeURIComponent(input.replace(/\+/g, ' '));

    const parser = /([^=?&]+)=?([^&]*)/g;
    let queryArgs: JQuery.PlainObject = {},
        part;

    while (part = parser.exec(window.location.search)) {
        let key = decode(part[1]),
            value = decode(part[2]);

        // Prevent overriding of existing properties. This ensures that build-in
        // methods like `toString` or __proto__ are not overriden by malicious
        // querystrings.
        if (key in queryArgs) {
            continue;
        }
        queryArgs[key] = value;
    }

    return queryArgs;
}

// https://stackoverflow.com/questions/1909441/how-to-delay-the-keyup-handler-until-the-user-stops-typing/19259625
// https://github.com/dennyferra/TypeWatch/blob/master/jquery.typewatch.js
export function delayedCallback(callback: Function, waitMs: number): (this: any, ...args: any[]) => void {
    let timer = 0;
    return function(this: any): void {
        const self = this;
        const args = arguments;
        clearTimeout(timer); // clear the previous timer and set a new one. It will work if this function is called within the time interval [0..waitMs] and therefor the new timer will be set instead of the previous one.
        timer = setTimeout(function () {
            callback.apply(self, args);
        }, waitMs);
    };
}
