export function id(value: any): any {
    return value;
}

export function filterStringArgs(str: string, args: string[], filter: (s: string) => string): string {
    args.forEach(function (arg: string, index: number) {
        str = str.replace('{' + index + '}', arg);
    });
    return str;
}

export function isPromise(value: any): boolean {
    return value && $.isFunction(value.promise);
}

// found at Jasmine Testing framework, $.isDomNode
export function isDomNode(obj: any): boolean {
    return obj.nodeType > 0;
}
