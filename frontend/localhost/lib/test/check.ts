/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
/// <amd-module name="localhost/lib/test/check" />

export function checkEqual(expected: any, actual: any): void {
    expect(actual).toEqual(expected);
}

export function checkEmpty(arr: any[]): void {
    checkLength(0, arr);
}

export function checkNoEl($el: JQuery): void {
    checkLength(0, $el);
}

export function checkLength(expectedLength: number, list: any[] | JQuery) {
    checkEqual(expectedLength, list.length);
}

export function checkFalse(actual: any) {
    expect(actual).toBeFalsy();
}

export function checkTrue(actual: any) {
    expect(actual).toBeTruthy();
}
