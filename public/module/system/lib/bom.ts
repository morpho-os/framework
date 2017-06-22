/// <reference path="bom.d.ts" />

// ---------------------------------------------------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------------------------------------------------

String.prototype.escapeHtml = function (this: String): string {
    const entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };
    return this.replace(/[&<>"'\/]/g, function (s: string): string {
        return (<any>entityMap)[s];
    });
};

String.prototype.titleize = function (this: String): string {
    // @TODO:
    return this.charAt(0).toUpperCase() + this.slice(1)
};