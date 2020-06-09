interface RegExpConstructor {
    escape(s: string): string;
}

interface JQueryStatic {
    resolvedPromise(...args: any[]): JQueryPromise<any>;

    rejectedPromise(...args: any[]): JQueryPromise<any>;
}

interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

interface String {
    titleize(): string;
    nl2Br(): string;
    replaceAll(search: string, replace: string): string;
    escapeHtml(): string;
}

interface Math {
    EPS: number;

    // Returns x from base^x ~> n, e.g.: logN(8, 2) ~> 3, because 2^3 ~> 8
    logN(n: number, base: number): number;

    roundFloat(val: number, precision: number): number;

    floatLessThanZero(val: number): boolean;

    floatGreaterThanZero(val: number): boolean;

    floatEqualZero(val: number): boolean;

    floatsEqual(a: number, b: number): boolean;
}


interface JQuery {
    once(this: JQuery, fn: (key: any, value: any) => any): JQuery;
}

interface Math {
    EPS: number;
    roundFloat(val: number, precision: number): number;
    floatLessThanZero(val: number): boolean;
    floatGreaterThanZero(val: number): boolean;
    floatEqualZero(val: number): boolean;
    floatsEqual(a: number, b: number): boolean;
}

interface String {
    encodeHtml(): string;
    titleize(): string;
    format(args: string[], filter?: (s: string) => string): string;
}

interface JSONResult<TOK = any, TErr = any> {
    ok: TOK;
    err: TErr;
}
