/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/// <amd-module name="localhost/lib/bom" />

// --------------------------------------------------------------------------
// Math

Math.EPS = 0.000001;

Math.roundFloat = function (val: number, precision: number = 2): number {
    const dd = Math.pow(10, precision);
    return Math.round(val * dd) / dd;
};
Math.floatLessThanZero = function (val: number): boolean {
    return val < -Math.EPS;
};
Math.floatGreaterThanZero = function (val: number): boolean {
    return val > Math.EPS;
};
Math.floatEqualZero = function (val: number): boolean {
    return Math.abs(val) <= Math.EPS;
};
Math.floatsEqual = function (a: number, b: number): boolean {
    return Math.floatEqualZero(a - b);
};

// E.g: logN(8, 2) ~> 3
Math.logN = function (n: number, base: number): number {
    return Math.log(n) / Math.log(base);
};

// --------------------------------------------------------------------------
// String

String.prototype.encodeHtml = function (this: string): string {
    const entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        // tslint:disable-next-line:object-literal-sort-keys
        '"': '&quot;',
        "'": '&#39;'
    };
    return this.replace(/[&<>"']/g, function (s: string): string {
        return (<any>entityMap)[s];
    });
};

String.prototype.titleize = function (this: string): string {
    // @TODO
    return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.format = function (this: string, args: string[], filter?: (s: string) => string): string {
    let val = this;
    args.forEach((arg: string, index: number) => {
        val = val.replace('{' + index + '}', filter ? filter(arg) : arg);
    });
    return val;
}

String.prototype.nl2Br = function (): string {
    return this.replace(/\r?\n/g, '<br>');
};
String.prototype.replaceAll = function (search: string, replace: string): string {
    return this.split(search).join(replace);
};

// ----------------------------------------------------------------------------
// RegExp

// https://github.com/benjamingr/RegExp.escape/blob/master/polyfill.js
RegExp.escape = function (s: string): string {
    return String(s).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&');
};
