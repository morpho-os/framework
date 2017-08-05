define("system/lib/check", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    function checkEqual(expected, actual) {
        expect(actual).toEqual(expected);
    }
    exports.checkEqual = checkEqual;
    function checkEmpty(arr) {
        checkLength(0, arr);
    }
    exports.checkEmpty = checkEmpty;
    function checkNoEl($el) {
        checkLength(0, $el);
    }
    exports.checkNoEl = checkNoEl;
    function checkLength(expectedLength, list) {
        checkEqual(expectedLength, list.length);
    }
    exports.checkLength = checkLength;
    function checkFalse(actual) {
        expect(actual).toBeFalsy();
    }
    exports.checkFalse = checkFalse;
    function checkTrue(actual) {
        expect(actual).toBeTruthy();
    }
    exports.checkTrue = checkTrue;
    function bootJasmine() {
        window.jasmine = jasmineRequire.core(jasmineRequire);
        jasmineRequire.html(jasmine);
        var env = jasmine.getEnv();
        var jasmineInterface = jasmineRequire.interface(jasmine, env);
        extend(window, jasmineInterface);
        var queryString = new jasmine.QueryString({
            getWindowLocation: function () { return window.location; }
        });
        var filterSpecs = !!queryString.getParam("spec");
        var catchingExceptions = queryString.getParam("catch");
        env.catchExceptions(typeof catchingExceptions === "undefined" ? true : catchingExceptions);
        var throwingExpectationFailures = queryString.getParam("throwFailures");
        env.throwOnExpectationFailure(throwingExpectationFailures);
        var random = queryString.getParam("random");
        env.randomizeTests(random);
        var seed = queryString.getParam("seed");
        if (seed) {
            env.seed(seed);
        }
        var htmlReporter = new jasmine.HtmlReporter({
            env: env,
            onRaiseExceptionsClick: function () { queryString.navigateWithNewParam("catch", !env.catchingExceptions()); },
            onThrowExpectationsClick: function () { queryString.navigateWithNewParam("throwFailures", !env.throwingExpectationFailures()); },
            onRandomClick: function () { queryString.navigateWithNewParam("random", !env.randomTests()); },
            addToExistingQueryString: function (key, value) { return queryString.fullStringWithNewParam(key, value); },
            getContainer: function () { return document.body; },
            createElement: function () { return document.createElement.apply(document, arguments); },
            createTextNode: function () { return document.createTextNode.apply(document, arguments); },
            timer: new jasmine.Timer(),
            filterSpecs: filterSpecs
        });
        env.addReporter(jasmineInterface.jsApiReporter);
        env.addReporter(htmlReporter);
        var specFilter = new jasmine.HtmlSpecFilter({
            filterString: function () { return queryString.getParam("spec"); }
        });
        env.specFilter = function (spec) {
            return specFilter.matches(spec.getFullName());
        };
        window.setTimeout = window.setTimeout;
        window.setInterval = window.setInterval;
        window.clearTimeout = window.clearTimeout;
        window.clearInterval = window.clearInterval;
        function extend(destination, source) {
            for (var property in source) {
                destination[property] = source[property];
            }
            return destination;
        }
        return {
            htmlReporter: htmlReporter,
            env: env
        };
    }
    var _a = bootJasmine(), htmlReporter = _a.htmlReporter, env = _a.env;
    function main() {
        jasmine.getEnv().addReporter({
            jasmineDone: function (runDetails) {
                if (window.location.search.indexOf('selenium') >= 0) {
                    document.getElementById('page-body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
                }
            }
        });
        htmlReporter.initialize();
        env.execute();
    }
    exports.main = main;
});
