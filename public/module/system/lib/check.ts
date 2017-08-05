/// <reference path="jasmine.d.ts"/>
export function checkEqual(expected: any, actual: any): void {
    expect(actual).toEqual(expected);
}

export function checkEmpty(arr: any[]): void {
    checkLength(0, arr);
}

export function checkNoEl($el: JQuery): void {
    checkLength(0, $el);
}

export function checkLength(expectedLength: number, list: any[] | JQuery) {
    checkEqual(expectedLength, list.length);
}

export function checkFalse(actual: any) {
    expect(actual).toBeFalsy();
}

export function checkTrue(actual: any) {
    expect(actual).toBeTruthy();
}

/* tslint:disable */
/**
 * Changed version of jasmine-core/lib/jasmine-core/boot.js of the 'jasmine' module.
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
function bootJasmine() {
    /**
     * ## Require &amp; Instantiate
     *
     * Require Jasmine's core files. Specifically, this requires and attaches all of Jasmine's code to the `jasmine` reference.
     */
    window.jasmine = jasmineRequire.core(jasmineRequire);

    /**
     * Since this is being run in a browser and the results should populate to an HTML page, require the HTML-specific Jasmine code, injecting the same reference.
     */
    jasmineRequire.html(jasmine);

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

    /**
     * ## Reporters
     * The `HtmlReporter` builds all of the HTML UI for the runner page. This reporter paints the dots, stars, and x's for specs, as well as all spec names and all failures (if any).
     */
    const htmlReporter = new jasmine.HtmlReporter({
        env,
        onRaiseExceptionsClick() { queryString.navigateWithNewParam("catch", !env.catchingExceptions()); },
        onThrowExpectationsClick() { queryString.navigateWithNewParam("throwFailures", !env.throwingExpectationFailures()); },
        onRandomClick() { queryString.navigateWithNewParam("random", !env.randomTests()); },
        addToExistingQueryString(key, value) { return queryString.fullStringWithNewParam(key, value); },
        getContainer() { return document.body; },
        createElement() { return document.createElement.apply(document, arguments); },
        createTextNode() { return document.createTextNode.apply(document, arguments); },
        timer: new jasmine.Timer(),
        filterSpecs
    });

    /**
     * The `jsApiReporter` also receives spec results, and is used by any environment that needs to extract the results  from JavaScript.
     */
    env.addReporter(jasmineInterface.jsApiReporter);
    env.addReporter(htmlReporter);

    /**
     * Filter which specs will be run by matching the start of the full name against the `spec` query param.
     */
    const specFilter = new jasmine.HtmlSpecFilter({
        filterString() { return queryString.getParam("spec"); }
    });

    env.specFilter = function(spec) {
        return specFilter.matches(spec.getFullName());
    };

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

    return {
        htmlReporter,
        env
    };
}

// We need to boot jasmine on the module inclusion, so that the global 'jasmine' object would be accessible for tests.
const {htmlReporter, env} = bootJasmine();

export function main() {
    jasmine.getEnv().addReporter({
        jasmineDone(runDetails) {
            if (window.location.search.indexOf('selenium') >= 0) {
                document.getElementById('page-body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
                //alert($('.jasmine_html-reporter .jasmine-alert .jasmine-passed').text())
            }
        }
    });

    htmlReporter.initialize();
    env.execute();
}
