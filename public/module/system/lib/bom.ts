/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
Math.EPS = 0.000001;

Math.roundFloat = function (val: number, precision = 2): number {
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

// --------------------------------------------------------------------------

String.prototype.encodeHtml = function (this: String): string {
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

String.prototype.titleize = function (this: String): string {
    // @TODO
    return this.charAt(0).toUpperCase() + this.slice(1);
};

// ----------------------------------------------------------------------------

export function showUnknownError(message?: string): void {
    // @TODO
    alert("Unknown error, please contact support");
}

export function redirectToSelf(): void {
    redirectTo(window.location.href);
}

export function redirectToHome(): void {
    // @TODO:
    // redirectTo(uri.prependWithBasePath('/'));
    redirectTo('/');
}

export function redirectTo(uri: string): void {
    window.location.href = uri;
}
