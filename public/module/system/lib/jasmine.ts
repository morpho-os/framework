/* tslint:disable */
/**
 * This file is based on code found in the 'jasmine' npm module (https://jasmine.github.io/).
 *
 * Copyright (c) 2008-2017 Pivotal Labs
 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 "Software"), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
/* tslint:enable */

/// <reference path="jasmine.d.ts"/>

/*
interface ExpectationResult {
    matcherName: string;
    message: string;
    stack: string;
    passed: boolean;
    expected?: string;
    actual?: string;
/*
    see ExpectationResult in @types
    passed(): boolean;
    expected: any;
    actual: any;
    trace: Trace;
* /
}

interface ExpectationResultBuilderOptions {
    messageFormatter: (error: any) => string;
    stackFormatter: (error: Error | null) => string | null;
    matcherName: string;
    passed: boolean;
    expected: string;
    actual: string;
    message: string;
    error: Error;
}

function buildExpectationResult() {
    return function (options: ExpectationResultBuilderOptions): ExpectationResult {
        const messageFormatter = options.messageFormatter,
            stackFormatter = options.stackFormatter;

        const result: ExpectationResult = {
            matcherName: options.matcherName,
            message: message(),
            stack: stack(),
            passed: options.passed
        };

        if (!result.passed) {
            result.expected = options.expected;
            result.actual = options.actual;
        }

        return result;

        function message() {
            if (options.passed) {
                return 'Passed.';
            } else if (options.message) {
                return options.message;
            } else if (options.error) {
                return messageFormatter(options.error);
            }
            return '';
        }

        function stack() {
            if (options.passed) {
                return '';
            }

            let error = options.error;
            if (!error) {
                try {
                    throw new Error(message());
                } catch (e) {
                    error = e;
                }
            }
            return stackFormatter(error);
        }
    };
}
*/

class ExceptionFormatter {
    public message(error: any): string {
        let message = '';

        if (error.name && error.message) {
            message += error.name + ': ' + error.message;
        } else {
            message += error.toString() + ' thrown';
        }

        if (error.fileName || error.sourceURL) {
            message += ' in ' + (error.fileName || error.sourceURL);
        }

        if (error.line || error.lineNumber) {
            message += ' (line ' + (error.line || error.lineNumber) + ')';
        }

        return message;
    }

    public stack(error: Error | null): string {
        if (!error) {
            return '';
        }
        //console.log((<any>error).fileName, (<any>error).sourceURL, (<any>error).line, (<any>error).lineNumber)
        return error.stack;
    }
}

export function bootJasmine(): jasmine.Env {
    jasmineRequire.ExceptionFormatter = () => { return ExceptionFormatter; };
    //jasmineRequire.buildExpectationResult = buildExpectationResult;

    /**
     * ## Require &amp; Instantiate
     *
     * Require Jasmine's core files. Specifically, this requires and attaches all of Jasmine's code to the `jasmine` reference.
     */
    window.jasmine = jasmineRequire.core(jasmineRequire);

    /**
     * Create the Jasmine environment. This is used to run all specs in a project.
     */
    const env = jasmine.getEnv();

    /**
     * ## The Global Interface
     *
     * Build up the functions that will be exposed as the Jasmine public interface. A project can customize, rename or alias any of these functions as desired, provided the implementation remains unchanged.
     */
    const jasmineInterface = jasmineRequire.interface(jasmine, env);

    /**
     * Add all of the Jasmine global/public interface to the global scope, so a project can use the public interface directly. For example, calling `describe` in specs instead of `jasmine.getEnv().describe`.
     */
    extend(window, jasmineInterface);

    /**
     * ## Runner Parameters
     *
     * More browser specific code - wrap the query string in an object and to allow for getting/setting parameters from the runner user interface.
     */

    /*
    const queryString = new jasmine.QueryString({
        getWindowLocation() { return window.location; }
    });

    const filterSpecs = !!queryString.getParam("spec");

    const catchingExceptions = queryString.getParam("catch");
    env.catchExceptions(typeof catchingExceptions === "undefined" ? true : catchingExceptions);

    const throwingExpectationFailures = queryString.getParam("throwFailures");
    env.throwOnExpectationFailure(throwingExpectationFailures);

    const random = queryString.getParam("random");
    env.randomizeTests(random);

    const seed = queryString.getParam("seed");
    if (seed) {
        env.seed(seed);
    }
    */

    /**
     * Filter which specs will be run by matching the start of the full name against the `spec` query param.
     */
/*    const specFilter = new jasmine.HtmlSpecFilter({
        filterString() { return queryString.getParam("spec"); }
    });

    env.specFilter = function(spec) {
        return specFilter.matches(spec.getFullName());
    };*/

    /**
     * Setting up timing functions to be able to be overridden. Certain browsers (Safari, IE 8, phantomjs) require this hack.
     */
    window.setTimeout = window.setTimeout;
    window.setInterval = window.setInterval;
    window.clearTimeout = window.clearTimeout;
    window.clearInterval = window.clearInterval;

    /**
     * ## Execution
     *
     * Replace the browser window's `onload`, ensure it's called, and then run all of the loaded specs. This includes initializing the `HtmlReporter` instance and then executing the loaded Jasmine environment. All of this will happen after all of the specs are loaded.
     */
    /*    const currentWindowOnload = window.onload;
        window.onload = function(this: Window, ev: Event): any {
            if (currentWindowOnload) {
                currentWindowOnload.call(this, ev);
            }

        };*/

    /**
     * Helper function for readability above.
     */
    function extend(destination: any, source: any): any {
        // tslint:disable-next-line:forin
        for (let property in source) {
            destination[property] = source[property];
        }
        return destination;
    }

    return env;
}

interface TestStats {
    noOfTests: number;
    noOfFailedTests: number;
}

interface SuiteMeta extends TestStats {
    title: string;
}

export class TestResultsReporter implements jasmine.CustomReporter {
    protected el: JQuery;
    protected results: JQuery;
    protected stackTraceFormatter: StackTraceFormatter;
    protected suites: SuiteMeta[] = [];
    protected summary: TestStats = {
        noOfTests: 0,
        noOfFailedTests: 0
    };
    private firstTest = false;

    public constructor($container: JQuery, stackTraceFormatter: StackTraceFormatter) {
        this.el = $('<div class="panel panel-default test-results"></div>').prependTo($container);
        this.stackTraceFormatter = stackTraceFormatter;
    }

    public jasmineStarted(suiteInfo: jasmine.SuiteInfo): void {
        this.el.prepend('<div class="panel-heading">Testing results</div>');
        this.el.append('<div class="panel-body"></div>');
        this.append('<div class="test-results__intro">Total tests: ' + this.escape((suiteInfo.totalSpecsDefined || 0) + '') + '</div>');
        this.summary.noOfFailedTests = this.summary.noOfTests = 0;
        this.suites = [];
    }

    public jasmineDone(runDetails: jasmine.RunDetails): void {
        const summary = this.summary;
        this.append('All tests completed.<br>Passed: ' + this.escape((summary.noOfTests - summary.noOfFailedTests) + '') + '/' + this.escape(summary.noOfTests + ''));
        this.el.addClass(summary.noOfFailedTests > 0 ? 'test-results__failed' : 'test-results__successful');
    }

    public suiteStarted(result: jasmine.CustomReporterResult): void {
        const suiteTitle = result.description;
        this.append('<h5 class="test-results__suite test-results__suite_started">'
            + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
            + 'Suite \'' + this.escape(suiteTitle) + '\' started...'
            + '</h5>'
        );
        this.suites.push({
            title: suiteTitle,
            noOfTests: 0,
            noOfFailedTests: 0
        });
        this.firstTest = true;
    }

    public suiteDone(result: jasmine.CustomReporterResult): void {
        const suite = this.suites.pop();
        this.append('<h5 class="test-results__suite test-results__suite_finished">'
            + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
            + 'Suite \'' + this.escape(suite.title) + '\' finished'
            + '<br>'
            + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
            + 'Passed : ' + (suite.noOfTests - suite.noOfFailedTests) + '/' + suite.noOfTests
            + '</h5>'
        );
        if (this.suites.length === 0) {
            this.append('<hr>');
        }
        this.firstTest = true;
    }

/*    public specStarted(result: jasmine.CustomReporterResult): void {
        this.append(this.ppSpecResult(result));
    }*/

    public specDone(result: jasmine.CustomReporterResult): void {
        // ✓ - U+2713, ✕ - U+2715
        // @TODO: Handle result.status === 'pending'
        const success = result.failedExpectations.length === 0;
        let doneHtml = '';
        if (success) {
            doneHtml += this.formatSuccessfulTest(result);
            this.append(doneHtml);
        } else {
            doneHtml += this.formatFailedTest(result);
            this.append(doneHtml);
            this.applySourceMaps();
            this.summary.noOfFailedTests++;
        }

        const suite = this.suites[this.suites.length - 1];
        suite.noOfTests++;
        this.summary.noOfTests++;
        if (!success) {
            suite.noOfFailedTests++;
        }
    }

    protected applySourceMaps(): void {
        const self = this;
        self.el.find('.test-results__stack-trace:not(.processed)').each(function (this: HTMLElement) {
            const $el = $(this);
            $el.addClass('processed');
            const $stackTrace = $el.find('.test-results__stack');
            self.stackTraceFormatter($stackTrace.text())
                .then(function (stack: string) {
                    stack = self.highlightStackTraceLines(stack);
                    $stackTrace.html(stack);
                    $el.find('.test-results__stack-loading-indicator').remove();
                    $stackTrace.show();
                });
        });
    }

    protected formatSuccessfulTest(result: jasmine.CustomReporterResult): string {
        let indent = '';
        if (this.firstTest) {
            indent = this.indent(this.suites.length);
            this.firstTest = false;
        }
        const testTitle = result.description;
        let doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';

        doneHtml += ' test-results__successful-test">✓</span>';
        return doneHtml;
    }

    protected formatFailedTest(result: jasmine.CustomReporterResult): string {
        let indent = '';
        if (this.firstTest) {
            indent = this.indent(this.suites.length);
            this.firstTest = false;
        }
        const testTitle = result.description;
        let doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
        doneHtml += ' test-results__failed-test">✕</span> ' + this.escape(testTitle);
        for (let i = 0; i < result.failedExpectations.length; i++) {
            const expectation = result.failedExpectations[i];
            doneHtml += '<div class="test-results__failed-test-message">' + this.escape(expectation.message) + '</div>';
            doneHtml += '<pre class="test-results__stack-trace"><div class="test-results__stack-loading-indicator">Loading stack trace, please wait...</div><div class="test-results__stack" style="display: none;">' + this.escape(expectation.stack) + '</div></pre>';
        }
        return doneHtml;
    }

    /**
     * Returns html
     */
    protected highlightStackTraceLines(stack: string): string {
        const lines = stack.split("\n");
        const isTsLine = (line: string): boolean => /\s*at.*?\s+\([^)]+\.ts:\d+:\d+\)$/.test(line);
        return lines.map((line, index) => {
            line = line.trim();
            if (isTsLine(line)) {
                const lastTsLine = lines[index + 1] === undefined || !isTsLine(lines[index + 1]);
                return '<div class="test-results__stack-line test-results__stack-line_ts' + (lastTsLine ? ' test-results__stack-line_ts-last' : '') + '">' + this.escape(line) + '</div>';
            }
            return `<div class="test-results__stack-line">${this.escape(line)}</div>`;
        }).join("");
    }

    private escape(str: string): string {
        return jasmineRequire.util().htmlEscape(str);
    }

    private append(html: string): void {
        this.el.find('.panel-body').append(html);
    }

    private dump(obj: any): string {
        return '<pre>' + this.escape(JSON.stringify(obj)) + '</pre>';
    }

    private indent(length: number): string {
        let s = '';
        for (let i = 0; i < length; i++) {
            s += '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        return s;
    }
}
