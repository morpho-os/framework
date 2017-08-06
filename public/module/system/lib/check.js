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
    var _a = jasmine_1.bootJasmine(), htmlReporter = _a.htmlReporter, env = _a.env;
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
//# sourceMappingURL=check.js.map
