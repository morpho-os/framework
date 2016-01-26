/// <reference path="d.ts/bom.d.ts" />

Math.EPS = 0.000001;

Math.roundFloat = function (val: number, precision = 2): number {
    var dd = Math.pow(10, precision);
    return Math.round(val * dd) / dd;
};
Math.isFloatLessThanZero = function (val: number): boolean {
    return val < -Math.EPS;
};
Math.isFloatGreaterThanZero = function (val: number): boolean {
    return val > Math.EPS;
};
Math.isFloatEqualZero = function (val: number): boolean {
    return Math.abs(val) <= Math.EPS;
};
Math.isFloatsEqual = function (a: number, b: number): boolean {
    return Math.isFloatEqualZero(a - b);
};

// ---------------------------------------------------------------------------------------------------------------------

String.prototype.escapeHtml = function () {
    var entityMap = {
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

String.prototype.titleize = function () {
    // @TODO:
    return this.charAt(0).toUpperCase() + this.slice(1)
};

class Exception extends Error {
    //public stack: any;

    constructor(public message: string) {
        super(message);
        this.name = 'Exception';
        this.message = message;
//        this.stack = (<any>new Error()).stack;
    }
    toString() {
        return this.name + ': ' + this.message;
    }
}
class NotImplementedException extends Exception {
}
/*
class NotImplementedError extends Error {
}
*/