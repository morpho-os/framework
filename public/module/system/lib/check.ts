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
