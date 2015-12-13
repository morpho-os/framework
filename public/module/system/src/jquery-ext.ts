/// <reference path="d.ts/jquery-ext.d.ts" />

var uniqId: number = 0;
$.fn.once = function (fn: (key: any, value: any) => any): void {
    var cssClass: string = String(uniqId++) + '-processed';
    return this.not('.' + cssClass)
        .addClass(cssClass)
        .each(fn);
};

// @TODO: Use $.extend to extend jQuery instead of (<any>$)?

(<any>$).resolvedPromise = function (value?: any, ...args: any[]): JQueryPromise<any> {
    return $.Deferred().resolve(value, ...args).promise();
};

(<any>$).isPromise = function (value: any): boolean {
    return value && $.isFunction(value.promise);
};

// found at Jasmine Testing framework, j$.isDomNode.
(<any>$).isDomNode = function (obj: any): boolean {
    return obj.nodeType > 0;
};
