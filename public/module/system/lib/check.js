define("system/lib/check", ["require", "exports", "./jasmine"], function (require, exports, jasmine_1) {
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
    var env = jasmine_1.bootJasmine();
    function main($container, sourceMappedStackTrace) {
        var stackTraceFormatter = function (stack) {
            return new Promise(function (resolve) {
                sourceMappedStackTrace.mapStackTrace(stack, function (mappedStack) {
                    resolve(mappedStack.join("\n"));
                });
            });
        };
        env.addReporter(new jasmine_1.TestResultsReporter($container, stackTraceFormatter));
        var seleniumReporter = {
            jasmineDone: function (runDetails) {
                if (window.location.search.indexOf('bot') >= 0) {
                    document.getElementById('main__body').innerHTML += '<h2 id="testing-results">' + runDetails.failedExpectations.length + '</h2>';
                }
            }
        };
        env.addReporter(seleniumReporter);
        env.execute();
    }
    exports.main = main;
});
//# sourceMappingURL=check.js.map
