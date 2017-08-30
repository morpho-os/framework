/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
export function id(value: any): any {
    return value;
}

export function filterStringArgs(str: string, args: string[], filter: (s: string) => string): string {
    args.forEach(function (arg: string, index: number) {
        str = str.replace('{' + index + '}', arg);
    });
    return str;
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
export class Uri {
    public prependWithBasePath(uri: string): string {
        // @TODO
        return uri;
    }
}
// @TODO: Use global Application
export const uri = new Uri();*/
