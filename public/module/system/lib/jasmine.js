define("system/lib/jasmine", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var ExceptionFormatter = (function () {
        function ExceptionFormatter() {
        }
        ExceptionFormatter.prototype.message = function (error) {
            var message = '';
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
        window.jasmine = jasmineRequire.core(jasmineRequire);
        var env = jasmine.getEnv();
        var jasmineInterface = jasmineRequire.interface(jasmine, env);
        extend(window, jasmineInterface);
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
        return env;
    }
    exports.bootJasmine = bootJasmine;
    var TestResultsReporter = (function () {
        function TestResultsReporter($container, stackTraceFormatter) {
            this.suites = [];
            this.summary = {
                noOfTests: 0,
                noOfFailedTests: 0
            };
            this.firstTest = false;
            this.el = $('<div class="panel panel-default test-results"></div>').prependTo($container);
            this.stackTraceFormatter = stackTraceFormatter;
        }
        TestResultsReporter.prototype.jasmineStarted = function (suiteInfo) {
            this.el.prepend('<div class="panel-heading">Testing results</div>');
            this.el.append('<div class="panel-body"></div>');
            this.append('<div class="test-results__intro">Total tests: ' + this.escape((suiteInfo.totalSpecsDefined || 0) + '') + '</div>');
            this.summary.noOfFailedTests = this.summary.noOfTests = 0;
            this.suites = [];
        };
        TestResultsReporter.prototype.jasmineDone = function (runDetails) {
            var summary = this.summary;
            this.append('All tests completed.<br>Passed: ' + this.escape((summary.noOfTests - summary.noOfFailedTests) + '') + '/' + this.escape(summary.noOfTests + ''));
            this.el.addClass(summary.noOfFailedTests > 0 ? 'test-results__failed' : 'test-results__successful');
        };
        TestResultsReporter.prototype.suiteStarted = function (result) {
            var suiteTitle = result.description;
            this.append('<h5 class="test-results__suite test-results__suite_started">'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Suite \'' + this.escape(suiteTitle) + '\' started...'
                + '</h5>');
            this.suites.push({
                title: suiteTitle,
                noOfTests: 0,
                noOfFailedTests: 0
            });
            this.firstTest = true;
        };
        TestResultsReporter.prototype.suiteDone = function (result) {
            var suite = this.suites.pop();
            this.append('<h5 class="test-results__suite test-results__suite_finished">'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Suite \'' + this.escape(suite.title) + '\' finished'
                + '<br>'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Passed : ' + (suite.noOfTests - suite.noOfFailedTests) + '/' + suite.noOfTests
                + '</h5>');
            if (this.suites.length === 0) {
                this.append('<hr>');
            }
            this.firstTest = true;
        };
        TestResultsReporter.prototype.specDone = function (result) {
            var success = result.failedExpectations.length === 0;
            var doneHtml = '';
            if (success) {
                doneHtml += this.formatSuccessfulTest(result);
                this.append(doneHtml);
            }
            else {
                doneHtml += this.formatFailedTest(result);
                this.append(doneHtml);
                this.applySourceMaps();
                this.summary.noOfFailedTests++;
            }
            var suite = this.suites[this.suites.length - 1];
            suite.noOfTests++;
            this.summary.noOfTests++;
            if (!success) {
                suite.noOfFailedTests++;
            }
        };
        TestResultsReporter.prototype.applySourceMaps = function () {
            var self = this;
            self.el.find('.test-results__stack-trace:not(.processed)').each(function () {
                var $el = $(this);
                $el.addClass('processed');
                var $stackTrace = $el.find('.test-results__stack');
                self.stackTraceFormatter($stackTrace.text())
                    .then(function (stack) {
                    stack = self.highlightStackTraceLines(stack);
                    $stackTrace.html(stack);
                    $el.find('.test-results__stack-loading-indicator').remove();
                    $stackTrace.show();
                });
            });
        };
        TestResultsReporter.prototype.formatSuccessfulTest = function (result) {
            var indent = '';
            if (this.firstTest) {
                indent = this.indent(this.suites.length);
                this.firstTest = false;
            }
            var testTitle = result.description;
            var doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
            doneHtml += ' test-results__successful-test">✓</span>';
            return doneHtml;
        };
        TestResultsReporter.prototype.formatFailedTest = function (result) {
            var indent = '';
            if (this.firstTest) {
                indent = this.indent(this.suites.length);
                this.firstTest = false;
            }
            var testTitle = result.description;
            var doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
            doneHtml += ' test-results__failed-test">✕</span> ' + this.escape(testTitle);
            for (var i = 0; i < result.failedExpectations.length; i++) {
                var expectation = result.failedExpectations[i];
                doneHtml += '<div class="test-results__failed-test-message">' + this.escape(expectation.message) + '</div>';
                doneHtml += '<pre class="test-results__stack-trace"><div class="test-results__stack-loading-indicator">Loading stack trace, please wait...</div><div class="test-results__stack" style="display: none;">' + this.escape(expectation.stack) + '</div></pre>';
            }
            return doneHtml;
        };
        TestResultsReporter.prototype.highlightStackTraceLines = function (stack) {
            var _this = this;
            var lines = stack.split("\n");
            var isTsLine = function (line) { return /\s*at.*?\s+\([^)]+\.ts:\d+:\d+\)$/.test(line); };
            return lines.map(function (line, index) {
                line = line.trim();
                if (isTsLine(line)) {
                    var lastTsLine = lines[index + 1] === undefined || !isTsLine(lines[index + 1]);
                    return '<div class="test-results__stack-line test-results__stack-line_ts' + (lastTsLine ? ' test-results__stack-line_ts-last' : '') + '">' + _this.escape(line) + '</div>';
                }
                return "<div class=\"test-results__stack-line\">" + _this.escape(line) + "</div>";
            }).join("");
        };
        TestResultsReporter.prototype.escape = function (str) {
            return jasmineRequire.util().htmlEscape(str);
        };
        TestResultsReporter.prototype.append = function (html) {
            this.el.find('.panel-body').append(html);
        };
        TestResultsReporter.prototype.dump = function (obj) {
            return '<pre>' + this.escape(JSON.stringify(obj)) + '</pre>';
        };
        TestResultsReporter.prototype.indent = function (length) {
            var s = '';
            for (var i = 0; i < length; i++) {
                s += '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            return s;
        };
        return TestResultsReporter;
    }());
    exports.TestResultsReporter = TestResultsReporter;
});
//# sourceMappingURL=jasmine.js.map
