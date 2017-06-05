interface JQueryStatic {
    resolvedPromise(value?: any, ...args: any[]): JQueryPromise<any>;
    isPromise(value: any): boolean;
    isDomNode(obj: any): boolean;
}
interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

let uniqId: number = 0;
$.fn.once = function (this: JQuery, fn: (key: any, value: any) => any): JQuery {
    let cssClass: string = String(uniqId++) + '-processed';
    return this.not('.' + cssClass)
        .addClass(cssClass)
        .each(fn);
};

$.resolvedPromise = function (value?: any, ...args: any[]): JQueryPromise<any> {
    return $.Deferred().resolve(value, ...args).promise();
};

$.isPromise = function (value: any): boolean {
    return value && $.isFunction(value.promise);
};

// found at Jasmine Testing framework, $.isDomNode.
$.isDomNode = function (obj: any): boolean {
    return obj.nodeType > 0;
};