interface JQueryStatic {
    resolvedPromise(value?: any, ...args: any[]): JQueryPromise<any>;
    rejectedPromise(value?: any, ...args: any[]): JQueryPromise<any>;
}
interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

(() => {
    let uniqId: number = 0;
    $.fn.once = function (this: JQuery, fn: (key: any, value: any) => any): JQuery {
        let cssClass: string = String(uniqId++) + '-processed';
        return this.not('.' + cssClass)
            .addClass(cssClass)
            .each(fn);
    };
})();

$.resolvedPromise = function (value?: any, ...args: any[]): JQueryPromise<any> {
    return $.Deferred().resolve(value, ...args).promise();
};

$.rejectedPromise = function (value?: any, ...args: any[]): JQueryPromise<any> {
    return $.Deferred().reject(value, ...args).promise();
};
