/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
interface JQueryStatic {
    resolvedPromise(value?: any, ...args: any[]): JQueryPromise<any>;
    rejectedPromise(value?: any, ...args: any[]): JQueryPromise<any>;
}
interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

interface Math {
    EPS: number;
    roundFloat(val: number, precision: number): number;
    floatLessThanZero(val: number): boolean;
    floatGreaterThanZero(val: number): boolean;
    floatEqualZero(val: number): boolean;
    floatsEqual(a: number, b: number): boolean;
}

interface String {
    encodeHtml(): string;
    titleize(): string;
}

interface JsonResponse {
    error: any;
    success: any;
}
