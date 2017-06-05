interface Math {
    EPS: number;
    roundFloat(val: number, precision: number): number;
    isFloatLessThanZero(val: number): boolean;
    isFloatGreaterThanZero(val: number): boolean;
    isFloatEqualZero(val: number): boolean;
    isFloatEqual(a: number, b: number): boolean;
}

interface String {
    escapeHtml(): string;
    titleize(): string;
}

// ---------------------------------------------------------------------------------------------------------------------

Math.EPS = 0.000001;

Math.roundFloat = function (val: number, precision = 2): number {
    const dd = Math.pow(10, precision);
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
Math.isFloatEqual = function (a: number, b: number): boolean {
    return Math.isFloatEqualZero(a - b);
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