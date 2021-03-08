define("localhost/lib/test/jasmine", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.TestResultsReporter = exports.bootJasmine = void 0;
    class ExceptionFormatter {
        message(error) {
            let message = '';
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
        }
        stack(error) {
            if (!error) {
                return '';
            }
            return error.stack || '';
        }
    }
    function bootJasmine() {
        jasmineRequire.ExceptionFormatter = () => { return ExceptionFormatter; };
        window.jasmine = jasmineRequire.core(jasmineRequire);
        const env = jasmine.getEnv();
        const jasmineInterface = jasmineRequire.interface(jasmine, env);
        extend(window, jasmineInterface);
        window.setTimeout = window.setTimeout;
        window.setInterval = window.setInterval;
        window.clearTimeout = window.clearTimeout;
        window.clearInterval = window.clearInterval;
        function extend(destination, source) {
            for (let property in source) {
                destination[property] = source[property];
            }
            return destination;
        }
        return env;
    }
    exports.bootJasmine = bootJasmine;
    class TestResultsReporter {
        constructor(container, stackTraceFormatter) {
            Object.defineProperty(this, "el", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "results", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "stackTraceFormatter", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: void 0
            });
            Object.defineProperty(this, "suites", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: []
            });
            Object.defineProperty(this, "summary", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: {
                    noOfTests: 0,
                    noOfFailedTests: 0
                }
            });
            Object.defineProperty(this, "firstTest", {
                enumerable: true,
                configurable: true,
                writable: true,
                value: false
            });
            this.el = $('<div class="panel panel-default test-results"></div>').prependTo(container);
            this.stackTraceFormatter = stackTraceFormatter;
        }
        jasmineStarted(suiteInfo) {
            this.el.prepend('<div class="panel-heading">Testing results</div>');
            this.el.append('<div class="panel-body"></div>');
            this.append('<div class="test-results__intro">Total tests: ' + this.escape((suiteInfo.totalSpecsDefined || 0) + '') + '</div>');
            this.summary.noOfFailedTests = this.summary.noOfTests = 0;
            this.suites = [];
        }
        jasmineDone(runDetails) {
            const summary = this.summary;
            this.append('All tests completed.<br>Passed: ' + this.escape((summary.noOfTests - summary.noOfFailedTests) + '') + '/' + this.escape(summary.noOfTests + ''));
            this.el.addClass(summary.noOfFailedTests > 0 ? 'test-results__failed' : 'test-results__successful');
        }
        suiteStarted(result) {
            const suiteTitle = result.description;
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
        }
        suiteDone(result) {
            const suite = this.suites.pop();
            this.append('<h5 class="test-results__suite test-results__suite_finished">'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Suite \'' + this.escape(suite.title) + '\' finished'
                + '<br>'
                + this.indent(this.suites.length) + (this.suites.length ? '-&gt; ' : '')
                + 'Passed : ' + (suite.noOfTests - suite.noOfFailedTests) + '/' + suite.noOfTests
                + ' (not aggregating results of descendant suites)</h5>');
            if (this.suites.length === 0) {
                this.append('<hr>');
            }
            this.firstTest = true;
        }
        specDone(result) {
            const success = !result.failedExpectations || result.failedExpectations.length === 0;
            let doneHtml = '';
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
            const suite = this.suites[this.suites.length - 1];
            suite.noOfTests++;
            this.summary.noOfTests++;
            if (!success) {
                suite.noOfFailedTests++;
            }
        }
        applySourceMaps() {
            const self = this;
            self.el.find('.test-results__stack-trace:not(.processed)').each(function () {
                const $el = $(this);
                $el.addClass('processed');
                const $stackTrace = $el.find('.test-results__stack');
                self.stackTraceFormatter($stackTrace.text())
                    .then(function (stack) {
                    stack = self.highlightStackTraceLines(stack);
                    $stackTrace.html(stack);
                    $el.find('.test-results__stack-loading-indicator').remove();
                    $stackTrace.show();
                });
            });
        }
        formatSuccessfulTest(result) {
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
        formatFailedTest(result) {
            let indent = '';
            if (this.firstTest) {
                indent = this.indent(this.suites.length);
                this.firstTest = false;
            }
            const testTitle = result.description;
            let doneHtml = indent + '<span title="' + this.escape(testTitle) + '" class="test-results__test';
            doneHtml += ' test-results__failed-test">✕</span> ' + this.escape(testTitle);
            const failedExpectations = result.failedExpectations || [];
            for (let i = 0; i < failedExpectations.length; i++) {
                const expectation = failedExpectations[i];
                doneHtml += '<div class="test-results__failed-test-message">' + this.escape(expectation.message) + '</div>';
                doneHtml += '<pre class="test-results__stack-trace"><div class="test-results__stack-loading-indicator">Loading stack trace, please wait...</div><div class="test-results__stack" style="display: none;">' + this.escape(expectation.stack) + '</div></pre>';
            }
            return doneHtml;
        }
        highlightStackTraceLines(stack) {
            const lines = stack.split("\n");
            const isTsLine = (line) => /\s*at.*?\s+\([^)]+\.ts:\d+:\d+\)$/.test(line);
            return lines.map((line, index) => {
                line = line.trim();
                if (isTsLine(line)) {
                    const lastTsLine = lines[index + 1] === undefined || !isTsLine(lines[index + 1]);
                    return '<div class="test-results__stack-line test-results__stack-line_ts' + (lastTsLine ? ' test-results__stack-line_ts-last' : '') + '">' + this.escape(line) + '</div>';
                }
                return `<div class="test-results__stack-line">${this.escape(line)}</div>`;
            }).join("");
        }
        escape(str) {
            return jasmineRequire.util().htmlEscape(str);
        }
        append(html) {
            this.el.find('.panel-body').append(html);
        }
        dump(obj) {
            return '<pre>' + this.escape(JSON.stringify(obj)) + '</pre>';
        }
        indent(length) {
            let s = '';
            for (let i = 0; i < length; i++) {
                s += '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            return s;
        }
    }
    exports.TestResultsReporter = TestResultsReporter;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiamFzbWluZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbImphc21pbmUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7OztJQWdIQSxNQUFNLGtCQUFrQjtRQUNiLE9BQU8sQ0FBQyxLQUFVO1lBQ3JCLElBQUksT0FBTyxHQUFHLEVBQUUsQ0FBQztZQUVqQixJQUFJLEtBQUssQ0FBQyxJQUFJLElBQUksS0FBSyxDQUFDLE9BQU8sRUFBRTtnQkFDN0IsT0FBTyxJQUFJLEtBQUssQ0FBQyxJQUFJLEdBQUcsSUFBSSxHQUFHLEtBQUssQ0FBQyxPQUFPLENBQUM7YUFDaEQ7aUJBQU07Z0JBQ0gsT0FBTyxJQUFJLEtBQUssQ0FBQyxRQUFRLEVBQUUsR0FBRyxTQUFTLENBQUM7YUFDM0M7WUFFRCxJQUFJLEtBQUssQ0FBQyxRQUFRLElBQUksS0FBSyxDQUFDLFNBQVMsRUFBRTtnQkFDbkMsT0FBTyxJQUFJLE1BQU0sR0FBRyxDQUFDLEtBQUssQ0FBQyxRQUFRLElBQUksS0FBSyxDQUFDLFNBQVMsQ0FBQyxDQUFDO2FBQzNEO1lBRUQsSUFBSSxLQUFLLENBQUMsSUFBSSxJQUFJLEtBQUssQ0FBQyxVQUFVLEVBQUU7Z0JBQ2hDLE9BQU8sSUFBSSxTQUFTLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxJQUFJLEtBQUssQ0FBQyxVQUFVLENBQUMsR0FBRyxHQUFHLENBQUM7YUFDakU7WUFFRCxPQUFPLE9BQU8sQ0FBQztRQUNuQixDQUFDO1FBRU0sS0FBSyxDQUFDLEtBQW1CO1lBQzVCLElBQUksQ0FBQyxLQUFLLEVBQUU7Z0JBQ1IsT0FBTyxFQUFFLENBQUM7YUFDYjtZQUVELE9BQU8sS0FBSyxDQUFDLEtBQUssSUFBSSxFQUFFLENBQUM7UUFDN0IsQ0FBQztLQUNKO0lBRUQsU0FBZ0IsV0FBVztRQUN2QixjQUFjLENBQUMsa0JBQWtCLEdBQUcsR0FBRyxFQUFFLEdBQUcsT0FBTyxrQkFBa0IsQ0FBQyxDQUFDLENBQUMsQ0FBQztRQVF6RSxNQUFNLENBQUMs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