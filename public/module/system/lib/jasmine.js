define("system/lib/jasmine", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    function buildExpectationResult() {
        return function (options) {
            var messageFormatter = options.messageFormatter, stackFormatter = options.stackFormatter;
            var result = {
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
                }
                else if (options.message) {
                    return options.message;
                }
                else if (options.error) {
                    return messageFormatter(options.error);
                }
                return '';
            }
            function stack() {
                if (options.passed) {
                    return '';
                }
                var error = options.error;
                if (!error) {
                    try {
                        throw new Error(message());
                    }
                    catch (e) {
                        error = e;
                    }
                }
                return stackFormatter(error);
            }
        };
    }
    var ExceptionFormatter = (function () {
        function ExceptionFormatter() {
        }
        ExceptionFormatter.prototype.message = function (error) {
            var message = 'Hello, World!';
            if (error.name && error.message) {
                message += error.name + ': ' + error.message;
            }
            else {
                message += error.toString() + ' thrown';
            }
            if (error.fileName || error.sourceURL) {
                message += ' in ' + (error.fileName || error.sourceURL);
            }
            if (error.line || error.lineNumber) {
                message += ' (line ' + (error.line || error.lineNumber) + ')';
            }
            return message;
        };
        ExceptionFormatter.prototype.stack = function (error) {
            if (!error) {
                return '';
            }
            return error.stack;
        };
        return ExceptionFormatter;
    }());
    function bootJasmine() {
        jasmineRequire.ExceptionFormatter = function () { return ExceptionFormatter; };
        jasmineRequire.buildExpectationResult = buildExpectationResult;
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
    exports.bootJasmine = bootJasmine;
});
//# sourceMappingURL=jasmine.js.map
