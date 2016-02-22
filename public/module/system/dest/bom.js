Math.EPS = 0.000001;
Math.roundFloat = function (val, precision) {
    if (precision === void 0) { precision = 2; }
    var dd = Math.pow(10, precision);
    return Math.round(val * dd) / dd;
};
Math.isFloatLessThanZero = function (val) {
    return val < -Math.EPS;
};
Math.isFloatGreaterThanZero = function (val) {
    return val > Math.EPS;
};
Math.isFloatEqualZero = function (val) {
    return Math.abs(val) <= Math.EPS;
};
Math.isFloatsEqual = function (a, b) {
    return Math.isFloatEqualZero(a - b);
};
String.prototype.escapeHtml = function () {
    var entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };
    return this.replace(/[&<>"'\/]/g, function (s) {
        return entityMap[s];
    });
};
String.prototype.titleize = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};
