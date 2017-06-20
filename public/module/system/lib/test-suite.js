define("system/lib/test-suite", ["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var TestSuite = (function () {
        function TestSuite() {
        }
        TestSuite.prototype.run = function (tests) {
            alert(tests().length);
        };
        return TestSuite;
    }());
    exports.TestSuite = TestSuite;
});
