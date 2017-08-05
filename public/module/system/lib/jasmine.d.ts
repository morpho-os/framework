interface Window {
    jasmine: Jasmine;
}

interface Jasmine {
}

interface JsApiReporter {
}

interface JasmineInterface {
    jsApiReporter: JsApiReporter;
}

interface JasmineRequire {
    core(jasmineRequire: JasmineRequire): Jasmine;
    html(jasmine: Jasmine): void;
    interface(jasmine: Jasmine, env: jasmine.Env): JasmineInterface;
}

declare const jasmineRequire: JasmineRequire;

declare namespace jasmine {
    interface QueryStringOptions {
        getWindowLocation: () => Location;
    }

    //function QueryString(options: QueryStringOptions): void;
    interface QueryString {
        //new (options: QueryStringOptions): QueryString;
        new (options: QueryStringOptions): QueryString;
        //readonly prototype: QueryString;
        getParam(key: string): any;
        navigateWithNewParam(key: string, value: any): void;
        fullStringWithNewParam(key: string, value: any): string;
    }
    const QueryString: QueryString;

    interface Timer {
        new (): Timer;
    }
    const Timer: Timer;

    interface Env {
        catchExceptions(value: boolean): boolean;
        catchingExceptions(): boolean;
    }

    interface HtmlReporterOptions {
        env: jasmine.Env;
        filterSpecs: boolean;
        timer: jasmine.Timer;
        addToExistingQueryString(key: string, value: any): any;
        createElement(): HTMLElement;
        createTextNode(): Text;
        getContainer(): HTMLElement;
        onRaiseExceptionsClick(): void;
        onRandomClick(): void;
        onThrowExpectationsClick(): void;
    }
    interface HtmlReporter {
        new (options: HtmlReporterOptions): any;
    }

    interface HtmlSpecFilter {
        new (options: HtmlSpecFilterOptions): any;
    }
    interface HtmlSpecFilterOptions {
        filterString(): any;
    }
}
